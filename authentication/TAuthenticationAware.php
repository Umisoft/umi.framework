<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\authentication;

use umi\authentication\adapter\IAuthAdapter;
use umi\authentication\exception\RequiredDependencyException;
use umi\authentication\provider\IAuthProvider;
use umi\authentication\storage\IAuthStorage;

/**
 * Трейт для внедрения поддержки аутентификации.
 */
trait TAuthenticationAware
{
    /**
     * @var IAuthenticationFactory $traitAuthFactory фабрика компонентов авторизации
     */
    private $traitAuthFactory;

    /**
     * @see IAuthenticationAware::setAuthenticationFactory()
     */
    public function setAuthenticationFactory(IAuthenticationFactory $authFactory)
    {
        $this->traitAuthFactory = $authFactory;
    }

    /**
     * Возвращает сконфигурированный адаптер.
     * @param array $config конфигурация адаптера
     * @return IAuthAdapter
     */
    protected function createAuthAdapter(array $config = [])
    {
        return $this->getAuthFactory()
            ->createAdapter($config);
    }

    /**
     * Возвращает сконфигурированный storage.
     * @param array $config конфигурация хранилища
     * @return IAuthStorage
     */
    protected function createAuthStorage(array $config = [])
    {
        return $this->getAuthFactory()
            ->createStorage($config);
    }

    /**
     * Возвращает сконфигурированный провайдер.
     * @param string $type тип провайдера
     * @param array $constructorArgs аргументы конструктора провайдера
     * @return IAuthProvider
     */
    protected function createAuthProvider($type, array $constructorArgs = [])
    {
        return $this->getAuthFactory()
            ->createProvider($type, $constructorArgs);
    }

    /**
     * Создает менеджер аутентификации.
     * @param IAuthAdapter $adapter адаптер аутентификации
     * @param IAuthStorage $storage хранилище аутентификации
     * @param array $options опции менеджера аутентификации
     * @return IAuthManager
     */
    protected function createAuthManager(IAuthAdapter $adapter, IAuthStorage $storage, array $options = [])
    {
        return $this->getAuthFactory()
            ->createAuthManager($adapter, $storage, $options);
    }

    /**
     * Возвращает менеджер аутентификации с натройками по умолчанию.
     * @return IAuthManager
     */
    protected function getDefaultAuthManager()
    {
        return $this->getAuthFactory()->getDefaultAuthManager();
    }

    /**
     * Возвращает фабрику компонентов авторизации.
     * @throws RequiredDependencyException если фабрика не была установлена
     * @return IAuthenticationFactory
     */
    private function getAuthFactory()
    {
        if (!$this->traitAuthFactory) {
            throw new RequiredDependencyException(sprintf(
                'Authentication factory is not injected in class "%s".',
                get_class($this)
            ));
        }

        return $this->traitAuthFactory;
    }
}
