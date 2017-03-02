<?php

namespace ADT\SparkPostApiMailer\Services;

class SparkPostApiMailerService extends \Nette\Object {

	protected $config;

	/** @var \SparkPost\SparkPost */
	protected $sparky;

	public function setConfig(array $config) {
		$this->config = $config;

		$sparkyOptions = [
			'key' => $config['authToken'],
		];

		if (!$this->sparky) {
			$httpClient = new \Http\Adapter\Guzzle6\Client(new \GuzzleHttp\Client);
			$this->sparky = new \SparkPost\SparkPost($httpClient, $sparkyOptions);
		} else {
			$this->sparky->setOptions($sparkyOptions);
		}
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
	public function send(\Nette\Mail\Message $mail) {
		return $this->sendAsync($mail)
			->wait();
	}

	/**
	 * @param string $recipient
	 * @return FALSE|array
	 */
	public function getSuppressionDetails($recipient) {
		try {
			$response = $this->sparky->syncRequest('GET', 'suppression-list/' . urlencode($recipient));

			if ($response->getStatusCode() !== 200) {
				return FALSE;
			}

			return $response->getBody();
		} catch (\SparkPost\SparkPostException $e) {
			return FALSE;
		}
	}

	/**
	 * @param $recipient
	 * @return bool
	 */
	public function removeSuppression($recipient) {
		try {
			$response = $this->sparky->syncRequest('DELETE', 'suppression-list/' . urlencode($recipient));

			return $response->getStatusCode() === 204;
		} catch (\SparkPost\SparkPostException $e) {
			return FALSE;
		}
	}
}