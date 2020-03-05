<?php
require_once './vendor/autoload.php';
require_once './helper/errorHandler.php';
class sendMail {
    private $smtp = 'smtp.mailtrap.io';
    private $port = 2525;
    private $username = 'f1ffc8f4a696e9';
    private $password = '8d487d46a45a65';
    private $transport;
    private $mailer;
    private $message;
    private $from = ['info@bruidenapp.nl' => 'BruidenApp'];


    function __construct($subject, $to, $message)
    {
        $this->transport = (new Swift_SmtpTransport($this->smtp, $this->port));
        $this->transport->setUsername($this->username);
        $this->transport->setPassword($this->password);
        $this->mailer = new Swift_Mailer($this->transport);
        $this->message = new Swift_Message();
        $this->message->setSubject($subject);
        $this->message->setFrom($this->from);
        $this->message->addTo($to);
        $this->message->setBody($message);
        $this->message->setContentType("text/html");
    }
    
    function sendMail()
    {
        try {
            $result = $this->mailer->send($this->message);
            return true;
        } catch (Exception $e) {
        $error = new errorHandler('Maildienst buiten gebruik', 503);
        return $error;
        }
    }
}
?>