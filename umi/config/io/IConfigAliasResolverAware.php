<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\config\io;

/**
 * Интерфейс для внедрения поддержки работы с символическими именами конфигурации.
 * @internal
 */
interface IConfigAliasResolverAware
{
    /**
     * Устанавливает I/O сервис.
     * @param IConfigIO $configIO I/O сервис
     */
    public function setConfigIO(IConfigIO $configIO);
}