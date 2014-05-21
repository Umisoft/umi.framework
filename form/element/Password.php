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
 * Элемент формы - Пароль(password).
 * @example <input type="password" />
 */
class Password extends BaseFormInput
{
    /**
     * Тип элемента.
     */
    const TYPE_NAME = 'password';

    /**
     * {@inheritdoc}
     */
    protected $inputType = self::TYPE_NAME;

    /**
     * {@inheritdoc}
     */
    protected function extendView(FormEntityView $view)
    {
        parent::extendView($view);
        unset($view->attributes['value']);
    }

}