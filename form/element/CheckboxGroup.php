<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\form\element;

use umi\form\EntityAttributesView;
use umi\form\FormEntityView;

/**
 * Группа элементов формы - флаги(checkbox).
 * @example <input name="name[]" type="checkbox" />
 */
class CheckboxGroup extends BaseChoiceElement
{
    /**
     * Тип элемента.
     */
    const TYPE_NAME = 'checkboxGroup';

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

        $selected = (array) $this->getValue();
        $view->choices = [];

        foreach ($this->getChoices() as $value => $label) {
            $attributes = [
                'name' => $this->getElementName(),
                'type' => 'checkbox',
                'value' => $value
            ];

            if (in_array($value, $selected)) {
                $attributes += [
                    'checked' => 'checked'
                ];
            }

            $view->choices[] = [
                'label' => $this->translate($label),
                'attributes' => new EntityAttributesView($attributes)
            ];
        }
    }
}