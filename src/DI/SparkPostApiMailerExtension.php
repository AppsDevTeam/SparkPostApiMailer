<?php

namespace ADT\SparkPostApiMailer\DI;


use ADT\SparkPostApiMailer\Services;

class SparkPostApiMailerExtension extends \Nette\DI\CompilerExtension {

	public function beforeCompile() {
		parent::beforeCompile();

		$builder = $this->getContainerBuilder();

		// register service
		$builder->addDefinition($this->prefix('mailer'))
			->setClass(Services\SparkPostApiMailerService::class)
			->addSetup('setConfig(?)', [ $this->getConfig() ]);
	}


}