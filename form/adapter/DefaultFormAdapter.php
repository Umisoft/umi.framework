<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\form\adapter;

use SplObjectStorage;
use umi\form\element\IFormElement;
use umi\form\element\IChoiceFormElement;

/**
 * Null-адаптер данных формы
 */
class DefaultFormAdapter implements IDataAdapter
{

    /**
     * @var SplObjectStorage $data провайдер данных для формы
     */
    protected $data;

    /**
     * Конструктор.
     */
    public function __construct()
    {
        $this->data = new SplObjectStorage();
    }

    /**
     * {@inheritdoc}
     */
    public function setData(IFormElement $element, $data)
    {
        $this->data->attach($element, $data);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getData(IFormElement $element)
    {
        if ($this->data->offsetExists($element)) {
            return $this->data->offsetGet($element);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function validate(IFormElement $element, $value)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getValidationErrors(IFormElement $element)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getValidatorsConfig(IFormElement $element)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getChoices(IChoiceFormElement $element)
    {
        return [];
    }
}
 