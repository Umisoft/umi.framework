<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\orm\metadata\field;

use umi\dbal\builder\IInsertBuilder;
use umi\dbal\builder\IQueryBuilder;
use umi\dbal\builder\IUpdateBuilder;
use umi\i18n\ILocalizable;
use umi\i18n\TLocalizable;
use umi\orm\exception\NonexistentEntityException;
use umi\orm\exception\UnexpectedValueException;
use umi\orm\object\IObject;
use umi\orm\object\property\IProperty;

/**
 * Поле типа данных.
 */
abstract class BaseField implements IField, ILocalizable
{
    use TLocalizable;

    /**
     * @var string $name имя поля
     */
    protected $name;
    /**
     * @var string $type тип поля
     */
    protected $type;
    /**
     * @var bool $isReadOnly флаг "доступно только на чтение"
     */
    protected $isReadOnly = false;
    /**
     * @var mixed $defaultValue значение поля по умолчанию, которое сохраняется в БД
     */
    protected $defaultValue = null;
    /**
     * @var string $columnName имя столбца в таблице, связанного с полем
     */
    protected $columnName;
    /**
     * @var string $accessor имя getter'а для доступа к значению поля объекта
     */
    protected $accessor;
    /**
     * @var string $mutator имя setter'а для установки значения поля объекта
     */
    protected $mutator;
    /**
     * @var array $validatorsConfig список валидаторов в формате [$validatorType => [$optionName => $value, ...], ...]
     */
    protected $validatorsConfig = [];
    /**
     * @var array $filtersConfig список фильтров в формате [$filterType => [$optionName => $value, ...], ...]
     */
    protected $filtersConfig = [];
    /**
     * @var array $localizations список локализаций в виде
     * [$localeId => ['columnName' => $columnName, 'defaultValue' => $defaultValue], ...]
     */
    protected $localizations = [];

