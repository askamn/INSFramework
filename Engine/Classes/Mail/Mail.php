<?php

class Mail
{
	/**
	 * The recipient's email
	 *
	 * @var		string
	 */
	public $to;
	
	/**
	 * Subject
	 *
	 * @var		string
	 */
	public $subject = "";
	
	/**
	 * Headers to apply while sending the email
	 *
	 * @var		string
	 */
	public $headers = "";
	
	/**
	 * Message to send
	 *
	 * @var		string
	 */
	public $message = "";
	
	/**
	 * Is it HTML we are trying to send?
	 *
	 * @var		boolean
	 */
	public $html = false;

	/**
	 * Constructor
	 *
	 * @return		void
	 */
	public function __construct($to, $subject, $message, $html=false)
	{
		$this->to = $to;
		$this->subject = $subject;
		$this->message = $message;
		$this->html = $html;
	}
	
	/**
	 * Sends mail
	 *
	 * @return		boolean
	 */
    public function send()
	{
	    global $ins;
		
		/* External SMTP is disabled. Attempt to use PHP inbuilt mail function. */
		if($ins->settings['email']['smtp_enabled'] == 0)
		{
			if($this->html === true)
			{
				$this->headers .= 'MIME-Version: 1.0' . "\r\n";
				$this->headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			}
			if(is_array($this->to))
			{
				$this->to  = explode(", ", $to); 
			}
			
			$this->headers .= "To: {$this->to}" . "\r\n";
			$this->headers .= "From: {$ins->settings['site']['name']} Staff <{$ins->settings['email']['main']}>" . "\r\n";
			$this->headers .= "Cc: {$ins->settings['email']['cc']}" . "\r\n";
			$this->headers .= "Bcc: {$ins->settings['email']['bcc']}" . "\r\n"; 
			
			if(mail($this->to, $this->subject, $this->message, $this->headers))
			{
				return true;
			}	
			else
			{
				return false;
			}	
		}
		else
		{
			require_once "/3rdparty/class.phpmailer.php";
			
			$mail = new PHPMailer;
			$mail->IsSMTP();                                      			// Set mailer to use SMTP
			$mail->Host = 'smtp.gmail.com';                       			// Specify main and backup server
			$mail->Port = 465;                                    			// Mailer's Port
			$mail->SMTPAuth = true;                               			// Enable SMTP authentication
			$mail->Username = $ins->settings['email']['smtp_email'];    	// SMTP username
			$mail->Password = $ins->settings['email']['smtp_password'];     // SMTP password
			$mail->SMTPSecure = 'ssl';                            			// Enable encryption, 'ssl' also accepted
			$mail->From = "{$ins->settings['email']['main']}";
			$mail->FromName = "{$ins->settings['site']['name']} Staff";
			$mail->AddAddress($this->to);
			$mail->AddReplyTo($ins->settings['email']['smtp_username'], "{$ins->settings['site']['name']} Staff");
			$mail->AddCC($ins->settings['email']['cc']);   
			$mail->AddBCC($ins->settings['email']['bcc']); 
			$mail->WordWrap = 50;                           
			$mail->IsHTML($this->html);                          
			$mail->Subject = $this->subject;                	
			$mail->Body    = $this->message;
			$mail->AltBody = $this->message;
			
			if($mail->Send())
			{
				return true; 
			}
			else
			{
				return false;  
			}
		}	
	}
}
?>