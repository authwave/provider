<?php
namespace Authwave\Email;

use Gt\Logger\Log;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Attributes\AttributesExtension;
use League\CommonMark\Extension\Autolink\AutolinkExtension;

class Emailer {
	const DEFAULT_EMAIL_FROM_ADDRESS = "support@authwave.com";
	const DEFAULT_EMAIL_FROM_NAME = "Authwave";

	public function __construct(
		private readonly string $apiKey,
	) {}

	public function send(
		string $toAddress,
		string $templateName,
		array $kvp = [],
		string $fromAddress = self::DEFAULT_EMAIL_FROM_ADDRESS,
		string $fromName = self::DEFAULT_EMAIL_FROM_NAME,
	):string {
		$filePath = "data/email/$templateName.md";
		if(!is_file($filePath)) {
			throw new EmailTemplateNotFoundException($templateName);
		}

		$markdown = file_get_contents($filePath);
		$markdown = trim($markdown);

		foreach($kvp as $key => $value) {
			if(!is_scalar($value)) {
				continue;
			}

			$markdown = str_replace(
				"{{" . $key . "}}",
				$value,
				$markdown
			);
		}

		$subject = trim(substr($markdown, 1, strpos($markdown, "\n")));
		$markdown = substr($markdown, strpos($markdown, "\n") + 2);

		$environment = new Environment();
		$environment->addExtension(new AutolinkExtension());
		$environment->addExtension(new AttributesExtension());

		$converter = new CommonMarkConverter();
		$html = $converter->convert($markdown);

		$emailData = [
			"sender" => [
				"name" => $fromName,
				"email" => $fromAddress,
			],
			"to" => [
				[
					"email" => $toAddress,
				]
			],
			"subject" => $subject,
			"textContent" => $markdown,
			"htmlContent" => (string)$html,
		];

// TODO: Upgrade to use fetch()
		$ch = curl_init("https://api.sendinblue.com/v3/smtp/email");
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			"accept: application/json",
			"api-key: $this->apiKey",
			"content-type: application/json",
		]);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($emailData));
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);

		$response = curl_exec($ch);
		$emailId = trim($response);
		Log::info("Send email: $templateName ($emailId)");
		return trim($emailId);
	}

	public function sendToken(
		string $email,
		string $siteName,
		string $token,
	):string {
		return $this->send(
			$email,
			"token",
			[
				"token" => $token,
				"siteName" => $siteName,
			],
		);
	}
}
