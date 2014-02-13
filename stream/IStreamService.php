<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\stream;

use umi\stream\exception\AlreadyRegisteredException;
use umi\stream\exception\NotRegisteredException;
use umi\stream\exception\RuntimeException;

/**
 * Сервис для работы с потоками.
 */
interface IStreamService
{
    /**
     * Проверяет, зарегистрирован ли поток.
     * @param string $protocol имя протокола
     * @return bool
     */
    public function hasStream($protocol);

    /**
     * Регистрирует поток для протокола.
     * @param string $protocol имя протокола
     * @param callable $handler обработчик, возвращающий результат работы стрима, первым параметром получает $uri
     * @throws AlreadyRegisteredException если поток уже был зарегистрирован
     * @throws RuntimeException если не удалось зарегистрировать поток
     * @return self
     */
    public function registerStream($protocol, callable $handler);

    /**
     * Выгружает зарегистрированный поток и отменяет регистрацию протокола.
     * @param string $protocol имя протокола
     * @throws NotRegisteredException если поток не был зарегистрирован
     * @return bool результат операции
     */
    public function unregisterStream($protocol);

    /**
     * Запускает обработчик потока и возвращает результат работы.
     * @param string $uri запрос к потоку
     * @throws NotRegisteredException если поток не был зарегистрирован
     * @return string результат работы обработчика
     */
    public function executeStream($uri);
}
