<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\form\element;

use umi\form\FormEntityView;

/**
 * Элемент формы - флаг(checkbox).
 * @example <input type="checkbox" />
 */
class Checkbox extends BaseFormInput
{
    /**
     * Тип элемента.
     */
    const TYPE_NAME = 'checkbox';

    /**
     * {@inheritdoc}
     */
    protected $inputType = self::TYPE_NAME;

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return parent::getValue() ? 1 : 0;
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($value)
    {
        return parent::setValue((bool) $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getInputType()
    {
        return self::TYPE_NAME;
    }

    /**
     * {@inheritdoc}
     */
    protected function extendView(FormEntityView $view)
    {
        parent::extendView($view);

        if ($this->getValue()) {
            $view->attributes['checked'] = 'checked';
        }
        $view->attributes['value'] = 1;
    }
}