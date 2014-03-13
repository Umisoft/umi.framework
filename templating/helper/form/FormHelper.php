<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\templating\helper\form;

use umi\form\element\Checkbox;
use umi\form\element\CheckboxGroup;
use umi\form\element\IFormButton;
use umi\form\element\IFormInput;
use umi\form\element\MultiSelect;
use umi\form\element\Select;
use umi\form\element\Textarea;
use umi\form\fieldset\IFieldSet;
use umi\form\IForm;
use umi\form\IFormEntity;
use umi\templating\exception\InvalidArgumentException;

/**
 * Помощник шаблонов для вывода форм.
 */
class FormHelper
{

    /**
     * Генерирует открывающий тег формы для объекта $form.
     * @param IForm $form объект формы
     * @return string сгенерированный тэг
     */
    public function openTag(IForm $form)
    {
        $attributes = $this->buildAttributes($form->getAttributes());

        return '<form ' . $attributes . '>';
    }

    /**
     * Генерирует закрывающий тег формы.
     * @return string сгенерированный тэг
     */
    public function closeTag()
    {
        return '</form>';
    }

    /**
     * Генерирует открывающий тег fieldset.
     * @param IFieldSet $fieldSet набор сущностей
     * @return string сгенерированный тэг
     */
    public function openFieldSet(IFieldSet $fieldSet)
    {
        $attributes = $this->buildAttributes($fieldSet->getAttributes());

        $html = '<fieldset ' . $attributes . '>';

        if ($label = $fieldSet->getLabel()) {
            $html .= '<legend>' . $label . '</legend>';
        }

        return $html;
    }

    /**
     * Генерирует закрывающий тег fieldset.
     * @return string сгенерированный тэг
     */
    public function closeFieldSet()
    {
        return '</fieldset>';
    }

    /**
     * Генерирует элемент формы. Выбирает нужный помощник шаблонов
     * в зависимости от типа сущность.
     * @param IFormEntity $element сущность формы
     * @throws InvalidArgumentException если сущность не может быть выведена.
     * @return string сгенерированный тэг
     */
    public function formElement(IFormEntity $element)
    {

        switch(true)
        {
            case $element instanceof Textarea: {
                return $this->formTextarea($element);
            }
            case $element instanceof Select: {
                return $this->formSelect($element);
            }
            case $element instanceof CheckboxGroup: {
                return $this->formCheckboxGroup($element);
            }
            case $element instanceof Checkbox: {
                return $this->formCheckbox($element);
            }
            case $element instanceof IFormButton: {
                return $this->formButton($element);
            }
            case $element instanceof IFormInput: {
                return $this->formInput($element);
            }
            default:
                throw new InvalidArgumentException(
                    sprintf('Cannot build element "%s". Element type is unknown.', $element->getName())
                );
        }
    }

    /**
     * Генерирует <select> элемент формы.
     * @param Select $select элемент формы
     * @return string сгенерированный тэг
     */
    public function formSelect(Select $select)
    {
        $attributes = array_merge(
            ['name' => $select->getElementName()],
            $select->getAttributes()
        );

        if ($select instanceof MultiSelect) {
            $attributes['multiple'] = 'multiple';
        }

        $html = '<select ' . $this->buildAttributes($attributes) . ' >';
        $selected = (array) $select->getValue();

        foreach ($select->getChoices() as $value => $label) {
            $attr = ['value' => $value];

            if (in_array($value, $selected)) {
                $attr += [
                    'selected' => 'selected'
                ];
            }

            $html .= '<option ' . $this->buildAttributes($attr) . '>' . $label . '</option>';
        }

        $html .= '</select>';

        return $html;
    }

    /**
     * Формирует группу checkbox.
     * @param CheckboxGroup $checkboxGroup элемент формы
     * @return string сгенерированные тэги
     */
    public function formCheckboxGroup(CheckboxGroup $checkboxGroup)
    {
        $attributes = array_merge(
            $checkboxGroup->getAttributes(),
            [
                'name' => $checkboxGroup->getElementName(),
                'type' => $checkboxGroup->getInputType()
            ]
        );

        $html = '';
        $selected = (array) $checkboxGroup->getValue();

        foreach ($checkboxGroup->getChoices() as $value => $label) {
            $attr = ['value' => $value];

            if (in_array($value, $selected)) {
                $attr += [
                    'checked' => 'checked'
                ];
            }

            $html .= '<label><input ' . $this->buildAttributes($attributes + $attr) .' />'. $label .'</label>';

        }

        return $html;
    }

    /**
     * Генерирует <textarea> элемент формы.
     * @param Textarea $textarea элемент формы
     * @return string сгенерированный тэг
     */
    public function formTextarea(Textarea $textarea)
    {
        $attributes = array_merge(
            $textarea->getAttributes(),
            ['name' => $textarea->getElementName()]
        );

        return '<textarea ' . $this->buildAttributes($attributes) . ' >' . $textarea->getValue() . '</textarea>';
    }

    /**
     * Генерирует <input> элемент формы.
     * @param IFormInput $input элемент формы
     * @return string сгенерированный тэг
     */
    public function formInput(IFormInput $input)
    {
        $attributes = array_merge(
            $input->getAttributes(),
            [
                'type' => $input->getInputType(),
                'name' => $input->getElementName(),
                'value' => $input->getValue()
            ]
        );

        return '<input ' . $this->buildAttributes($attributes) .' />';
    }

    /**
     * Генерирует <input type="checkbox"> элемент формы.
     * @param Checkbox $input элемент формы
     * @return string сгенерированный тэг
     */
    public function formCheckbox(Checkbox $input)
    {
        $attributes = array_merge(
            $input->getAttributes(),
            [
                'type' => $input->getInputType(),
                'name' => $input->getElementName(),
                'value' => 1
            ]
        );

        if ($input->getValue()) {
            $attributes['checked'] = 'checked';
        }
        $html = '<input type="hidden" value="0" name="' . $input->getElementName() . '"/>';
        $html .= '<input ' . $this->buildAttributes($attributes) .' />';

        return $html;
    }

    /**
     * Генерирует <button> элемент формы.
     * @param IFormButton $button элемент формы
     * @return string сгенерированный тэг
     */
    public function formButton(IFormButton $button)
    {
        $attributes = array_merge(
            $button->getAttributes(),
            [
                'type' => $button->getButtonType(),
                'name' => $button->getElementName(),
                'value' => $button->getValue()
            ]
        );

        return '<button ' . $this->buildAttributes($attributes) . ' />';

    }

    /**
     * Генерирует строку аттрибутов для элемента.
     * @param array $attributes массив аттрибутов элемента
     * @return string
     */
    protected function buildAttributes(array $attributes)
    {
        $strings = [];

        foreach ($attributes as $key => $value) {
            if (is_array($value)) {
                continue;
            }
            $strings[] = $key . '="' . $value . '"';
        }

        return implode(' ', $strings);
    }

}
