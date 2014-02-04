<?php
require_once 'swift/swift_required.php';

/**
 * SwiftMailer : send mail using Swift
 * @see lang::mail
 * @author Tilotiti
 */
class SwiftMail extends mail {
    
	/**
	 * Called by mail::send if sendmail found
	 * @return number of mails sent
	 */
	public function send() {
		$message = Swift_Message::newInstance();
		
		$message->setSubject($this->subject)
	  			->setFrom($this->from)
	  			->setTo($this->to)
	  			->setBody($this->message,'text/html',CHARSET);
	  			
		if ($this->bcc && count((array) $this->bcc)>0) {
			$message->setBcc((array) $this->bcc);
		}
		if ($this->cc && count((array) $this->cc)>0) {
			$message->setCc((array) $this->cc);
		}
		if ($this->replyto && count((array) $this->replyto)>0) {
			$message->setReplyTo((array) $this->replyto);
		}
		if ($this->confirm && count((array) $this->confirm)>0) {
			$message->setReadReceiptTo($this->confirm);
		}
		// use MTA
		$transport = Swift_SendmailTransport::newInstance('/usr/sbin/sendmail -t');
		$mailer = Swift_Mailer::newInstance($transport);
		
		return $mailer->send($message);
	}
	
	// TODO attachments
	
}