# SparkPostApiMailer

## Installation

1. composer

	```bash
	composer require adt/spark-post-api-mailer
	```

2. config.neon

	```neon
	extensions:
	    sparkPostApiMailer:
	        authToken: <YOUR TOKEN>
	```

## Usage

1. standalone

	config.neon
	```neon
	services:
	    nette.mailer:
	        class: \ADT\SparkPostApiMailer\Services\SparkPostApiMailerService
	```
	
	MailComponent.php
	```php
	function __construct(\Nette\Mail\IMailer $mailer) {
	    $this->mailer = $mailer;
	}
	 
	function sendMail(\Nette\Mail\Message $mail) {
	    $this->mailer->send($mail);
	}
	```

2. with adt/single-recipient-mailer

	Mailer.php
	```
	class Mailer extends \ADT\Mail\SingleRecipientMailer {
	    public function __construct(
	        array $options,
	        \ADT\SparkPostApiMailer\Services\SparkPostApiMailerService $apiMailer
	    ) {
	        parent::__construct($options);
	        $this->mailer = $apiMailer;
	    }
	}

