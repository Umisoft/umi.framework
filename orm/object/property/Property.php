<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\orm\object\property;

use umi\filter\IFilterAware;
use umi\filter\TFilterAware;
use umi\i18n\ILocalesAware;
use umi\i18n\ILocalesService;
use umi\i18n\ILocalizable;
use umi\i18n\TLocalesAware;
use umi\i18n\TLocalizable;
use umi\orm\exception\InvalidArgumentException;
use umi\orm\metadata\field\IField;
use umi\orm\object\IObject;
use umi\validation\IValidationAware;
use umi\validation\TValidationAware;

/**
 * Базовый класс свойства
 */
class Property implements IProperty, ILocalizable, ILocalesAware, IFilterAware, IValidationAware
{

    use TLocalizable;
    use TFilterAware;
    use TLocalesAware;
    use TValidationAware;

    /**
     * @var IObject $object владелец свойства
     */
    protected $object;
    /**
     * @var IField $field поле типа данных
     */
    protected $field;
    /**
     * @var bool $isLoaded загружено ли значение свойства
     */
    protected $isLoaded = false;
    /**
     * @var bool $isModified статус модифицированности значения свойства
     */
    protected $isModified = false;
    /**
     * @var mixed $dbValue значение свойства в БД
     */
    protected $dbValue;
    /**
     * @var mixed $persistedDbValue текущее сохраненное значение свойства в БД
     */
    protected $persistedDbValue;
    /**
     * @var mixed $value значение свойства
     */
    protected $value;
    /**
     * @var mixed $persistedValue текущее сохраненное значение свойства
     */
    protected $persistedValue;
    /**
     * @var bool $isValuePrepared флаг, указывающий на то что значение свойство было подготовлено
     */
    protected $isValuePrepared = false;
    /**
     * @var string $localeId идентификатор локали
     */
    protected $localeId;
    /**
     * @var array $validationErrors массив ошибок валидации
     */
    protected $validationErrors = [];

    /**
     * Конструктор
     * @param IObject $object владелец свойства
     * @param IField $field поле типа данных
     * @param string $localeId идентификатор локали
     */
    public function __construct(IObject $object, IField $field, $localeId = null)
    {
        $this->object = $object;
        $this->field = $field;
        $this->localeId = $localeId;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->field->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function getFullName()
    {
        if ($this->field->getIsLocalized()) {
            return $this->field->getName() . self::LOCALE_SEPARATOR . $this->localeId;
        }

        return $this->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * {@inheritdoc}
     */
    public function getIsLoaded()
    {
        return $this->isLoaded;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocaleId()
    {
        return $this->localeId;
    }

    /**
     * {@inheritdoc}
     */
    public function setInitialValue($dbValue)
    {
        $this->dbValue = $this->persistedDbValue = $dbValue;
        $this->value = $this->persistedValue = null;
        $this->isLoaded = true;
        $this->isModified = false;
        $this->isValuePrepared = false;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDbValue()
    {
        if (!$this->getIsLoaded()) {

            $localization = $this->object->getLoadLocalization();

            if ($this->field->getIsLocalized()) {
                $currentLocaleId = ($localization === ILocalesService::LOCALE_CURRENT) ?
                    $this->getCurrentDataLocale() : $localization;

                if ($this->localeId !== $currentLocaleId && $this->localeId !== $this->getDefaultDataLocale()) {
                    $localization = ILocalesService::LOCALE_ALL;
                }
            }

            $this->object->fullyLoad($localization);
        }

        return $this->dbValue;
    }

    /**
     * {@inheritdoc}
     */
    public function getPersistedDbValue()
    {
        return $this->persistedDbValue;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultValue()
    {
        if (!$this->getIsReadOnly()) {
            $propertyValue = $this->field->preparePropertyValue($this->object, $this->field->getDefaultValue());
            $this->setValue($propertyValue);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($value)
    {
        $value = $this->applyFilters($value);

        if (!is_null($value)) {
            $isValid = false;
            $validationException = null;

            try {
                $isValid = $this->field->validateInputPropertyValue($value);
            } catch (InvalidArgumentException $validationException) {
            }

            if (!$isValid) {
                throw new InvalidArgumentException($this->translate(
                    'Cannot set value for property "{name}". Wrong value type.',
                    ['name' => $this->getName()]
                ), 0, $validationException);
            }
        }

        $this->update($value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        if (!$this->isValuePrepared) {
            $this->value = $this->persistedValue = $this->field->preparePropertyValue(
                $this->object,
                $this->getDbValue()
            );
            $this->isValuePrepared = true;
        }

        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function getPersistedValue()
    {
        if (!$this->isValuePrepared) {
            $this->getValue();
        }

        return $this->persistedValue;
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessor()
    {
        return $this->field->getAccessor();
    }

    /**
     * {@inheritdoc}
     */
    public function getMutator()
    {
        return $this->field->getMutator();
    }

    /**
     * {@inheritdoc}
     */
    public function getIsReadOnly()
    {
        return $this->field->getIsReadOnly();
    }

    /**
     * {@inheritdoc}
     */
    public function getIsModified()
    {
        return $this->isModified;
    }

    /**
     * {@inheritdoc}
     */
    public function setIsConsistent()
    {
        $this->isModified = false;
        $this->persistedDbValue = $this->dbValue;
        $this->persistedValue = $this->value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function rollback()
    {
        if ($this->getIsModified()) {
            $this->dbValue = $this->persistedDbValue;
            $this->value = $this->persistedValue;
            $this->isModified = false;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getIsValuePrepared()
    {
        return $this->isValuePrepared;
    }

    /**
     * {@inheritdoc}
     */
    public function update($value)
    {
        if ($this->getValue() !== $value) {
            $this->dbValue = $this->field->prepareDbValue($this->object, $value);
            $this->value = $value;
            $this->isModified = true;
            $this->object->setIsModified();
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function validate()
    {
        $result = true;
        $this->validationErrors = [];

        if ($validators = $this->getField()->getValidatorsConfig($this->getLocaleId())) {

            $validatorCollection = $this->createValidatorCollection($validators);
            if (!$validatorCollection->isValid($this->getValue())) {
                $this->addValidationErrors($validatorCollection->getMessages());
                $result = false;
            }
        }
        $validatorMethod = IObject::VALIDATOR_METHOD_PREFIX . $this->getName();
        if (method_exists($this->object, $validatorMethod)) {
            if ($this->object->{$validatorMethod}() === false) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * Возвращает список ошибок валидации
     * @return array
     */
    public function getValidationErrors()
    {
        return $this->validationErrors;
    }

    /**
     * {@inheritdoc}
     */
    public function addValidationErrors(array $errors)
    {
        $this->validationErrors = array_merge($this->validationErrors, $errors);

        return $this;
    }

    /**
     * Применяет фильтры поля к значению свойства
     * @param mixed $propertyValue
     * @return mixed
     */
    protected function applyFilters($propertyValue)
    {
        $filterConfig = $this->field->getFiltersConfig($this->getLocaleId());
        if (count($filterConfig)) {
            $filterCollection = $this->createFilterCollection($filterConfig);
            $propertyValue = $filterCollection->filter($propertyValue);
        }

        return $propertyValue;
    }

}
