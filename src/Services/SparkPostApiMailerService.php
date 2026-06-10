<?php

namespace ADT\SparkPostApiMailer\Services;

class SparkPostApiMailerService {
	
	use \Nette\SmartObject;

	protected $config;

	/** @var \SparkPost\SparkPost */
	protected $sparky;
	
	private $lastException = null;

	/**
	 * @return \Exception|null
	 */
	public function getLastException()
	{
		return $this->lastException;
	}

	public function setConfig(array $config) {
		$this->config = $config;

		$sparkyOptions = [
			'key' => $config['authToken'],
		];

		if (!$this->sparky) {
			$httpClient = new \Http\Adapter\Guzzle7\Client(new \GuzzleHttp\Client([
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

			if ($header === 'Bcc') {
				$mail->setHeader('Bcc', null);
			}
		}

		$message['content']['email_rfc822'] = $mail->generateMessage();

		if (!empty($this->config['options'])) {
			// pass transmission options
			$message['options'] = $this->config['options'];
		}

		if (!empty($this->config['return_path'])) {
			// pass transmission return_path
			$message['return_path'] = $this->config['return_path'];
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
			$this->lastException = $e;
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
			$this->lastException = $e;
			return FALSE;
		}
	}

	public function getSendingDomains()
	{
		return $this->sparky->syncRequest('GET', 'sending-domains')->getBody()['results'];
	}

	/**
	 * Vrátí jednu stránku suppression listu s kurzorovou paginací.
	 * https://developers.sparkpost.com/api/suppression-list.html#suppression-list-search-get
	 *
	 * @param string|null $cursor Kurzor další stránky (z linku 'rel: next'), null = první stránka
	 * @param int $perPage
	 * @return FALSE|array Tělo odpovědi (klíče 'results', 'links', 'total_count'), nebo FALSE při chybě
	 */
	public function getSuppressions($cursor = null, $perPage = 10000) {
		try {
			$payload = [ 'per_page' => $perPage ];
			// "cursor=" (i prázdný) přepne SparkPost do kurzorové paginace
			$payload['cursor'] = $cursor ?? '';

			$response = $this->sparky->syncRequest('GET', 'suppression-list', $payload);

			if ($response->getStatusCode() !== 200) {
				return FALSE;
			}

			return $response->getBody();
		} catch (\SparkPost\SparkPostException $e) {
			$this->lastException = $e;
			return FALSE;
		}
	}
}
