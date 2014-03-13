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
 * Элемент формы - кнопка(button).
 * @example <button>example button</button>
 */
class Button extends BaseFormElement implements IFormButton
{
    /**
     * Тип элемента.
     */
    const TYPE_NAME = 'button';

    /**
     * {@inheritdoc}
     */
    public function setValue($value)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return $this->getLabel();
    }

    /**
     * {@inheritdoc}
     */
    public function getButtonType()
    {
        return static::TYPE_NAME;
    }
}