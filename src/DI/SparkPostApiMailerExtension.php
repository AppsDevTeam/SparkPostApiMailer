<?php

namespace ADT\SparkPostApiMailer\DI;


use ADT\SparkPostApiMailer\Services;

class SparkPostApiMailerExtension extends \Nette\DI\CompilerExtension {

	public function loadConfiguration() {
		parent::loadConfiguration();

		$config = $this->getConfig();
		$builder = $this->getContainerBuilder();

		// register service
		$builder->addDefinition($this->prefix('service'))
			->setClass(Services\SparkPostApiMailerService::class)
			->addSetup('$service->setConfig(?)', [ $config ]);

		if (!empty($config['registerMailer'])) {
			$builder->addDefinition($this->prefix('mailer'))
				->setClass(Services\SparkPostApiMailer::class)
				->setInject();
		}
	}


}