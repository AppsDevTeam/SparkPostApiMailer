<?php

namespace ADT\SparkPostApiMailer\Services;

class SparkPostApiMailerService extends \Nette\Object {

	const SPARKPOST_ENDPOINT = 'https://api.sparkpost.com/api/v1';

	protected $config;

	public function setConfig(array $config) {
		$this->config = $config;
	}

	protected function createTransmission($message) {
		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => static::SPARKPOST_ENDPOINT . '/transmissions?num_rcpt_errors=3',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => json_encode($message),
			CURLOPT_HTTPHEADER => array(
				"accept: application/json",
				"authorization: " . $this->config['authToken'],
				"cache-control: no-cache",
				"content-type: application/json",
			),
		));

		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err = curl_error($curl);
		curl_close($curl);

		if ($err) {
			throw new \Nette\Mail\SendException('SparkPostApiMailer error: ' . $err);
		}

		if (substr($httpcode, 0, 1) !== '2') {
			throw new \Nette\Mail\SendException('SparkPostApiMailer error: ' . $response);
		}

		return $response;
	}

	public function send(\Nette\Mail\Message $mail) {
		$message = [ ];

		foreach ($mail->getHeader('To') as $email => $name) {
			$message['recipients'][]['address'] = $email;
		}

		$message['content']['email_rfc822'] = $mail->generateMessage();
		$this->createTransmission($message);
	}

}