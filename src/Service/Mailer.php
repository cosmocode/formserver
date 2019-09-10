<?php

namespace CosmoCode\Formserver\Service;


use CosmoCode\Formserver\FormGenerator\Form;
use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;

class Mailer
{
    /**
     * @var string
     */
    protected $sender;

    /**
     * @var Swift_Mailer
     */
    protected $swiftmailer;

    /**
     * @var array
     */
    protected $recipients = [];

    public function __construct(array $mailerConfiguration)
    {
        $this->sender = $mailerConfiguration['sender'];

        $transport = new Swift_SmtpTransport(
            $mailerConfiguration['host'],
            $mailerConfiguration['port'],
            $mailerConfiguration['encryption']
        );

        if (!empty($mailerConf['username']) && !empty($mailerConf['password'])) {
            $transport->setUsername($mailerConf['username']);
            $transport->setPassword($mailerConf['password']);
        }

        $this->swiftmailer = new Swift_Mailer($transport);
    }

    public function sendForm(array $emailConf, array $data) {
        $recipients = $emailConf['recipients'];
        $subject = $emailConf['subject'] ?? 'Formular ausgefüllt';
        $body = json_encode($data);

        $message = new Swift_Message();
        $message
            ->setSubject($subject)
            ->setFrom($this->sender)
            ->setTo($recipients)
            ->setBody($body, 'text/html');

        $this->swiftmailer->send($message);
    }

}