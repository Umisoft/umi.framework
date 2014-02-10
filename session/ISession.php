<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\session;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Сервис сессии.
 */
interface ISession extends SessionInterface
{
    /**
     * Проверяет, зарегистрирован ли контейнер сессии
     * @param string $name имя контейнера
     * @return bool
     */
    public function hasBag($name);

    /**
     * Создает и регистрирует контейнер сессии
     * @param string $name имя контейнера
     * @throws \InvalidArgumentException если контейнер с таким именем был зарегистрирован
     * @return self
     */
    public function addAttributeBag($name);

}