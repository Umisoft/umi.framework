<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\form\adapter;

use umi\config\entity\IConfig;
use umi\form\element\IChoiceFormElement;
use umi\form\element\IFormElement;

/**
 * Адаптер формы для данных в виде конфигурации
 */
class ConfigFormAdapter implements IDataAdapter
{
    /**
     * @var IConfig $data провайдер данных для формы
     */
    protected $data;

    /**
     * Конструктор.
     * @param IConfig $data провайдер данных для формы
     */
    public function __construct(IConfig $data)
    {
        $this->data = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function setData(IFormElement $element, $data)
    {
        if ($dataSource = $element->getDataSource()) {
            $this->data->set($dataSource, $data);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getData(IFormElement $element)
    {
        $dataSource = $element->getDataSource();

        if ($dataSource && $this->data->has($dataSource)) {
            return $this->data->get($dataSource);
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
 