<?php

namespace ADT\SparkPostApiMailer\DI;


use ADT\SparkPostApiMailer\Services;

class SparkPostApiMailerExtension extends \Nette\DI\CompilerExtension {

	public function loadConfiguration() {
		parent::loadConfiguration();

		$builder = $this->getContainerBuilder();

		// disable autowiring for all other IMailer services
		foreach ($builder->getDefinitions() as $definition) {
			if ($definition->getClass() === \Nette\Mail\IMailer::class) {
				$definition->setAutowired(FALSE);
			}
		}

		// register service
		$builder->addDefinition($this->prefix('mailer'))
			->setClass(Services\SparkPostApiMailerService::class)
			->addSetup('$service->setConfig(?)', [ $this->getConfig() ]);
	}


}