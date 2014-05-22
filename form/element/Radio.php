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
 * Элемент формы - Переключатель(radio).
 * @example <input type="radio" />
 */
class Radio extends BaseChoiceElement
{
    /**
     * Тип элемента.
     */
    const TYPE_NAME = 'radio';

    /**
     * {@inheritdoc}
     */
    protected $type = 'radio';

    /**
     * {@inheritdoc}
     */
    protected function extendView(FormEntityView $view)
    {
        parent::extendView($view);

        if (!$this->isLazy()) {

            $selected = (array) $this->getValue();
            $view->choices = [];

            foreach ($this->getChoices() as $value => $label) {
                $attributes = [
                    'name' => $this->getElementName(),
                    'type' => 'radio',
                    'value' => $value
                ];

                if (in_array($value, $selected)) {
                    $attributes += [
                        'checked' => 'checked'
                    ];
                }

                $view->choices[] = [
                    'label' => $this->translate($label),
                    'value' => $value,
                    'attributes' => new EntityAttributesView($attributes)
                ];
            }
        }
    }

}