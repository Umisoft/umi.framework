<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\form\element;

use umi\form\exception\InvalidArgumentException;

/**
 * Базовый класс элемента, предаставляющего значения на выбор.
 */
abstract class BaseChoiceElement extends BaseFormElement implements IChoiceFormElement
{

    /**
     * {@inheritdoc}
     */
    public function getChoices()
    {
        if (isset($this->options['choices'])) {
            return $this->options['choices'];
        }

        return $this->getDataAdapter()->getChoices($this);
    }

    /**
     * {@inheritdoc}
     */
    public function getChoiceValueSource()
    {
        if (isset($this->options['choicesSource']['value'])) {
            return $this->options['choicesSource']['value'];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getChoiceLabelSource()
    {
        if (isset($this->options['choicesSource']['label'])) {
            return $this->options['choicesSource']['label'];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     * @throws InvalidArgumentException если значение не в списке.
     */
    public function setValue($value)
    {
        $values = (array) $value;

        foreach ($values as $item) {
            $item = $this->filter($item);
            if (!isset($this->getChoices()[$item])) {
                throw new InvalidArgumentException(
                    sprintf('Value "%s" is not in available values list.', $value)
                );
            }
        }

        return parent::setValue($value);
    }
}