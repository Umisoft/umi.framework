<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */
namespace umi\messages;

use Swift_Attachment;
use Swift_Message;
use umi\messages\exception\FailedRecipientsException;

/**
 * Сервис отправки почты, декоратор над {@link http://swiftmailer.org/ Swift_Mailer}
 */
class SwiftMailer extends \Swift_Mailer
{
    /**
     * Отправители по умолчанию
     * @var string|array $defaultFrom
     */
    private $defaultFrom;

    /**
     * Адресаты по умолчанию
     * @var string|array $defaultTo
     */
    private $defaultTo;

    /**
     * Сокращение для быстрой отправки почты через Swift Mailer
     *
     * @see Swift_Mailer::send()
     * @param string $subject Тема письма
     * @param string $body Текст письма
     * @param string $contentType Тип контента
     * @param array $files Имена файлов для присоединения
     * @param string|array $to Адресат (1+); если не указано, используется опция delivery_address
     * @param string|array $from Отправитель (1+); если не указано, используется опция sender_address
     * @param string $charset Кодировка письма
     * @throws FailedRecipientsException
     * @return void
     */
    public function sendMail(
        $subject,
        $body,
        $contentType,
        array $files = [],
        $to = null,
        $from = null,
        $charset = 'utf-8'
    ) {
        $msg = new Swift_Message($subject, $body, $contentType, $charset);
        foreach ($files as $attach) {
            $msg->attach(Swift_Attachment::fromPath($attach));
        }
        $msg->setFrom(is_null($from) ? $this->defaultFrom : $from);
        $msg->setTo(is_null($to) ? $this->defaultTo : $to);
        $badAddresses = [];
        $this->send($msg, $badAddresses);
        if (count($badAddresses)) {
            $e = new FailedRecipientsException("Failed to send to some addresses", $badAddresses);
            throw $e;
        }
    }

    /**
     * Устанавливает отправителей по умолчанию
     * @param string|array $defaultFrom
     * @see Swift_Message::setFrom()
     */
    public function setDefaultFrom($defaultFrom)
    {
        $this->defaultFrom = $defaultFrom;
    }

    /**
     * Устанавливает адресатов по умолчанию
     * @param string|array $defaultTo
     * @see Swift_Message::setTo()
     */
    public function setDefaultTo($defaultTo)
    {
        $this->defaultTo = $defaultTo;
    }
}
