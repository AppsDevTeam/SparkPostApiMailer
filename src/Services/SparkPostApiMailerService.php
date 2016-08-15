<?php

namespace ADT\SparkPostApiMailer\Services;


class SparkPostApiMailerService extends \Nette\Object {

	protected $config;

	public function setConfig(array $config) {
		$this->config = $config;
	}

	public function send(\Nette\Mail\Message $mail) {
		// TODO
	}

}