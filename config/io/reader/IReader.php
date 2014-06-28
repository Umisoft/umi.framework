<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\config\io\reader;

use umi\config\entity\IConfigSource;

/**
 * Интерфейс считывателя конфигурации.
 */
interface IReader
{
    /**
     * Команда конструкции конфигурации для подключения части конфигурации.
     */
    const COMMAND_PART = 'partial';
    /**
     * Команда конструкции конфигурации для подключения части конфигурации ленивым образом.
     */
    const COMMAND_LAZY = 'lazy';
    /**
     * Команда конструкции конфигурации для преобразования алиаса в реальный путь до локальной директории проекта.
     */
    const LOCAL_DIR = 'localDir';
    /**
     * Команда конструкции конфигурации для преобразования алиаса в реальный путь до мастер директории.
     */
    const MASTER_DIR = 'masterDir';

    /**
     * Возвращает конфигурацию с заданным имененем.
     * @param string $alias символическое имя конфигурации
     * @return IConfigSource
     */
    public function read($alias);

}