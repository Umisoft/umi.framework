<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\form;

use umi\form\adapter\IDataAdapter;
use umi\form\fieldset\IFieldSet;

/**
 * Интерфейс формы.
 */
interface IForm extends IFieldSet
{
    /**
     * Возвращает action формы.
     * @return string
     */
    public function getAction();

    /**
     * Устанавливает action формы.
     * @param string $action
     * @return self
     */
    public function setAction($action);

    /**
     * Возвращает метод отправки формы.
     * @return string
     */
    public function getMethod();

    /**
     * Устанавливает метод формы.
     * @param string $method
     * @return self
     */
    public function setMethod($method);

    /**
     * Устанавливает адаптер данных для формы.
     * @param IDataAdapter $dataAdapter
     * @return self
     */
    public function setDataAdapter(IDataAdapter $dataAdapter);

}