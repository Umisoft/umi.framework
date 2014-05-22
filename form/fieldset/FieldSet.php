<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\form\fieldset;

use Iterator;
use umi\form\element\IFormElement;
use umi\form\exception\OutOfBoundsException;
use umi\form\exception\RuntimeException;
use umi\form\BaseFormEntity;
use umi\form\FormEntityView;
use umi\form\IFormEntity;

/**
 * Класс группы сущностей.
 */
class FieldSet extends BaseFormEntity implements Iterator, IFieldSet
{
    /**
     * Тип элемента формы.
     */
    const TYPE_NAME = 'fieldset';

    /**
     * {@inheritdoc}
     */
    protected $tagName = 'fieldset';
    /**
     * {@inheritdoc}
     */
    protected $type = 'fieldset';
    /**
     * @var IFormEntity[] $children дочерние сущности
     */
    protected $children = [];
    /**
     * @var bool $isSubmitted признак того, что данные группы были установлены
     */
    protected $isSubmitted = false;

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        if (!$this->has($name)) {
            throw new OutOfBoundsException($this->translate(
                'Entity "{name}" not found in "{entity}".',
                ['name' => $name, 'entity' => $this->getName()]
            ));
        }

        return $this->children[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function has($name)
    {
        return isset($this->children[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function add(IFormEntity $entity)
    {
        $name = $entity->getName();

        if (!$name) {
            throw new RuntimeException($this->translate(
                'Cannot add form entity to "{entity}". Entity name cannot be empty.',
                ['entity' => $this->getName()]
            ));
        }

        if (isset($this->children[$name])) {
            throw new RuntimeException($this->translate(
                'Cannot add form entity to "{entity}". Entity already has a child with name "{name}".',
                ['name' => $name, 'entity' => $this->getName()]
            ));
        }

        $entity->setParent($this);
        $this->children[$name] = $entity;

        return $this;

    }

    /**
     * {@inheritdoc}
     */
    public function setData(array $data)
    {
        $this->isSubmitted = true;

        foreach ($this->children as $child) {
            if ($child instanceof IFormElement) {
                $key = $child->getName();
                if (isset($data[$key])) {
                    $child->setValue($data[$key]);
                }
            } elseif ($child instanceof IFieldset) {

                if (isset($data[$child->getName()])) {
                    $child->setData($data[$child->getName()]);
                }
            } else {
                throw new RuntimeException($this->translate(
                        'Unknown element "{name}" type.',
                        ['name' => $child->getName()]
                    )
                );
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $cleanData = [];

        foreach ($this->children as $key => $child) {

            if ($child instanceof IFormElement) {
                $cleanData[$key] = $child->isValid() ? $child->getValue() : null;

            } elseif ($child instanceof IFieldset) {
                $cleanData[$key] = $child->getData();

            } else {
                throw new RuntimeException($this->translate(
                        'Unknown element "{name}" type.',
                        ['name' => $child->getName()]
                    )
                );
            }
        }

        return $cleanData;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessages()
    {
        $messages = [];

        foreach ($this->children as $child) {
            if ($messages = $child->getMessages()) {
                $messages[$child->getName()] = $messages;
            }
        }

        return $messages;
    }

    /**
     * {@inheritdoc}
     */
    public function isValid()
    {
        $isValid = true;

        if (!$this->getIsSubmitted()) {
            return $isValid;
        }

        foreach ($this->children as $child) {
            $isValid = $isValid && $child->isValid();
        }

        return $isValid;
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return current($this->children);
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        return next($this->children);
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return key($this->children);
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return key($this->children) !== null;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        return reset($this->children);
    }

    /**
     * {@inheritdoc}
     */
    public function getDataAdapter()
    {
        if (!$this->getParent()) {
            throw new RuntimeException($this->translate(
                'Cannot get form data adapter. Parent form is unknown'
            ));
        }

        return $this->getParent()->getDataAdapter();
    }

    /**
     * {@inheritdoc}
     */
    public function getIsSubmitted()
    {
        if (!$this->getParent()) {
            throw new RuntimeException($this->translate(
                'Cannot detect whether the element was submitted. Parent form is unknown.'
            ));
        }

        return $this->getParent()->getIsSubmitted();
    }

    /**
     * {@inheritdoc}
     */
    protected function extendView(FormEntityView $view)
    {
        $view->elements = [];
        foreach ($this->children as $child) {
            $view->elements[] = $child->getView();
        }
    }
}