<?php

declare(strict_types=1);

namespace CosmoCode\Formserver\Service;

use CosmoCode\Formserver\Data\FormData;
use CosmoCode\Formserver\Exceptions\MailException;
use CosmoCode\Formserver\FormGenerator\Form;
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
     * @param array $data
     * @return void
     */
    public function sendForm(Form $form, array $data): void
    {
        if (empty($form->getMeta('email'))) {
            return;
        }

        try {
            $formData = FormData::getFormData($data, $form->getElementsConfig());

            $recipients = $form->getMeta('email')['recipients'];
            $subject = $form->getMeta('email')['subject'] ?? 'Formular ausgefüllt';
            $subject = $this->injectFormValues($subject, $formData);
            $cc = $this->getCarbonCopyAddresses($form, $formData);

            if (empty($recipients) && empty($cc)) {
                return;
            }

            $this->formToMessage(
                $formData,
                $form->getMeta('title')
            );

            $message = new Message();
            $message
                ->setSubject($subject)
                ->setFrom($this->sender)
                ->setHtmlBody($this->htmlBody);

            foreach ($recipients as $recipient) {
                $message->addTo($recipient);
            }

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
     * @param array $formData
     * @param string|null $title
     */
    protected function formToMessage(
        array $formData,
        string $title = null
    ): void {
        $htmlHeadline = '<h2>%s</h2>';

        $htmlLine = '<p><strong>%s</strong></p><p>%s</p>';

        if ($title) {
            $this->htmlBody .= sprintf('<h1>%s</h1>', $title);
        }

        foreach ($formData as $fullId => $element) {
            if ($element['type'] === 'fieldset') {
                $this->htmlBody .= sprintf($htmlHeadline, $element['label']);
                continue;
            }

            $label = $element['label'];
            $value = $element['value'];

            if (empty($value)) {
                continue;
            }

            // special handling of uploads
            if ($element['type'] === 'upload') {
                $files = $value;
                $value = LangManager::getString('email_text_attachments');
                foreach ($files as $file) {
                    $value .=  "\n" . $file['file'];
                    $encoded = explode(",", $file['content'])[1];
                    $decoded = base64_decode($encoded);
                    $this->attachments[]
                        = [$file['file'], $decoded];
                }
            }

            if ($element['type'] === 'signature') {
                $encoded_image = explode(",", $value)[1];
                $decoded_image = base64_decode($encoded_image);
                $this->attachments[] = [$fullId . '.jpg', $decoded_image];

                // do not send the image source data
                $value = LangManager::getString('email_text_attachments');
            }

            // finally flatten all multivalue fields
            if (is_array($value)) {
                $value = implode(", ", $value);
            }

            $this->htmlBody .= sprintf($htmlLine, $label, $value);
        }
    }

    /**
     * Replaces {{fieldId}} placeholders with form values
     *
     * @param string $string
     * @param array $formData
     * @return string
     */
    protected function injectFormValues(string $string, array $formData): string
    {
        return preg_replace_callback(
            '~{{([a-zA-Z0-9 \.\-\_]*?)}}~',
            function ($matches) use ($formData) {
                return isset($formData[$matches[1]]) ? $formData[$matches[1]]['value'] : '';
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
    protected function getCarbonCopyAddresses(Form $form, array $formData): array
    {
        $ccFields = $form->getMeta('email')['cc'] ?? [];

        $cc = [];
        foreach ($ccFields as $ccField) {
            if (isset($formData[$ccField])) {
                $cc[] = $formData[$ccField]['value'];
            }
        }

        return $cc;
    }
}
