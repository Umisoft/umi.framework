<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\session;

use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;
use umi\session\exception\RequiredDependencyException;

/**
 * Трейт для внедрения поддержки сессии.
 */
trait TSessionAware
{
    /**
     * @var ISession $_sessionService
     */
    private $_sessionService;
    /**
     * @var AttributeBagInterface $_sessionBag
     */
    private $_sessionBag;

    /**
     * @param ISession $sessionService сервис сесии
     */
    public final function setSessionService(ISession $sessionService)
    {
        $this->_sessionService = $sessionService;
    }

    /**
     * Возвращает имя контейнера сессии.
     * @return string
     */
    protected function getSessionBagName()
    {
        return get_class($this);
    }

    /**
     * Проверяет, существует ли переменная в сессии.
     * @param string $name имя переменной
     * @return boolean
     */
    protected function hasSessionVar($name)
    {
        return $this->getSessionBag()->has($name);
    }

    /**
     * Возвращает переменную из сессии.
     * @param string $name имя переменной
     * @param mixed $default значение переменной по умолчанию
     * @return mixed
     */
    protected function getSessionVar($name, $default = null)
    {
        return $this->getSessionBag()->get($name, $default);
    }

    /**
     * Устанавливает значение переменной в сессии.
     * @param string $name имя переменной
     * @param mixed $value значение переменной
     * @return $this
     */
    protected function setSessionVar($name, $value)
    {
        $this->getSessionBag()->set($name, $value);

        return $this;
    }

    /**
     * Возвращает все переменные из сессии.
     * @return array
     */
    protected function getSessionVars()
    {
        return $this->getSessionBag()->all();
    }

    /**
     * Sets attributes.
     *
     * @param array $attributes Attributes
     */

    /**
     * Заменяет все значения в сессии.
     * @param array $attributes переменные
     * @return $this
     */
    protected function replaceSessionVars(array $attributes)
    {
        $this->getSessionBag()->replace($attributes);

        return $this;
    }

    /**
     * Удвляет переменную из сессии.
     * @param string $name имя переменной
     * @return mixed значение удаленной переменной или null, если переменной не было
     */
    protected function removeSessionVar($name)
    {
        return $this->getSessionBag()->remove($name);
    }

    /**
     * Возвращает контейнер сессии.
     * @return AttributeBagInterface
     */
    private function getSessionBag()
    {
        if (!$this->_sessionBag) {

            $bagName = $this->getSessionBagName();

            if (!$this->getSession()->hasBag($bagName)) {
                $this->getSession()->addAttributeBag($bagName);
            }

            $this->_sessionBag = $this->getSession()->getBag($bagName);
        }

        return $this->_sessionBag;
    }

    /**
     * Возвращает сервис сессии.
     * @return ISession
     * @throws RequiredDependencyException если сервис не был внедрен.
     */
    private function getSession()
    {
        if (!$this->_sessionService) {
            throw new RequiredDependencyException(sprintf(
                'Session service is not injected in class "%s".',
                get_class($this)
            ));
        }

        return $this->_sessionService;
    }
}