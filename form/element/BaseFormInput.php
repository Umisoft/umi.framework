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
 * Базовый класс инпутов формы.
 */
abstract class BaseFormInput extends BaseFormElement
{
    /**
     * @var string $inputType тип инпута
     */
    protected $inputType = 'text';

    /**
     * {@inheritdoc}
     */
    protected function extendView(FormEntityView $view)
    {
        parent::extendView($view);

        $view->attributes['type'] = $this->inputType;
        $view->attributes['value'] = $this->getValue();
    }
}
 