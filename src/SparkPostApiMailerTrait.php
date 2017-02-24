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
}