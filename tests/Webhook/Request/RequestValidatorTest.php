<?php declare(strict_types=1);

namespace Tests\Torr\Storyblok\Webhook\Request;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\Request;
use Torr\Storyblok\Config\StoryblokConfig;
use Torr\Storyblok\Webhook\Request\RequestValidator;

/**
 * @internal
 */
final class RequestValidatorTest extends TestCase
{
	/**
	 *
	 */
	public static function provideIsValidRequest () : iterable
	{
		$sign = static fn (string $secret) => hash_hmac("sha1", "", $secret);

		yield "valid" => [
			true,
			"s3cr3t",
			$sign("s3cr3t"),
			null,
		];

		yield "invalid" => [
			false,
			"s3cr3t",
			$sign("invalid"),
			null,
		];

		yield "empty value, but header given" => [
			false,
			null,
			"a",
			null,
		];

		yield "secret, but no header" => [
			false,
			"s3cr3t",
			"",
			null,
		];

		yield "no secret, no header, no url secret" => [
			true,
			null,
			"",
			null,
		];

		// URL secrets disabled: should never be allowed if not explicitly configured as allowed
		yield "url secret disabled: only url set" => [
			false,
			null,
			"",
			"url secret",
		];

		yield "url secret disabled: secret configured + url set" => [
			false,
			"s3cr3t",
			"",
			"url secret",
		];

		yield "url secret disabled: correct header secret, but obsolete url secret and not allowed" => [
			false,
			"s3cr3t",
			$sign("s3cr3t"),
			"url secret",
		];

		yield "url secret disabled: correct url secret, but not allowed" => [
			false,
			"url secret",
			"",
			"url secret",
		];

		// URL secrets enabled:
		yield "url secret enabled: no secret configured" => [
			false,
			null,
			"",
			"url secret",
			true,
		];

		yield "url secret enabled: invalid secret configured" => [
			false,
			"invalid",
			"",
			"url secret",
			true,
		];

		yield "url secret enabled: correct secret configured" => [
			true,
			"url secret",
			"",
			"url secret",
			true,
		];

		yield "url secret enabled: no secret given" => [
			false,
			"url secret",
			"",
			null,
			true,
		];

		yield "url secret enabled: no secret given + none configured" => [
			true,
			null,
			"",
			null,
			true,
		];
	}

	/**
	 *
	 */
	#[DataProvider("provideIsValidRequest")]
	public function testIsValidRequest (
		bool $expectedValid,
		?string $secret,
		string $headerValue,
		?string $urlToken,
		bool $allowUrlSecrets = false,
	) : void
	{
		$request = new Request(
			server: [
				"HTTP_webhook_signature" => $headerValue,
			],
		);

		$config = new StoryblokConfig(
			webhookSecret: $secret,
			allowUrlWebhookSecret: $allowUrlSecrets,
		);

		$validator = new RequestValidator($config, new NullLogger());
		self::assertSame($expectedValid, $validator->isValidRequest($request, $urlToken));
	}
}
