<?php declare(strict_types=1);

namespace Torr\Storyblok\Webhook\Parser;

use Psr\Log\LoggerInterface;
use Torr\Storyblok\Adapter\StoryblokAdapterRegistry;
use Torr\Storyblok\Webhook\Action\WebhookAction;
use Torr\Storyblok\Webhook\Exception\WebhookParseFailedException;
use Torr\Storyblok\Webhook\Payload\AbstractWebhookPayload;
use Torr\Storyblok\Webhook\Payload\AssetWebhookPayload;
use Torr\Storyblok\Webhook\Payload\DatasourceEntryWebhookPayload;
use Torr\Storyblok\Webhook\Payload\PipelineWebhookPayload;
use Torr\Storyblok\Webhook\Payload\ReleaseWebhookPayload;
use Torr\Storyblok\Webhook\Payload\StoryWebhookPayload;
use Torr\Storyblok\Webhook\Payload\UserWebhookPayload;
use Torr\Storyblok\Webhook\Payload\WorkflowStageWebhookPayload;

/**
 * Parses the storyblok payload from raw array into DTO
 */
final readonly class WebhookPayloadParser
{
	/**
	 */
	public function __construct (
		private StoryblokAdapterRegistry $storyblokAdapterRegistry,
		private LoggerInterface $logger,
	) {}

	/**
	 * Parses the storyblok webhook event from the given payload
	 */
	public function parseFromRawArray (array $payload, string $spaceId) : ?AbstractWebhookPayload
	{
		$text = $payload["text"] ?? null;
		$action = $payload["action"] ?? null;
		$payloadSpaceId = $payload["space_id"] ?? null;

		// check basic structure
		if (
			!\is_string($text)
			|| !\is_string($action)
			|| !\is_int($payloadSpaceId)
		)
		{
			$this->logger->error("Storyblok Webhook: could not parse basic structure", [
				"payload" => $payload,
			]);

			return null;
		}

		// check space id
		if ((string) $payloadSpaceId !== $spaceId)
		{
			$this->logger->error("Storyblok Webhook: received webhook for different space. Got id {provided}, but expected {expected}", [
				"provided" => $payloadSpaceId,
				"expected" => $spaceId,
				"payload" => $payload,
			]);

			return null;
		}

		if (null === $this->storyblokAdapterRegistry->getByStoryblokSpaceId($spaceId))
		{
			$this->logger->error("Storyblok Webhook: no storyblok adapter found for space id {spaceId}", [
				"spaceId" => $spaceId,
				"payload" => $payload,
			]);

			return null;
		}

		try
		{
			return $this->parseAction($text, $action, $payload);
		}
		catch (WebhookParseFailedException $exception)
		{
			$this->logger->error("Storyblok Webhook: failed to parse payload: {message}", [
				"message" => $exception->getMessage(),
				"exception" => $exception,
				"payload" => $payload,
			]);

			return null;
		}
	}

	/**
	 * Parses the webhook action from the plain text action and the payload.
	 * Unfortunately, the action keys are not unique, so they depend on other keys.
	 */
	private function parseAction (string $text, string $action, array $payload) : ?AbstractWebhookPayload
	{
		// must be at the front, as it also shares some payload entries
		if (isset($payload["workflow_name"]))
		{
			$parsedAction = match ($action)
			{
				"stage.changed" => WebhookAction::WorkflowStageChanged,
				default => throw new WebhookParseFailedException(\sprintf(
					"Invalid workflow webhook action found: %s",
					$action,
				)),
			};

			return $this->parseWorkflowEvent($text, $parsedAction, $payload);
		}

		if (isset($payload["asset_id"]))
		{
			$parsedAction = match ($action)
			{
				"created" => WebhookAction::AssetCreated,
				"deleted" => WebhookAction::AssetDeleted,
				"replaced" => WebhookAction::AssetReplaced,
				"restored" => WebhookAction::AssetRestored,
				default => throw new WebhookParseFailedException(\sprintf(
					"Invalid asset webhook action found: %s",
					$action,
				)),
			};

			return $this->parseAssetEvent($text, $parsedAction, $payload);
		}

		if (isset($payload["datasource_slug"]))
		{
			$parsedAction = match ($action)
			{
				"entries_updated" => WebhookAction::DatasourceEntryUpdated,
				default => throw new WebhookParseFailedException(\sprintf(
					"Invalid asset webhook action found: %s",
					$action,
				)),
			};

			return $this->parseDatasourceEvent($text, $parsedAction, $payload);
		}

		if (isset($payload["story_id"]))
		{
			$parsedAction = match ($action)
			{
				"deleted" => WebhookAction::StoryDeleted,
				"moved" => WebhookAction::StoryMoved,
				"published" => WebhookAction::StoryPublished,
				"unpublished" => WebhookAction::StoryUnpublished,
				default => throw new WebhookParseFailedException(\sprintf(
					"Invalid story webhook action found: %s",
					$action,
				)),
			};

			return $this->parseStoryEvent($text, $parsedAction, $payload);
		}

		if (isset($payload["branch_id"]))
		{
			$parsedAction = match ($action)
			{
				"deployed" => WebhookAction::PipelineDeployed,
				default => throw new WebhookParseFailedException(\sprintf(
					"Invalid pipeline webhook action found: %s",
					$action,
				)),
			};

			return $this->parsePipelineEvent($text, $parsedAction, $payload);
		}

		if (isset($payload["user_id"]))
		{
			$parsedAction = match ($action)
			{
				"added" => WebhookAction::UserAdded,
				"roles_updated" => WebhookAction::UserRolesUpdated,
				"removed" => WebhookAction::UserRemoved,
				default => throw new WebhookParseFailedException(\sprintf(
					"Invalid user webhook action found: %s",
					$action,
				)),
			};

			return $this->parseUserEvent($text, $parsedAction, $payload);
		}

		if (isset($payload["release_id"]))
		{
			$parsedAction = match ($action)
			{
				"merged" => WebhookAction::ReleaseMerged,
				default => throw new WebhookParseFailedException(\sprintf(
					"Invalid release webhook action found: %s",
					$action,
				)),
			};

			return $this->parseReleaseAction($text, $parsedAction, $payload);
		}

		$this->logger->error("Storyblok Webhook: could not match any action", [
			"payload" => $payload,
		]);

		return null;
	}

	/**
	 * Parses all asset events
	 */
	private function parseAssetEvent (string $text, WebhookAction $action, array $payload) : ?AssetWebhookPayload
	{
		$assetId = $payload["asset_id"] ?? null;

		if (!\is_int($assetId))
		{
			$this->logger->error("Storyblok Webhook, could not parse asset event: invalid asset id", [
				"asset_id" => $assetId,
				"payload" => $payload,
			]);

			return null;
		}

		if (!preg_match('~\nhttps://a\.storyblok\.com/f/\d+(?P<path>/.+)$~D', $text, $matches))
		{
			$this->logger->error("Storyblok Webhook, could not parse asset event: missing/invalid asset URL", [
				"asset_id" => $assetId,
				"payload" => $payload,
				"text" => $text,
			]);

			return null;
		}

		return new AssetWebhookPayload($action, $text, $assetId, $matches["path"]);
	}

	/**
	 * Parses the datasource action
	 */
	private function parseDatasourceEvent (string $text, WebhookAction $action, array $payload) : ?DatasourceEntryWebhookPayload
	{
		$slug = $payload["datasource_slug"] ?? null;

		if (!\is_string($slug))
		{
			$this->logger->error("Storyblok Webhook, could not parse datasource event: no datasource slug matched", [
				"datasource_slug" => $slug,
				"payload" => $payload,
			]);

			return null;
		}

		return new DatasourceEntryWebhookPayload($action, $text, $slug);
	}

	/**
	 * Parses the datasource action
	 */
	private function parseStoryEvent (string $text, WebhookAction $action, array $payload) : ?StoryWebhookPayload
	{
		$storyId = $payload["story_id"] ?? null;
		$fullSlug = $payload["full_slug"] ?? null;

		if (!\is_int($storyId) || !\is_string($fullSlug))
		{
			$this->logger->error("Storyblok Webhook, could not parse story event: no valid story or slug matched", [
				"story_id" => $storyId,
				"full_slug" => $fullSlug,
				"payload" => $payload,
			]);

			return null;
		}

		return new StoryWebhookPayload($action, $text, $storyId, $fullSlug);
	}

	/**
	 * Parses the user event
	 */
	private function parseUserEvent (string $text, WebhookAction $action, array $payload) : ?UserWebhookPayload
	{
		$userId = $payload["user_id"] ?? null;

		if (!\is_int($userId))
		{
			$this->logger->error("Storyblok Webhook, could not parse user event: invalid user id", [
				"user_id" => $userId,
				"payload" => $payload,
			]);

			return null;
		}

		return new UserWebhookPayload($action, $text, $userId);
	}

	/**
	 * Parses the datasource action
	 */
	private function parseWorkflowEvent (string $text, WebhookAction $action, array $payload) : ?WorkflowStageWebhookPayload
	{
		$storyId = $payload["story_id"] ?? null;
		$workflowName = $payload["workflow_name"] ?? null;
		$workflowStageName = $payload["workflow_stage_name"] ?? null;

		if (!\is_int($storyId) || !\is_string($workflowName) || !\is_string($workflowStageName))
		{
			$this->logger->error("Storyblok Webhook, could not parse workflow event: invalid data", [
				"story_id" => $storyId,
				"workflow_name" => $workflowName,
				"workflow_stage_name" => $workflowStageName,
				"payload" => $payload,
			]);

			return null;
		}

		return new WorkflowStageWebhookPayload(
			$action,
			$text,
			$storyId,
			$workflowName,
			$workflowStageName,
		);
	}

	/**
	 * Parses the pipeline event
	 */
	private function parsePipelineEvent (string $text, WebhookAction $action, array $payload) : ?PipelineWebhookPayload
	{
		$branchId = $payload["branch_id"] ?? null;

		if (!\is_int($branchId))
		{
			$this->logger->error("Storyblok Webhook, could not parse pipeline event: invalid branch id", [
				"branch_id" => $branchId,
				"payload" => $payload,
			]);

			return null;
		}

		return new PipelineWebhookPayload($action, $text, $branchId);
	}

	/**
	 * Parses the pipeline event
	 */
	private function parseReleaseAction (string $text, WebhookAction $action, array $payload) : ?ReleaseWebhookPayload
	{
		$releaseId = $payload["release_id"] ?? null;

		if (!\is_int($releaseId))
		{
			$this->logger->error("Storyblok Webhook, could not parse release event: invalid release id", [
				"release_id" => $releaseId,
				"payload" => $payload,
			]);

			return null;
		}

		return new ReleaseWebhookPayload($action, $text, $releaseId);
	}
}
