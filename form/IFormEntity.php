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
use umi\form\exception\RuntimeException;
use umi\form\fieldset\IFieldSet;
use umi\i18n\ILocalizable;

/**
 * Интерфейс сущности формы.
 */
interface IFormEntity extends ILocalizable
{
    /**
     * Возвращает имя сущности.
     * @return string
     */
    public function getName();

    /**
     * Возвращает тип элемента.
     * @return string
     */
    public function getType();

    /**
     * Возвращает label для сущности.
     * @return string
     */
    public function getLabel();

    /**
     * Устанавливает label для сущности.
     * @param string $label
     * @return self
     */
    public function setLabel($label);

    /**
     * Устанавливает значение html-атрибута.
     * @param string $attributeName имя атрибута
     * @param mixed $value
     * @return self
     */
    public function setAttribute($attributeName, $value);

    /**
     * Возвращает значение html-атрибута.
     * @param string $attributeName имя атрибута
     * @param mixed $default значение по умолчанию
     * @return mixed
     */
    public function getAttribute($attributeName, $default = null);

    /**
     * Возвращает все аттрибуты в виде ассоциативного массива.
     * @return array
     */
    public function getAttributes();

    /**
     * Возвращает все опции.
     * @return array
     */
    public function getOptions();

    /**
     * Возвращает сообщения валидации.
     * @return array
     */
    public function getMessages();

    /**
     * Возвращает значение валидности сущности.
     * @return bool
     */
    public function isValid();

    /**
     * Устанавливает родительскую сущность.
     * @param IFieldSet $parent
     * @return self
     */
    public function setParent(IFieldSet $parent = null);

    /**
     * Возвращает родительскую сущность.
     * @return IFieldSet|null
     */
    public function getParent();

    /**
     * Возвращает адаптер данных формы, которой принадлежит сущность.
     * @throws RuntimeException если невозможно получить адаптер
     * @return IDataAdapter
     */
    public function getDataAdapter();

    /**
     * Проверяет были ли выставлены данные в форму, которой принадлежит сущность.
     * @throws RuntimeException если невозможно выполнить проверку
     * @return bool
     */
    public function getIsSubmitted();

    /**
     * Возвращает модель для отображения.
     * @return \ArrayObject
     */
    public function getView();
}