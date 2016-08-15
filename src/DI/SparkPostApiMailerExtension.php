<?php

namespace ADT\SparkPostApiMailer\DI;


class SparkPostApiMailerExtension extends \Nette\DI\CompilerExtension {

	public function beforeCompile() {
		parent::beforeCompile();

		dd('yay');
	}


}