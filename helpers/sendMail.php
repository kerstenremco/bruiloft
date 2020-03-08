<?php
namespace helpers;

use Exception;

class sendMail {
    private $transport;
    private $mailer;
    private $message;

    function __construct($subject, $to, $message)
    {
        $this->transport = (new \Swift_SmtpTransport(MAIL_SMTP, MAIL_PORT));
        $this->transport->setUsername(MAIL_USERNAME);
        $this->transport->setPassword(MAIL_PASSWORD);
        $this->mailer = new \Swift_Mailer($this->transport);
        $this->message = new \Swift_Message();
        $this->message->setSubject($subject);
        $this->message->setFrom(MAIL_FROM);
        $this->message->addTo($to);
        $this->message->setBody($message);
        $this->message->setContentType("text/html");
    }
    
    function sendMail()
    {
        try {
            $result = $this->mailer->send($this->message);
            return true;
        } catch (\Exception $e) {
        throw new Exception('Fout bij versturen van email, probeer het later nogmaals', 500);
        }
    }
}
?>