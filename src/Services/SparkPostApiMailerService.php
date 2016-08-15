<?php

namespace ADT\SparkPostApiMailer\Services;


class SparkPostApiMailerService extends \Nette\Object {

	protected $config;

	function __construct($config) {
		$this->config = $config;
	}

	public function send(\Nette\Mail\Message $mail) {

	}

}