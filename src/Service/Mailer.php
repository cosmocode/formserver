<?php

namespace CosmoCode\Formserver\Service;

use CosmoCode\Formserver\Exceptions\MailException;
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
        try {
            $recipients = $form->getMeta('email')['recipients'];
            $subject = $form->getMeta('email')['subject'] ?? 'Formular ausgefüllt';
            $subject = $this->injectFormValues($subject, $form);
            $cc = $this->getCarbonCopyAddresses($form);

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

            if (! empty($cc)) {
                $message->setCc($cc);
            }

            foreach ($this->attachments as $attachment) {
                $message->attach($attachment);
            }

            $this->swiftmailer->send($message);
        } catch (\Exception $e) {
            throw new MailException($e->getMessage());
        }
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
        string $title = null
    ) {
        $htmlHeadline = '<h2>%s</h2>';
        $textHeadline = "\n\n%s\n\n";

        $htmlLine = '<p><strong>%s</strong></p><p>%s</p>';
        $textLine = "\n%s\n%s\n";

        if ($title) {
            $this->textBody .= sprintf("\n\n%s\n\n", $title);
            $this->htmlBody .= sprintf('<h1>%s</h1>', $title);
        }

        /**
         * @var AbstractDynamicFormElement $element
         */
        foreach ($formElements as $element) {
            if ($element instanceof FieldsetFormElement
                && ! $element->isDisabled()
            ) {
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
                // multiple files?
                $files = explode(', ', $value);
                foreach ($files as $file) {
                    $this->attachments[]
                        = \Swift_Attachment::fromPath($formDirectory . $file);
                }
            }

            if ($element instanceof SignatureFormElement && $value) {
                $encoded_image = explode(",", $value)[1];
                $decoded_image = base64_decode($encoded_image);
                $this->attachments[] = (new \Swift_Attachment())
                    ->setFilename($element->getId() . '.jpg')
                    ->setContentType('image/jpeg')
                    ->setBody($decoded_image);

                // do not send the image source data
                $value = LangManager::getString('email_text_attachments');
            }

            $this->textBody .= sprintf($textLine, $label, $value);
            $this->htmlBody .= sprintf($htmlLine, $label, $value);
        }
    }

    /**
     * Replaces {{fieldId}} placeholders with form values
     *
     * @param string $string
     * @param Form $form
     * @return string
     */
    protected function injectFormValues(string $string, Form $form)
    {
        return preg_replace_callback(
            '~{{([a-zA-Z0-9 \.\-\_]*?)}}~',
            function ($matches) use ($form) {
                return $form->getFormElementValue($matches[1]);
            },
            $string
        );
    }

    /**
     * Get carbon copy addresses from form fields
     *
     * @param Form $form
     * @return array
     */
    protected function getCarbonCopyAddresses(Form $form)
    {
        $ccFields = $form->getMeta('email')['cc'] ?? [];

        $cc = [];
        foreach ($ccFields as $ccField) {
            $address = $form->getFormElementValue($ccField);
            if (! empty($address)) {
                $cc[] = $address;
            }
        }

        return $cc;
    }
}