    /**
     * Конструктор.
     * @param string $name имя поля
     * @param string $type тип поля
     * @param array $config конфигурация
     * @throws UnexpectedValueException в случае некорректного конфига
     */
    public function __construct($name, $type, array $config = [])
    {
        $this->name = $name;
        $this->type = $type;
        $this->applyCommonConfiguration($config);
        $this->applyConfiguration($config);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function getIsReadOnly()
    {
        return $this->isReadOnly;
    }

    /**
     * {@inheritdoc}
     */
    public function getColumnName($localeId = null)
    {
        if (!$localeId) {
            return $this->columnName;
        }

        if (!isset($this->localizations[$localeId]) || !isset($this->localizations[$localeId]['columnName'])) {
            throw new NonexistentEntityException($this->translate(
                'Information about column name for field "{field}" in locale "{locale}" does not exist.',
                ['field' => $this->getName(), 'locale' => $localeId]
            ));
        }

        return $this->localizations[$localeId]['columnName'];
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessor()
    {
        return $this->accessor;
    }

    /**
     *{@inheritdoc}
     */
    public function getMutator()
    {
        return $this->mutator;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultValue($localeId = null)
    {
        if (!$localeId) {
            return $this->defaultValue;
        }

        if (!isset($this->localizations[$localeId])) {
            throw new NonexistentEntityException($this->translate(
                'Cannot get default value for field "{field}" in locale "{locale}".',
                ['field' => $this->getName(), 'locale' => $localeId]
            ));
        }

        $defaultValue =
            isset($this->localizations[$localeId]['defaultValue'])
                ? $this->localizations[$localeId]['defaultValue']
                : null;

        return $defaultValue;

    }

    /**
     * {@inheritdoc}
     */
    public function getValidatorsConfig($localeId = null)
    {
        if (!$localeId) {
            return $this->validatorsConfig;
        }

        if (!isset($this->localizations[$localeId])) {
            throw new NonexistentEntityException($this->translate(
                'Cannot get validators for field "{field}" in locale "{locale}".',
                ['field' => $this->getName(), 'locale' => $localeId]
            ));
        }

        $validatorsConfig =
            isset($this->localizations[$localeId]['validators'])
                ? $this->localizations[$localeId]['validators']
                : [];

        if (!is_array($validatorsConfig)) {
            throw new UnexpectedValueException($this->translate(
                'Validators configuration for field "{field}" in locale "{locale}" should be an array.',
                ['field' => $this->getName(), 'locale' => $localeId]
            ));
        }

        return $validatorsConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getFiltersConfig($localeId = null)
    {
        if (!$localeId) {
            return $this->filtersConfig;
        }

        if (!isset($this->localizations[$localeId])) {
            throw new NonexistentEntityException($this->translate(
                'Cannot get filters for field "{field}" in locale "{locale}".',
                ['field' => $this->getName(), 'locale' => $localeId]
            ));
        }

        $filtersConfig =
            isset($this->localizations[$localeId]['filters'])
                ? $this->localizations[$localeId]['filters']
                : [];

        if (!is_array($filtersConfig)) {
            throw new UnexpectedValueException($this->translate(
                'Filters configuration for field "{field}" in locale "{locale}" should be an array.',
                ['field' => $this->getName(), 'locale' => $localeId]
            ));
        }

        return $filtersConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getIsLocalized()
    {
        return (bool) count($this->getLocalizations());
    }

    /**
     * {@inheritdoc}
     */
    public function getLocalizations()
    {
        return $this->localizations;
    }

    /**
     * {@inheritdoc}
     */
    public function hasLocale($localeId)
    {
        return isset($this->localizations[$localeId]);
    }

    /**
     * {@inheritdoc}
     */
    public function getLocaleDefaultValue($localeId = null)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function persistProperty(IObject $object, IProperty $property, IQueryBuilder $builder)
    {
        /**
         * @var IUpdateBuilder $builder
         */
        if ($builder instanceof IInsertBuilder || $builder instanceof IUpdateBuilder) {

            $localeId = $property->getLocaleId();

            if ($localeId && !$this->hasLocale($localeId)) {
                return $this;
            }

            $builder->set($this->getColumnName($localeId));
            $builder->bindValue(':' . $this->getColumnName($localeId), $property->getDbValue(), $this->getDataType());
        }

        return $this;
    }

    /**
     * Разбирает и применяет общую конфигурацию поля
     * @param array $config конфигурация поля
     * @throws UnexpectedValueException при ошибках в конфигурации
     */
    protected function applyCommonConfiguration($config)
    {
        $this->columnName = isset($config['columnName']) ? strval($config['columnName']) : $this->name;

        if (isset($config['readOnly'])) {
            $this->isReadOnly = (bool) $config['readOnly'];
        }
        if (isset($config['defaultValue'])) {
            $this->defaultValue = $config['defaultValue'];
        }
        if (isset($config['accessor'])) {
            $this->accessor = strval($config['accessor']);
        }
        if (isset($config['mutator'])) {
            $this->mutator = strval($config['mutator']);
        }

        if (isset($config['validators'])) {
            $validators = $config['validators'];
            if (!is_array($validators)) {
                throw new UnexpectedValueException($this->translate(
                    'Validators configuration for field "{field}" should be an array.',
                    ['field' => $this->getName()]
                ));
            }
            $this->validatorsConfig = $validators;
        }

        if (isset($config['filters'])) {
            $filters = $config['filters'];
            if (!is_array($filters)) {
                throw new UnexpectedValueException($this->translate(
                    'Filters configuration for field "{field}" should be an array.',
                    ['field' => $this->getName()]
                ));
            }
            $this->filtersConfig = $filters;
        }

        if (isset($config['localizations'])) {
            $localizations = $config['localizations'];
            if (!is_array($localizations)) {
                throw new UnexpectedValueException($this->translate(
                    'Localization configuration for localizable field "{field}" should be an array.',
                    ['field' => $this->getName()]
                ));
            }
            $this->localizations = $localizations;
        }
    }

    /**
     * Разбирает и применяет конфигурацию для поля
     * @param array $config конфигурация поля
     */
    protected function applyConfiguration(array $config)
    {
    }
}
