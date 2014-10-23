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
 * Представление сущности формы
 *
 * @property string $type тип сущности
 * @property string $tag имя тега
 * @property string $label лейбл
 * @property string $id уникальный идентификатор формы
 * @property bool $valid признак валидности
 * @property array $errors список ошибок валидации
 * @property EntityAttributesView $attributes представление атрибутов сущности
 */
class FormEntityView extends \ArrayObject
{
    /**
     * {@inheritdoc}
     */
    public function __construct(array $array)
    {
        parent::__construct($array, self::STD_PROP_LIST | self::ARRAY_AS_PROPS);
    }
}
 