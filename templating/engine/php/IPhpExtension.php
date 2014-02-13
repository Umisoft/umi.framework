<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\templating\engine\php;

/**
 * Расширение для PHP-шаблонизатора/
 */
interface IPhpExtension
{
    /**
     * Возвращает имя расширения.
     * @return string
     */
    public function getName();

    /**
     * Возвращает список функций.
     * @return array вида [$functionName => $callable, ...]
     */
    public function getFunctions();

}
 