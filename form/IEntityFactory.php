<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\form;

/**
 * Интерфейс фабрики элементов формы.
 */
interface IEntityFactory
{

    /**
     * Создает форму на основе конфигурации.
     * @param array $config конфигурация
     * @param array|object $object объект, с которым связана форма
     * @return IForm
     */
    public function createForm(array $config, $object = null);
}