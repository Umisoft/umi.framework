<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\form\element;

use umi\filter\IFilterCollection;
use umi\form\exception\RuntimeException;
use umi\form\IFormEntity;
use umi\validation\IValidatorCollection;

/**
 * Интерфейс элемента формы.
 */
interface IFormElement extends IFormEntity
{
    /**
     * Возвращает значение элемента.
     * @return string
     */
    public function getValue();

    /**
     * Устанавливает значение элемента.
     * @param mixed $value значение
     * @return self
     */
    public function setValue($value);

    /**
     * Возвращает установленные фильтры.
     * @return IFilterCollection
     */
    public function getFilters();

    /**
     * Возвращает установленные валидаторы.
     * @return IValidatorCollection
     */
    public function getValidators();

    /**
     * Возвращает конфигурацию валидаторов.
     * @return array в формате [$validatorType => [$optionName => $value, ...], ...]
     */
    public function getValidatorsConfig();

    /**
     * Возвращает конфигурацию фильтров.
     * @return array в формате [$filterType => [$optionName => $value, ...], ...]
     */
    public function getFiltersConfig();

    /**
     * Возвращает идентификатор источника данных для элемента.
     * @return string|null
     */
    public function getDataSource();

    /**
     * Формирует и возвращает значение атрибута name элемента в форме.
     * @throws RuntimeException если невозможно получить имя
     * @return string
     */
    public function getElementName();

}