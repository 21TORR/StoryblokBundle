<?php declare(strict_types=1);

namespace Torr\Storyblok\Webhook\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Exception\JsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Torr\Storyblok\Event\StoryblokWebhookEvent;
use Torr\Storyblok\Webhook\Parser\WebhookPayloadParser;
use Torr\Storyblok\Webhook\Request\RequestValidator;

final class WebhookController extends AbstractController
{
	/**
	 * Handles the incoming webhook from Storyblok
	 */
	public function webhook (
		RequestValidator $requestValidator,
		LoggerInterface $logger,
		WebhookPayloadParser $payloadParser,
		EventDispatcherInterface $dispatcher,
		Request $request,
		?string $urlSecret,
	) : JsonResponse
	{
		// Trailing slashes at the end of the URL will cause the URL secret to contain an empty string. We're normalizing here.
		$urlSecret = $urlSecret ?: null;

		$isValidSignature = $requestValidator->isValidRequest($request, $urlSecret);

		if (!$isValidSignature || !$request->isMethod("POST"))
		{
			$logger->error("Storyblok Webhook: failed to handle request", [
				"method" => $request->getMethod(),
				"payload" => $request->getContent(),
				"signature_valid" => $isValidSignature,
				"headers" => $request->headers->all(),
			]);

			return $this->json([
				"ok" => false,
				"error" => "invalid / unsigned request",
			], 403);
		}

		try
		{
			$payload = $payloadParser->parseFromRawArray($request->toArray());

			if (null === $payload)
			{
				return $this->json([
					"ok" => false,
					"error" => "invalid payload",
				]);
			}

			$webhookEvent = new StoryblokWebhookEvent($payload);
			$dispatcher->dispatch($webhookEvent);

			return $this->json([
				...$webhookEvent->getResponseData(),
				"ok" => true,
			]);
		}
		catch (JsonException $exception)
		{
			$logger->error("Invalid webhook request", [
				"exception" => $exception,
				"body" => $request->getContent(),
			]);

			return $this->json([
				"ok" => false,
				"error" => "invalid JSON",
			]);
		}
	}
}
