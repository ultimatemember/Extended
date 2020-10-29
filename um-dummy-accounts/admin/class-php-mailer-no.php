<?php
/**
 * Disable emails
 */

require_once ABSPATH . WPINC . '/class-phpmailer.php';
require_once ABSPATH . WPINC . '/class-smtp.php';

class PHPMailerNO extends PHPMailer {

	public function send() {
		return true;
	}

}
