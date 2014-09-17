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
use umi\form\FormEntityView;
use umi\orm\exception\RuntimeException;
use umi\validation\IValidationAware;
use umi\validation\IValidator;
use umi\validation\IValidatorCollection;
use umi\validation\TValidationAware;

/**
 * Абстрактный базовый класс элемента формы.
 */
abstract class BaseFormElement extends BaseFormEntity implements IFormElement, IValidationAware, IFilterAware
{

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
     * @var string $value значение элемента формы
     */
    private $value;

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        if ($this->getDataSource()) {
            return $this->getDataAdapter()->getData($this);
        } else {
            return $this->value;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($value)
    {
        $value = $this->filter($value);

        if ($this->getDataSource()) {
            $this->getDataAdapter()->setData($this, $value);
        } else {
            $this->value = $value;
        }

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
            $config = [];
            foreach ($this->getValidatorsConfig() as $type => $options) {
                if (isset($options['dictionaries'])) {
                    $options['dictionaries'] = array_merge($options['dictionaries'], $this->getI18nDictionaryNames());
                } else {
                    $options['dictionaries'] = $this->getI18nDictionaryNames();
                }
                $config[$type] = $options;
            }
            $this->validators = $this->createValidatorCollection($config);
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

        return $this->validate($this->getValue());
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
     * @param mixed $value
     * @return bool
     */
    protected function validate($value)
    {
        $isValid =
            $this->getValidators()->isValid($value) &&
            $this->getDataAdapter()->validate($this, $value);

        $this->messages = array_merge(
            $this->getValidators()->getMessages(),
            $this->getDataAdapter()->getValidationErrors($this)
        );

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

    /**
     * {@inheritdoc}
     */
    protected function extendView(FormEntityView $view)
    {
        $view->attributes['name'] = $this->getElementName();
        $view->dataSource = $this->getDataSource();
        $view->value = $this->getValue();

        $elementValidators = iterator_to_array($this->getValidators(), true);

        $dataSourceValidatorsConfig = $this->getDataAdapter()->getValidatorsConfig($this);
        $dataSourceValidators = iterator_to_array($this->createValidatorCollection($dataSourceValidatorsConfig), true);

        $validators = array_merge($elementValidators, $dataSourceValidators);
        $view->validators = [];

        /**
         * @var IValidator $validator
         */
        foreach ($validators as $validator) {
            $view->validators[] = [
                'type' => $validator->getType(),
                'message' => $this->translate($validator->getErrorLabel()),
                'options' => $validator->getOptions()
            ];
        }

        $view->filters = [];
        foreach ($this->getFiltersConfig() as $filterType => $filterOptions) {
            $view->filters[] = [
                'type' => $filterType,
                'options' => $filterOptions
            ];
        }
    }
}