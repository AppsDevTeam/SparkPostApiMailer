<?php

namespace ADT\SparkPostApiMailer;


trait SparkPostApiMailerTrait {

	/** @var Services\SparkPostApiMailerService @autowire */
	protected $sparkPostApiMailerService;

	public function injectSparkPostApiMailerService(Services\SparkPostApiMailerService $service) {
		$this->sparkPostApiMailerService = $service;
	}

	/**
	 * @param \Nette\Mail\Message $mail
	 * @return void
	 * @throws \Nette\Mail\SendException
	 */
	public function send(\Nette\Mail\Message $mail) {
		$this->sparkPostApiMailerService->send($mail);
	}

	/**
	 * @param \Nette\Mail\Message $mail
	 * @return \Http\Promise\Promise
	 */
	public function sendAsync(\Nette\Mail\Message $mail) {
		return $this->sparkPostApiMailerService->sendAsync($mail);
	}

	/**
	 * @param string $recipient
	 * @return FALSE|array
	 */
	public function getSuppressionDetails($recipient) {
		return $this->sparkPostApiMailerService->getSuppressionDetails($recipient);
	}

	/**
	 * @param $recipient
	 * @return bool
	 */
	public function removeSuppression($recipient) {
		return $this->sparkPostApiMailerService->removeSuppression($recipient);
	}
}