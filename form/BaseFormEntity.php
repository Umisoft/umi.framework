<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\form;

use umi\form\fieldset\IFieldSet;

/**
 * Базовый класс сущности формы.
 */
abstract class BaseFormEntity implements IFormEntity
{
    /**
     * @var string $name имя сущности формы
     */
    protected $name;
    /**
     * @var string $label
     */
    protected $label;
    /**
     * @var IFieldSet $parent родительская сущность
     */
    protected $parent;
    /**
     * @var array $attributes атрибуты сущности
     */
    protected $attributes = [];
    /**
     * @var array $options опции сущности
     */
    protected $options = [];

    /**
     * Конструктор.
     * @param string $name имя сущности
     * @param array $attributes аттрибуты
     * @param array $options опции
     */
    public function __construct($name, array $attributes = [], array $options = [])
    {
        $this->name = $name;
        $this->attributes = $attributes;
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * {@inheritdoc}
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * {@inheritdoc}
     */
    public function setAttribute($attributeName, $value)
    {
        $this->attributes[$attributeName] = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttribute($attributeName, $default = null)
    {
        if (isset($this->attributes[$attributeName])) {
            return $this->attributes[$attributeName];
        }

        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * {@inheritdoc}
     */
    public function setParent(IFieldSet $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return $this->parent;
    }

}