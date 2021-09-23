<?php

namespace ADT\SparkPostApiMailer\Services;

class SparkPostApiMailerService {
	
	use \Nette\SmartObject;

	protected $config;

	/** @var \SparkPost\SparkPost */
	protected $sparky;

	public function setConfig(array $config) {
		$this->config = $config;

		$sparkyOptions = [
			'key' => $config['authToken'],
		];

		if (!$this->sparky) {
			$httpClient = new \Http\Adapter\Guzzle6\Client(new \GuzzleHttp\Client([
				'timeout' => 30,
			]));
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

		return $this->sparky->transmissions->post($message);
	}

	/**
	 * @param \Nette\Mail\Message $mail
	 * @return int Transmission id
	 * @throws
	 */
	public function send(\Nette\Mail\Message $mail) {
		try {
			/** @var \SparkPost\SparkPostResponse $response */
			$response = $this->sendAsync($mail)
				->wait();

			/*
			 * Response body:
			 * Array
			 * (
			 *   [results] => Array
			 *   (
			 *     [total_rejected_recipients] => 0
			 *     [total_accepted_recipients] => 3
			 *     [id] => 102583775931650787
			 *   )
			 * )
			 */

			return $response->getBody()['results']['id'];
		} catch (\Exception $ex) {
			throw new \Nette\Mail\SendException($ex->getMessage(), $ex->getCode(), $ex);
		}
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

	public function getSendingDomains()
	{
		return $this->sparky->syncRequest('GET', 'sending-domains')->getBody()['results'];
	}
}
