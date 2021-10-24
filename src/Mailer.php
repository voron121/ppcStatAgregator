<?php
/**
 * Класс реализующий логику отправки писем с сервиса.
 * TODO: реализовать поддержку HTML шаблонов
 */

namespace PPCSoft;

use Swift_Mailer;
use Swift_MailTransport;
use Swift_Message;
use Swift_SmtpTransport;

class Mailer
{
    protected $mailer = null;
    protected $message = null;
    protected $transport = null;
    protected $recipient = [];
    protected $subject = "PPCSoft info message";

    public function __construct()
    {
        $this->transport = (new Swift_SmtpTransport(SMTP_HOST, SMTP_PORT))
            ->setUsername(SMTP_USER)
            ->setPassword(SMTP_PASSWORD);
        $this->mailer = new Swift_Mailer($this->transport);
        $this->message = new Swift_Message();
    }

    /**
     * @param $recipient
     */
    public function setRecipients($recipient) : void
    {
        if (is_array($recipient)) {
            $this->recipient = array_merge($this->recipient, $recipient);
        } else {
            $this->recipient[] = $recipient;
        }
    }

    /**
     * @param string $template
     * @param string $messageText
     * @return string
     */
    protected function getMailTemplate(string $template, string $messageText) : string
    {
        $template = "<p>{$messageText}</p>";
        if ("error" === $template) {
            $template = "<div><p>{$messageText}</p></div>";
        }
        return $template;
    }

    /**
     * @param string $subject
     */
    public function setSubject(string $subject) : void
    {
        $this->subject = $subject;
    }

    /**
     * @param string $messageText
     * @param string $template
     * @throws \Exception
     */
    public function setMessage(string $messageText, string $template = "default", array $params = []) : void
    {
        $this->message->setFrom([SMTP_USER => 'PPCSoft.pro'])
            ->setTo($this->recipient)
            ->setSubject($this->subject)
            ->setBody($this->getMailTemplate($template, $messageText), 'text/html');
    }

    /**
     * @return bool
     */
    public function send() : bool
    {
        return $this->mailer->send($this->message);
    }
}