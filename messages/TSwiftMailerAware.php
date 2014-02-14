<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\messages;

use umi\messages\exception\RequiredDependencyException;

/**
 * Трейт для внедрения почтового сервиса.
 */
trait TSwiftMailerAware
{
    /**
     * Ссылка на сервис отправки почты
     * @var SwiftMailer $traitSwiftMailer
     */
    private $traitSwiftMailer;

    /**
     * Внедряет почтовый сервис
     * @param \umi\messages\SwiftMailer $mailer сервис отправки почты
     */
    public function setSwiftMailer(SwiftMailer $mailer)
    {
        $this->traitSwiftMailer = $mailer;
    }

    /**
     * Возвращает внедренный почтовый сервис
     * @throws exception\RequiredDependencyException
     * @return \umi\messages\SwiftMailer
     */
    protected function getSwiftMailer()
    {
        if (!$this->traitSwiftMailer) {
            throw new RequiredDependencyException(sprintf(
                'Swift Mailer is not injected in class "%s".',
                get_class($this)
            ));
        }
        return $this->traitSwiftMailer;
    }

    /**
     * @see SwiftMailer::sendMail()
     */
    protected function sendMail(
        $subject,
        $body,
        $contentType,
        array $files = [],
        $to = null,
        $from = null,
        $charset = 'utf-8'
    ) {
        $this->getSwiftMailer()
            ->sendMail($subject, $body, $contentType, $files, $to, $from, $charset);
    }

}
