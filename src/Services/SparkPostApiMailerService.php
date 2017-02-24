<?php

namespace ADT\SparkPostApiMailer\Services;

class SparkPostApiMailerService extends \Nette\Object {

	protected $config;

	/** @var \SparkPost\SparkPost */
	protected $sparky;

	public function setConfig(array $config) {
		$this->config = $config;

		$this->sparky->setOptions(
			[
				'key' => $config['authToken'],
			]
		);
	}

	public function __construct() {
		$httpClient = new \Http\Adapter\Guzzle6\Client(new \GuzzleHttp\Client);
		$this->sparky = new \SparkPost\SparkPost($httpClient, []);
	}

	/**
	 * @param \Nette\Mail\Message $mail
	 * @return \Http\Promise\Promise
	 */
	public function sendAsync(\Nette\Mail\Message $mail) {
		$message = [];

		foreach ([ 'To', 'Cc', 'Bcc' ] as $header) {
			$addresses = $mail->getHeader($header);

			if (!$addresses) {
				// getHeader can return NULL
				continue;
			}

			foreach ($addresses as $email => $name) {
				$message['recipients'][]['address'] = [
					'email' => $email,
					'name' => $name,
				];
			}
		}

		$message['content']['email_rfc822'] = $mail->generateMessage();

		if (!empty($this->config['options'])) {
			// pass transmission options
			$message['options'] = $this->config['options'];
		}

		return $this->sparky->transmissions->post($message)
			->then(
				NULL,
				function (\SparkPost\SparkPostException $ex) {
					throw new \Nette\Mail\SendException('SparkPostApiMailer error: ' . $ex->getMessage(), $ex->getCode(), $ex);
				}
			);
	}

	/**
	 * @param \Nette\Mail\Message $mail
	 * @return \SparkPost\SparkPostResponse
	 * @throws
	 */
	public function sendSync(\Nette\Mail\Message $mail) {
		return $this->sendAsync($mail)
			->wait();
	}

}