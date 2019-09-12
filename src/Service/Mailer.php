<?php

namespace CosmoCode\Formserver\Service;


use CosmoCode\Formserver\FormGenerator\Form;
use CosmoCode\Formserver\FormGenerator\FormElements\AbstractDynamicFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\FieldsetFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\SignatureFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\UploadFormElement;
use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;

/**
 * Mail Service to send completed forms via email
 *
 * @package CosmoCode\Formserver\Service
 */
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

    /**
     * @var string
     */
    protected $htmlBody = '';

    /**
     * @var string
     */
    protected $textBody = '';

    /**
     * @var array
     */
    protected $attachments = [];

    /**
     * Pass mail configuration
     *
     * @param array $mailerConfiguration
     */
    public function __construct(array $mailerConfiguration)
    {
        $this->sender = $mailerConfiguration['sender'];

        $transport = new Swift_SmtpTransport(
            $mailerConfiguration['host'],
            $mailerConfiguration['port'],
            $mailerConfiguration['encryption']
        );

        if (! empty($mailerConf['username']) && ! empty($mailerConf['password'])) {
            $transport->setUsername($mailerConf['username']);
            $transport->setPassword($mailerConf['password']);
        }

        $this->swiftmailer = new Swift_Mailer($transport);
    }

    /**
     * Prepares the data of a form and sends it
     *
     * @param Form $form
     * @return void
     */
    public function sendForm(Form $form)
    {
        $recipients = $form->getMeta('email')['recipients'];
        $subject = $emailConf['subject'] ?? 'Formular ausgefüllt';


        $this->formToMessage(
            $form->getFormElements(),
            $form->getFormDirectory(),
            $form->getMeta('title')
        );

        $message = new Swift_Message();
        $message
            ->setSubject($subject)
            ->setFrom($this->sender)
            ->setTo($recipients)
            ->setBody($this->htmlBody, 'text/html')
            ->addPart($this->textBody, 'text/plain');

        foreach ($this->attachments as $attachment) {
            $message->attach($attachment);
        }

        $this->swiftmailer->send($message);
    }

    /**
     * Convert form data to message parts and attachments
     *
     * @param array $formElements
     * @param string $formDirectory
     * @param string $title
     * @return string
     */
    protected function formToMessage(
        array $formElements,
        string $formDirectory,
        string $title = ''
    ) {
        $htmlHeadline = '<h2>%s</h2>';
        $textHeadline = "\n\n%s\n\n";

        $htmlLine = '<p><strong>%s:</strong></p><p>%s</p>';
        $textLine = "\n%s:\n%s\n";

        if ($title) {
            $this->textBody .= sprintf("\n\n%s\n\n", $title);
            $this->htmlBody .= sprintf('<h1>%s</h1>', $title);
        }

        /**
         * @var AbstractDynamicFormElement $element
         */
        foreach ($formElements as $element) {
            if ($element instanceof FieldsetFormElement) {
                $this->textBody
                    .= sprintf($textHeadline, $element->getConfigValue('label'));
                $this->htmlBody
                    .= sprintf($htmlHeadline, $element->getConfigValue('label'));
                $this->formToMessage($element->getChildren(), $formDirectory);
            }

            // skip static elements
            if (! $element instanceof AbstractDynamicFormElement) {
                continue;
            }

            $label = $element->getConfigValue('label');
            $value = $element->getValueString();

            if ($element instanceof UploadFormElement && $value) {
                $this->attachments[]
                    = \Swift_Attachment::fromPath($formDirectory . $value);
            }
            if ($element instanceof SignatureFormElement && $value) {
                $encoded_image = explode(",", $value)[1];
                $decoded_image = base64_decode($encoded_image);
                $this->attachments[] = (new \Swift_Attachment())
                    ->setFilename($element->getId() . '.jpg')
                    ->setContentType('image/jpeg')
                    ->setBody($decoded_image);

                // FIXME no hardcoded text
                // do not send the image source data
                $value = 'Siehe Anhang';
            }

            $this->textBody .= sprintf($textLine, $label, $value);
            $this->htmlBody .= sprintf($htmlLine, $label, $value);

        }
    }
}
