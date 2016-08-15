<?php

namespace ADT\SparkPostApiMailer;


trait SparkPostApiMailerTrait {

	/** @var Services\SparkPostApiMailerService @autowire */
	protected $sparkPostApiMailerService;

	public function injectSparkPostApiMailerService(Services\SparkPostApiMailerService $service) {
		$this->sparkPostApiMailerService = $service;
	}

	public function send(\Nette\Mail\Message $mail) {
		return $this->sparkPostApiMailerService->send($mail);
	}

}