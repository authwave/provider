<?php
namespace Authwave\Page\Login;

use Authwave\Application\ApplicationDeployment;
use Authwave\DataTransfer\LoginData;
use Authwave\DataTransfer\RequestData;
use Authwave\Password\PasswordTooShortException;
use Authwave\Password\Strengthometer;
use Authwave\UI\Flash;
use Gt\DomTemplate\Element;
use Gt\Input\InputData\InputData;
use Gt\WebEngine\Logic\Page;
use TypeError;

class AuthenticatePage extends Page {
	public RequestData $requestData;
	public Flash $flash;
	private LoginData $loginData;

	public function go():void {
		$this->restoreLoginData();
		$this->outputEmailAddress();
		$this->outputProviders(
			$this->document->querySelector(".auth-option.social")
		);
	}

	public function doPassword(InputData $data):void {
		$password = $data->getString("password");

		$strengthometer = new Strengthometer($password);
		try {
			$strengthometer->validate();
		}
		catch(PasswordTooShortException $exception) {
			$this->flash->error("Your password is too short, please pick a stronger one with at least 12 characters");
			$this->reload();
		}

		$this->login(
			LoginData::TYPE_PASSWORD,
			$password
		);
	}

	public function doEmail(InputData $data):void {
		$this->login(
			LoginData::TYPE_EMAIL
		);
	}

	public function doSocialGoogle():void {
		$this->login(
			LoginData::TYPE_SOCIAL,
			LoginData::SOCIAL_GOOGLE
		);
	}

	public function doSocialTwitter():void {
		$this->login(
			LoginData::TYPE_SOCIAL,
			LoginData::SOCIAL_TWITTER
		);
	}

	public function doSocialFacebook():void {
		$this->login(
			LoginData::TYPE_SOCIAL,
			LoginData::SOCIAL_FACEBOOK
		);
	}

	public function doSocialLinkedIn():void {
		$this->login(
			LoginData::TYPE_SOCIAL,
			LoginData::SOCIAL_LINKEDIN
		);
	}

	public function doSocialGithub():void {
		$this->login(
			LoginData::TYPE_SOCIAL,
			LoginData::SOCIAL_GITHUB
		);
	}

	public function doSocialMicrosoft():void {
		$this->login(
			LoginData::TYPE_SOCIAL,
			LoginData::SOCIAL_MICROSOFT
		);
	}

	private function login(string $type, string $data = null):void {
		$this->restoreLoginData();
		var_dump($this->requestData, $data);die();
	}

	private function outputProviders(Element $outputTo):void {
		$providers = [
			"Google",
			"Facebook",
			"Twitter",
			"LinkedIn",
			"Github",
			"Microsoft",
		];

		$outputTo->bindList($providers);
	}

	private function restoreLoginData():void {
		try {
			$this->loginData = $this->session->get(
				LoginData::SESSION_LOGIN_DATA
			);
		}
		catch(TypeError $error) {
			$this->redirect("/login");
			exit;
		}
	}

	private function outputEmailAddress():void {
		/** @var LoginData $loginData */
		$loginData = $this->session->get(LoginData::SESSION_LOGIN_DATA);
		$this->document->bindKeyValue("email", $loginData->getEmail());
	}
}