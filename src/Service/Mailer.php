<?php

namespace CosmoCode\Formserver\Service;

use CosmoCode\Formserver\Exceptions\MailException;
use CosmoCode\Formserver\FormGenerator\Form;
use CosmoCode\Formserver\FormGenerator\FormElements\AbstractDynamicFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\FieldsetFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\SignatureFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\UploadFormElement;
use Nette\Mail\Message;
use Nette\Mail\SmtpMailer;

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
    protected string $sender;

    /** @var SmtpMailer  */
    protected SmtpMailer $mailer;

    /**
     * @var string
     */
    protected string $htmlBody = '';

    /**
     * @var array
     */
    protected array $attachments = [];

    /**
     * Pass mailer configuration
     *
     * @param array $mailerConfiguration
     */
    public function __construct(array $mailerConfiguration)
    {
        $this->sender = $mailerConfiguration['sender'];

        $this->mailer = new SmtpMailer(
            host: $mailerConfiguration['host'],
            username: $mailerConfiguration['username'] ?? '',
            password: $mailerConfiguration['password'] ?? '',
            port: $mailerConfiguration['port'],
            encryption: $mailerConfiguration['encryption']
        );
    }

    /**
     * Prepares the data of a form and sends it
     *
     * @param Form $form
     * @return void
     */
    public function sendForm(Form $form): void
    {
        if (empty($form->getMeta('email'))) {
            return;
        }

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

            $message = new Message();
            $message
                ->setSubject($subject)
                ->setFrom($this->sender)
                ->addTo(...$recipients)
                ->setHtmlBody($this->htmlBody);

            if (! empty($cc)) {
                $message->addCc(...$cc);
            }

            foreach ($this->attachments as $attachment) {
                $message->addAttachment(...(array)$attachment);
            }

            $this->mailer->send($message);
        } catch (\Exception $e) {
            throw new MailException($e->getMessage());
        }
    }

    /**
     * Convert form data to message parts and attachments
     *
     * @param array $formElements
     * @param string $formDirectory
     * @param string|null $title
     */
    protected function formToMessage(
        array $formElements,
        string $formDirectory,
        string $title = null
    ): void {
        $htmlHeadline = '<h2>%s</h2>';

        $htmlLine = '<p><strong>%s</strong></p><p>%s</p>';

        if ($title) {
            $this->htmlBody .= sprintf('<h1>%s</h1>', $title);
        }

        /**
         * @var AbstractDynamicFormElement $element
         */
        foreach ($formElements as $element) {
            if (
                $element instanceof FieldsetFormElement
                && ! $element->isDisabled()
            ) {
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

            // special handling of uploads
            if ($element instanceof UploadFormElement && $value) {
                $value = '';
                $files = $element->getUploadedFiles();
                foreach ($files as $file) {
                    $value .= $file['address']
                        . ' (' . LangManager::getString('uploaded_original')
                        . ' ' . $file['name'] . ') ';
                    $this->attachments[]
                        = [$file['name'], file_get_contents($formDirectory . $file['address'])];
                }
            }

            if ($element instanceof SignatureFormElement && $value) {
                $encoded_image = explode(",", $value)[1];
                $decoded_image = base64_decode($encoded_image);
                $this->attachments[] = [$element->getId() . '.jpg', $decoded_image];

                // do not send the image source data
                $value = LangManager::getString('email_text_attachments');
            }

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
    protected function injectFormValues(string $string, Form $form): string
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
    protected function getCarbonCopyAddresses(Form $form): array
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
