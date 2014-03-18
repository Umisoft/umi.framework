<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\messages\toolbox;

use Swift_MailTransport;
use Swift_SendmailTransport;
use Swift_SmtpTransport;
use Swift_Transport;
use umi\messages\exception\InvalidArgumentException;
use umi\messages\exception\LogicException;
use umi\messages\ISwiftMailerAware;
use umi\messages\SwiftMailer;
use umi\toolkit\exception\UnsupportedServiceException;
use umi\toolkit\toolbox\IToolbox;
use umi\toolkit\toolbox\TToolbox;

/**
 * Набор инструментов для отправки сообщений при помощи вспомогательных внутренних сервисов.
 */
class MessagesTools implements IToolbox
{
    /**
     * Имя набора инструментов.
     */
    const NAME = 'messages';

    use TToolbox;

    /**
     * @var array $mailerOptions настройки почтовой службы
     */
    public $mailerOptions = [
        'transport' => 'mail',
        'sender_address' => [],
        'delivery_address' => [],
    ];

    public $transportOptions = [
        'smtp' => [
            'encryption' => null,
            'auth_mode' => null,
            'host' => null,
            'port' => null,
            'username' => null,
            'password' => null,
        ],
        'mail' => [],
        'sendmail' => [],
    ];

    /**
     * Почтовая служба
     * @var SwiftMailer $mailer
     */
    private $mailer;
    /**
     * Транспорт для почтовой службы, может быть подменен до первого обращения к самой почтовой службе
     * @var Swift_Transport $transport
     */
    private $transport;

    /**
     * {@inheritdoc}
     */
    public function getService($serviceInterfaceName, $concreteClassName)
    {
        switch ($serviceInterfaceName) {
            case 'umi\messages\SwiftMailer':
                return $this->getMailer();
        }
        throw new UnsupportedServiceException($this->translate(
            'Toolbox "{name}" does not support service "{interface}".',
            ['name' => self::NAME, 'interface' => $serviceInterfaceName]
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function injectDependencies($object)
    {
        if ($object instanceof ISwiftMailerAware) {
            $object->setSwiftMailer($this->getMailer());
        }
    }

    /**
     * Возвращает сервис отправки электронной почты.
     * @return SwiftMailer
     */
    protected function getMailer()
    {
        if (!$this->mailer) {
            $this->mailer = $this->createMailer();
        }
        return $this->mailer;
    }

    /**
     * Создает сервис отправки почты в соответствии с настройками тулбокса.
     * @throws InvalidArgumentException
     * @return SwiftMailer
     */
    protected function createMailer()
    {
        if (!isset($this->mailerOptions['sender_address'])) {
            throw new InvalidArgumentException("No default sender address");
        }
        if (!isset($this->mailerOptions['delivery_address'])) {
            throw new InvalidArgumentException("No default delivery address");
        }
        $swiftMailer = new SwiftMailer($this->getTransport());
        $swiftMailer->setDefaultFrom($this->mailerOptions['sender_address']);
        $swiftMailer->setDefaultTo($this->mailerOptions['delivery_address']);
        return $swiftMailer;
    }

    /**
     * Создает почтовый транспорт согласно настройкам тулбокса.
     * @return Swift_Transport
     * @throws InvalidArgumentException
     */
    private function createTransportFromConfig()
    {
        if (!isset($this->mailerOptions['transport'])) {
            throw new InvalidArgumentException("Transport type is not specified");
        }
        switch ($this->mailerOptions['transport']) {
            case 'smtp':
                $transport = $this->createSmtpTransport();
                break;
            case 'mail':
                $transport = $this->createMailTransport();
                break;
            case 'sendmail':
                $transport = $this->createSendmailTransport();
                break;
            default:
                throw new InvalidArgumentException(
                    "Transport type {$this->mailerOptions['transport']} is not supported"
                );
                break;
        }
        return $transport;
    }

    /**
     * Возвращает почтовый транспорт.
     * @return Swift_Transport
     */
    protected function getTransport()
    {
        if (is_null($this->transport)) {
            $this->transport = $this->createTransportFromConfig();
        }
        return $this->transport;
    }

    /**
     * Устанавливает транспорт, выбрасывает исключение, если почтовый сервис уже работает.
     * @param Swift_Transport $transport
     * @throws LogicException
     */
    public function setTransport(Swift_Transport $transport)
    {
        if (!is_null($this->mailer)) {
            throw new LogicException("Cannot set up transport after mailer created");
        }
        $this->transport = $transport;
    }

    /**
     * @return Swift_SmtpTransport
     * @throws InvalidArgumentException
     */
    protected function createSmtpTransport()
    {
        if (!isset($this->transportOptions['smtp'])) {
            throw new InvalidArgumentException("SMTP mailer transport options not specified");
        }
        $transportOptions = $this->transportOptions['smtp'];

        if (!isset($transportOptions['host'])) {
            throw new InvalidArgumentException("SMTP mailer host not specified");
        }
        $transport = Swift_SmtpTransport::newInstance($transportOptions['host']);
        if (isset($transportOptions['port'])) {
            $transport->setPort($transportOptions['port']);
        }
        if (isset($transportOptions['encryption'])) {
            $transport->setEncryption($transportOptions['encryption']);
        }
        if (isset($transportOptions['username'])) {
            $transport->setUsername($transportOptions['username']);
            if (isset($transportOptions['password'])) {
                $transport->setPassword($transportOptions['password']);
            }
        }
        if (isset($transportOptions['auth_mode'])) {
            $transport->setAuthMode($transportOptions['auth_mode']);
        }
        if (isset($transportOptions['timeout'])) {
            $transport->setTimeout($transportOptions['timeout']);
        }
        if (isset($transportOptions['source_ip'])) {
            $transport->setSourceIp($transportOptions['source_ip']);
            return $transport;
        }
        return $transport;
    }

    /**
     * @return Swift_SendmailTransport
     */
    protected function createSendmailTransport()
    {
        return Swift_SendmailTransport::newInstance();
    }

    /**
     * @return Swift_MailTransport
     */
    protected function createMailTransport()
    {
        return Swift_MailTransport::newInstance();
    }
}
