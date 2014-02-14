<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\messages\toolbox;

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
     * @var array $sessionStorage настройки почтовой службы
     */
    public $mailerOptions = [
        'encryption' => null,
        'auth_mode' => null,
        'transport' => null,
        'host' => null,
        'port' => null,
        'username' => null,
        'password' => null,
        'sender_address' => [],
        'delivery_address' => [],
    ];

    /**
     * @var SwiftMailer $mailer почтовая служба
     */
    private $mailer;
    /**
     * @var \Swift_Transport $transport транспорт для почтовой слудбы,
     * может быть подменен до первого обращения к самой почтовой службе
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
     * Создает сервис отправки почты в соответствии с настройками тулбокса
     * @throws \umi\messages\exception\InvalidArgumentException
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
     * Создает почтовый транспорт согласно настройкам тулбокса
     * @return \Swift_Transport
     * @throws InvalidArgumentException
     */
    private function createTransportFromConfig()
    {
        if (!isset($this->mailerOptions['transport'])) {
            throw new InvalidArgumentException("Transport type is not specified");
        }
        if ('smtp' === $this->mailerOptions['transport']) {
            if (!isset($this->mailerOptions['host'])) {
                throw new InvalidArgumentException("smtp mailer host must be specified");
            }
            $transport = \Swift_SmtpTransport::newInstance($this->mailerOptions['host']);
            if (isset($this->mailerOptions['port'])) {
                $transport->setPort($this->mailerOptions['port']);
            }
            if (isset($this->mailerOptions['encryption'])) {
                $transport->setEncryption($this->mailerOptions['encryption']);
            }
            if (isset($this->mailerOptions['username'])) {
                $transport->setUsername(isset($this->mailerOptions['username']));
                if (isset($this->mailerOptions['password'])) {
                    $transport->setPassword(isset($this->mailerOptions['password']));
                }
            }
            if (isset($this->mailerOptions['auth_mode'])) {
                $transport->setAuthMode(isset($this->mailerOptions['auth_mode']));
            }
            if (isset($this->mailerOptions['timeout'])) {
                $transport->setTimeout(isset($this->mailerOptions['timeout']));
            }
            if (isset($this->mailerOptions['source_ip'])) {
                $transport->setSourceIp(isset($this->mailerOptions['source_ip']));
            }
        } elseif ('sendmail' === $this->mailerOptions['transport']) {
            $transport = \Swift_SendmailTransport::newInstance();
        } elseif ('mail' === $this->mailerOptions['transport']) {
            $transport = \Swift_MailTransport::newInstance();
        } else {
            throw new InvalidArgumentException("Transport type {$this->mailerOptions['transport']} is not supported");
        }
        return $transport;
    }

    /**
     * Возвращает почтовый транспорт
     * @return \Swift_Transport
     */
    protected function getTransport()
    {
        if (is_null($this->transport)) {
            $this->transport = $this->createTransportFromConfig();
        }
        return $this->transport;
    }

    /**
     * Устанавливает транспорт, выбрасывает исключение, если почтовый сервис уже работает
     * @param \Swift_Transport $transport
     * @throws LogicException
     */
    public function setTransport(\Swift_Transport $transport)
    {
        if (!is_null($this->mailer)) {
            throw new LogicException("Cannot set up transport after mailer created");
        }
        $this->transport = $transport;
    }
}
