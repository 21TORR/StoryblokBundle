<?php declare(strict_types=1);

namespace Torr\Storyblok\Webhook\Request;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Exclude;
use Symfony\Component\HttpFoundation\Request;
use Torr\Storyblok\Adapter\SpaceSettings;

#[Exclude]
final readonly class RequestValidator
{
	/**
	 */
	public function __construct (
		private SpaceSettings $spaceSettings,
		private LoggerInterface $logger,
	) {}

	/**
	 */
	public function isValidRequest (Request $request, ?string $urlSecret) : bool
	{
		$secret = $this->spaceSettings->webhookSecret ?? "";

		if ($this->checkProperSignature($secret, $request))
		{
			if (null !== $urlSecret)
			{
				// bail out if a URL secret and proper secret is given
				$this->logger->critical("Storyblok Webhook Request Validator: detected both webhook signature and url secret. Disable the URL secret, update the webhook URL and rotate the secret.");

				return false;
			}

			return true;
		}

		if (null !== $urlSecret)
		{
			if ($this->spaceSettings->allowUrlWebhookSecret)
			{
				return $this->checkUrlSecret($secret, $urlSecret);
			}

			$this->logger->critical("Storyblok Webhook Request Validator: detected url secret even if disabled. Update the webhook URL and rotate the secret.");

			return false;
		}

		// allow empty / missing signature if we have no secret
		$requestSignature = (string) $request->headers->get("webhook-signature");

		return "" === $requestSignature && "" === $secret;
	}

	/**
	 * Checks a full signature of the payload
	 */
	private function checkProperSignature (string $secret, Request $request) : bool
	{
		$requestSignature = (string) $request->headers->get("webhook-signature");

		return hash_equals(
			hash_hmac("sha1", (string) $request->getContent(), $secret),
			$requestSignature,
		);
	}

	/**
	 * Checks whether the URL secret is correct
	 */
	private function checkUrlSecret (string $secret, string $urlSecret) : bool
	{
		return hash_equals($secret, $urlSecret);
	}
}
