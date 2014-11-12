<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\session\toolbox;

use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use umi\session\ISession;
use umi\session\ISessionAware;
use umi\session\Session;
use umi\toolkit\exception\UnsupportedServiceException;
use umi\toolkit\toolbox\IToolbox;
use umi\toolkit\toolbox\TToolbox;

/**
 * Набор инструментов для работы с сессиями.
 */
class SessionTools implements IToolbox
{
    /**
     * Имя набора инструментов.
     */
    const NAME = 'session';

    use TToolbox;

    /**
     * @var array $sessionStorage опции хранилища значений сессии
     */
    public $storage = [];

    /**
     * @var ISession $session сессия
     */
    private $session;

    /**
     * {@inheritdoc}
     */
    public function getService($serviceInterfaceName, $concreteClassName)
    {
        switch ($serviceInterfaceName) {
            case 'umi\session\ISession':
                return $this->getSession();
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
        if ($object instanceof ISessionAware) {
            $object->setSessionService($this->getSession());
        }
    }

    /**
     * Устанавливает сервис сессии
     * @param ISession $session
     */
    public function setSession(ISession $session)
    {
        $this->session = $session;
    }

    /**
     * Возвращает сервис сессии.
     * @return ISession
     */
    protected function getSession()
    {
        if (!$this->session) {
            $this->session = new Session($this->getSessionStorage());
        }
        return $this->session;
    }

    /**
     * Возвращает хранилище значений сессии.
     * @return NativeSessionStorage
     */
    private function getSessionStorage()
    {
        $options = $this->configToArray($this->storage, true);
        return new NativeSessionStorage($options);
    }

}