<?php

use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\PHPMailer;

class Mailer {

	static private $from;

	static private $smtp;

	static function setFrom($address, $name = null) {
		self::$from = compact("address", "name");
	}

	static function setSMTP($user, $password, $host = "smtp.gmail.com", $type = "tls", $port = 587, $insecure = false) {
		self::$smtp = compact("user", "password", "host", "type", "port", "insecure");
	}

	static function send($to, $subject, $view, array $data, array $options = []) {
		$mail = new PHPMailer(true);

		if (self::$from)
			$mail->setFrom(self::$from["address"], self::$from["name"]);

		if (is_array($to)) {
			$mail->addAddress($to[0], $to[1]);
		} else {
			$mail->addAddress($to);
		}

		if ($replyTo = @$options["replyTo"]) {
			if (is_array($replyTo)) {
				$mail->addReplyTo($replyTo[0], $replyTo[1]);
			} else {
				$mail->addReplyTo($replyTo);
			}
		}

		$mail->isHTML(true);
		$mail->CharSet = "UTF-8";
		$mail->Subject = $subject;
		$mail->Body = view($view, $data);

		if (self::$smtp) {
			$mail->isSMTP();
			$mail->Host = self::$smtp["host"];
			$mail->SMTPAuth = true;
			$mail->Username = self::$smtp["user"];
			$mail->Password = self::$smtp["password"];
			$mail->SMTPSecure = self::$smtp["type"];
			$mail->Port = self::$smtp["port"];

			if (true || self::$smtp["insecure"]) {
				$mail->SMTPOptions = [
					"ssl" => [
						"verify_peer" => false,
						"verify_peer_name" => false,
						"allow_self_signed" => true
					]
				];
			}
		}

		$mail->Send();
	}
}
