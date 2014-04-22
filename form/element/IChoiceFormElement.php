<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\form\element;

/**
 * Интерфейс элемента, предаставляющего значения на выбор.
 */
interface IChoiceFormElement extends IFormElement
{
    /**
     * Возвращает список возможных значений элемента.
     * @return array в формате [$value => $label, ...]
     */
    public function getChoices();

    /**
     * Возвращает список возможных значений элемента явно заданных в конфигурации.
     * @return array в формате [$value => $label, ...]
     */
    public function getStaticChoices();

    /**
     * Возвращает идентификатор источника данных для возможных значений.
     * @return string|null
     */
    public function getChoiceValueSource();

    /**
     * Возвращает идентификатор источника данных для заголовков возможных значений.
     * @return string|null
     */
    public function getChoiceLabelSource();
}