<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\http;

/**
 * Фабрика HTTP-сущностей.
 */
interface IHttpFactory
{
    /**
     * Создает HTTP-запрос к серверу из глобальных переменных.
     * @return Request
     */
    public function createRequest();

    /**
     * Создает HTTP-ответ к серверу.
     * @return Response
     */
    public function createResponse();
}