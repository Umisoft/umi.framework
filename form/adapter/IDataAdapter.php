<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\form\adapter;

use umi\form\element\IFormElement;
use umi\form\element\IChoiceFormElement;

/**
 * Адаптер данных для формы.
 */
interface IDataAdapter
{
    /**
     * Устанавливает значение в провайдер данных.
     * @param IFormElement $element элемент формы, для которого устанавливается значение
     * @param mixed $data значение
     * @return self
     */
    public function setData(IFormElement $element, $data);

    /**
     * Возвращает значение провайдера данных.
     * @param IFormElement $element элемент формы, для которого формируется значение
     * @return mixed
     */
    public function getData(IFormElement $element);

    /**
     * Валидирует значение провайдера данных.
     * @param IFormElement $element элемент формы, для которого формируется значение
     * @return bool
     */
    public function isValid(IFormElement $element);

    /**
     * Возвращает ошибки валидации провайдера данных.
     * @param IFormElement $element элемент формы, для которого формируется значение
     * @return []
     */
    public function getValidationErrors(IFormElement $element);

    /**
     * Возвращает список вариантов значений на выбор.
     * @param IChoiceFormElement $element элемент формы с выбором значений
     * @return array в формате [$value => $label, ...]
     */
    public function getChoices(IChoiceFormElement $element);
}
 