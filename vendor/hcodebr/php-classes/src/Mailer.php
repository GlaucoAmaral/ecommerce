/<?php 

namespace HCode;


//vamos usar a classe phpMailer, como ela esta no escopo principal e nao no vendor, nao precisamos coloco o user phpmailer..


use Rain\Tpl();




class Mailer {
	const USERNAME = "testeprojetophp2@gmail.com";
	const PASSWORD = "joaodasilva123";
	const NAME_FROM = "Hcode Store";

	private $this->mail;

	public function __construct($toAddress, $toName, $subject, $tplName, $data = array())
	{

		$config = array(
			"tpl_dir"       => $_SERVER["DOCUMENT_ROOT"] . "/views/email",
			"cache_dir"     => $_SERVER["DOCUMENT_ROOT"] . "/views-cache/",
			"debug"         => false
			);

		Tpl::configure( $config );

		$tpl = new Tpl();

		//passando os dados para o template
		foreach ($data as $key => $value) {
			$tpl->assign($key, $value);
		}

		$html = $tpl->draw($tplName, true);//true para nao jogar na tela e sim na variavel


		//Create a new PHPMailer instance
		$this->mail = new \PHPMailer();
		//$mail = new PHPMailer\PHPMailer\PHPMailer();

		//Tell PHPMailer to use SMTP
		$this->mail->isSMTP();
		$this->mail->SMTPOptions = array(
		    'ssl' => array(
		        'verify_peer' => false,
		        'verify_peer_name' => false,
		        'allow_self_signed' => true
		    )
		);

		//Enable SMTP debugging
		// 0 = off (for production use)
		// 1 = client messages
		// 2 = client and server messages
		$this->mail->SMTPDebug = 0;

		//Set the hostname of the mail server
		$this->mail->Host = 'smtp.gmail.com';
		// use
		// $this->mail->Host = gethostbyname('smtp.gmail.com');
		// if your network does not support SMTP over IPv6

		//Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
		$this->mail->Port = 587;

		//Set the encryption system to use - ssl (deprecated) or tls
		$this->mail->SMTPSecure = 'tls';

		//Whether to use SMTP authentication
		$this->mail->SMTPAuth = true;

		//Username to use for SMTP authentication - use full email address for gmail
		$this->mail->Username = MAiler::USERNAME;

		//Password to use for SMTP authentication
		$this->mail->Password = Mailer::PASSWORD;

		//Set who the message is to be sent from
		$this->mail->setFrom(Mailer::USERNAME, MAiler::NAME_FROM);

		//Set an alternative reply-to address
		//$mail->addReplyTo('replyto@example.com', 'First Last');

		//Set who the message is to be sent to
		$this->mail->addAddress($toAddress, $toName);

		//Set the subject line
		$this->mail->Subject = $subject;

		//Read an HTML message body from an external file, convert referenced images to embedded,
		//convert HTML into a basic plain-text alternative body
		$this->mail->msgHTML();

		//Replace the plain text body with one created manually
		$this->mail->AltBody = 'This is a plain-text message body';

		//Attach an image file
		//$mail->addAttachment('images/phpmailer_mini.png');

	}

	public function send()
	{
		return $this->mail->send();
	}



}



 ?>