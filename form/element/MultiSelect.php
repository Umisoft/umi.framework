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
 * Элемент формы select с несколькими значениями.
 * @example <select name="name[]"></select>
 */
class MultiSelect extends Select
{

    /**
     * Тип элемента.
     */
    const TYPE_NAME = 'multiSelect';

    /**
     * {@inheritdoc}
     */
    protected $type = 'multiSelect';

    /**
     * {@inheritdoc}
     */
    public function getElementName()
    {
        $name = parent::getElementName();

        return $name . '[]';
    }

    /**
     * {@inheritdoc}
     */
    protected function extendView(FormEntityView $view)
    {
        parent::extendView($view);

        $view->attributes['multiple'] = 'multiple';
    }
}
 