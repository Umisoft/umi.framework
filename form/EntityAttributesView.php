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
 * Представление атрибутов сущности формы
 */
class EntityAttributesView extends \ArrayObject
{
    /**
     * {@inheritdoc}
     */
    public function __construct(array $array)
    {
        parent::__construct($array, self::STD_PROP_LIST | self::ARRAY_AS_PROPS);
    }

    /**
     * Преобразует набор атрибутов в строку
     */
    public function __toString()
    {
        $strings = [];

        foreach ($this as $key => $value) {
            if (is_array($value)) {
                continue;
            }
            $strings[] = $key . '="' . $value . '"';
        }

        return implode(' ', $strings);
    }
}
 