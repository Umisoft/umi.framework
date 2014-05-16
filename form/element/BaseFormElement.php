<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\form\element;

use umi\filter\IFilterAware;
use umi\filter\IFilterCollection;
use umi\filter\TFilterAware;
use umi\form\BaseFormEntity;
use umi\orm\exception\RuntimeException;
use umi\validation\IValidationAware;
use umi\validation\IValidatorCollection;
use umi\validation\TValidationAware;

/**
 * Абстрактный базовый класс элемента формы.
 */
abstract class BaseFormElement extends BaseFormEntity implements IFormElement, IValidationAware, IFilterAware
{

    /**
     * Тип элемента.
     */
    const TYPE_NAME = 'text';

    use TValidationAware;
    use TFilterAware;

    /**
     * @var array $messages сообщения валидации
     */
    protected $messages = [];
    /**
     * @var IFilterCollection $filters фильтры элемента
     */
    private $filters;
    /**
     * @var IValidatorCollection $validators валидаторы элемента
     */
    private $validators;

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return $this->getDataAdapter()->getData($this);
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($value)
    {
        $value = $this->filter($value);
        $this->getDataAdapter()->setData($this, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        if (!$this->filters) {
            $this->filters = $this->createFilterCollection($this->getFiltersConfig());
        }

        return $this->filters;
    }

    /**
     * {@inheritdoc}
     */
    public function getFiltersConfig()
    {
        return isset($this->options['filters']) ? $this->options['filters'] : [];
    }

    /**
     * {@inheritdoc}
     */
    public function getValidators()
    {
        if (!$this->validators) {
            $this->validators = $this->createValidatorCollection($this->getValidatorsConfig());
        }

        return $this->validators;
    }

    /**
     * {@inheritdoc}
     */
    public function getValidatorsConfig()
    {
        return isset($this->options['validators']) ? $this->options['validators'] : [];
    }

    /**
     * {@inheritdoc}
     */
    public function getElementName()
    {
        if (!$this->getParent()) {
            throw new RuntimeException('Cannot get element name. Parent form is unknown.');
        }

        $names = [$this->getName()];

        $element = $this->getParent();
        while(!is_null($parent = $element->getParent())) {
            array_unshift($names, $element->getName());
            $element = $parent;
        }

        $name = array_shift($names);
        foreach ($names as $part) {
            $name .= '[' . $part . ']';
        }

        return $name;

    }

    /**
     * {@inheritdoc}
     */
    public function getDataSource()
    {
        return isset($this->options['dataSource']) ? $this->options['dataSource'] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function isValid()
    {
        if (!$this->getIsSubmitted()) {
            return true;
        }

        return $this->validate();
    }

    /**
     * {@inheritdoc}
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Возвращает отфильтрованное значение.
     * @param string $value
     * @return mixed
     */
    protected function filter($value)
    {
        if (is_array($value)) {
            array_walk($value, function(&$item) {
                    $item = $this->getFilters()->filter($item);
                }
            );
            return $value;
        }
        return $this->getFilters()->filter($value);
    }

    /**
     * Проверяет значение на сооветсвие валидаторам.
     * @return bool
     */
    protected function validate()
    {
        $isValid =
            $this->getValidators()->isValid($this->getValue());
        $this->messages = $this->getValidators()->getMessages();

        return $isValid;
    }

    /**
     * {@inheritdoc}
     */
    public function getDataAdapter()
    {
        if (!$this->getParent()) {
            throw new RuntimeException('Cannot get form data adapter. Parent form is unknown.');
        }

        return $this->getParent()->getDataAdapter();
    }

    /**
     * {@inheritdoc}
     */
    public function getIsSubmitted()
    {
        if (!$this->getParent()) {
            throw new RuntimeException('Cannot detect whether the element was submitted. Parent form is unknown.');
        }

        return $this->getParent()->getIsSubmitted();
    }
}