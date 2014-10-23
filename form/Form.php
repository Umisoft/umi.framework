<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\form;

use umi\form\adapter\IDataAdapter;
use umi\form\exception\RuntimeException;
use umi\form\fieldset\FieldSet;

/**
 * Класс форм.
 */
class Form extends FieldSet implements IForm
{
    /**
     * Тип элемента формы.
     */
    const TYPE_NAME = 'form';

    /**
     * {@inheritdoc}
     */
    protected $tagName = 'form';
    /**
     * {@inheritdoc}
     */
    protected $type = 'form';
    /**
     * @var IDataAdapter $dataAdapter адаптер данных формы
     */
    protected $dataAdapter;
    /**
     * @var string уникальный идентификатор формы
     */
    protected $id;

    /**
     * {@inheritdoc}
     */
    public function getAction()
    {
        return $this->getAttribute('action');
    }

    /**
     * {@inheritdoc}
     */
    public function getMethod()
    {
        return $this->getAttribute('method');
    }

    /**
     * {@inheritdoc}
     */
    public function setAction($action)
    {
        return $this->setAttribute('action', $action);
    }

    /**
     * {@inheritdoc}
     */
    public function setMethod($method)
    {
        return $this->setAttribute('method', $method);
    }

    /**
     * {@inheritdoc}
     */
    public function setDataAdapter(IDataAdapter $dataAdapter)
    {
        $this->dataAdapter = $dataAdapter;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDataAdapter()
    {
        if ($parent = $this->getParent()) {
            return $parent->getDataAdapter();
        }

        if (!$this->dataAdapter) {
            throw new RuntimeException($this->translate('Form data adapter is not set'));
        }

        return $this->dataAdapter;
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getIsSubmitted()
    {
        if ($parent = $this->getParent()) {
            return $parent->getIsSubmitted();
        }

        return $this->isSubmitted;
    }

}