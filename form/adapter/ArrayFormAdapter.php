<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\form\adapter;

use umi\form\element\IFormElement;
use umi\form\element\IChoiceFormElement;

/**
 * Адаптер формы для данных в виде массива
 */
class ArrayFormAdapter implements IDataAdapter
{

    /**
     * @var array $data провайдер данных для формы
     */
    protected $data;

    /**
     * Конструктор.
     * @param array $data провайдер данных для формы
     */
    public function __construct(array &$data)
    {
        $this->data = &$data;
    }

    /**
     * {@inheritdoc}
     */
    public function setData(IFormElement $element, $data)
    {
        if ($dataSource = $element->getDataSource()) {
            $this->data[$dataSource] = $data;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getData(IFormElement $element)
    {
        $dataSource = $element->getDataSource();

        if ($dataSource && isset($this->data[$dataSource])) {
            return $this->data[$dataSource];
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
    public function getChoices(IChoiceFormElement $element)
    {
        return [];
    }
}
 