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
 * Элемент формы select.
 * @example <select name="name"></select>
 */
class Select extends BaseChoiceElement
{
    /**
     * Тип элемента.
     */
    const TYPE_NAME = 'select';

    /**
     * {@inheritdoc}
     */
    protected $tagName = self::TYPE_NAME;

    /**
     * {@inheritdoc}
     */
    protected function extendView(FormEntityView $view)
    {
        parent::extendView($view);

        $selected = (array) $this->getValue();
        $view->choices = [];

        foreach ($this->getChoices() as $value => $label) {
            $attributes = ['value' => $value];

            if (in_array($value, $selected)) {
                $attributes += [
                    'selected' => 'selected'
                ];
            }

            $view->choices[] = [
                'label' => $this->translate($label),
                'attributes' => $attributes,
                'attributesString' => $this->buildAttributesString($attributes)
            ];
        }
    }
}