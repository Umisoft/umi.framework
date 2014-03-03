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
use umi\orm\exception\NonexistentEntityException;
use umi\orm\exception\UnexpectedValueException;
use umi\orm\object\IObject;
use umi\orm\object\property\localized\ILocalizedProperty;
use umi\orm\object\property\IProperty;

/**
 * Трейт для локализуемого поля.
 */
trait TLocalizableField
{

    /**
     * @var array $localizations список локализаций в виде
     * [$localeId => ['columnName' => $columnName, 'defaultValue' => $defaultValue], ...]
     */
    protected $localizations = [];

    /**
     * @see IField::getName()
     */
    abstract public function getName();

    /**
     * @see IField::getDataType()
     */
    abstract public function getDataType();

    /**
     * @see IField::getColumnName()
     */
    abstract public function getColumnName();

    /**
     * @see IField::getDefaultValue()
     */
    abstract public function getDefaultValue();

    /**
     * @see TLocalizable::translate()
     */
    abstract protected function translate($message, array $placeholders = [], $localeId = null);

    /**
     * @see TLocalesAware::getCurrentLocale()
     */
    abstract protected function getCurrentLocale();

    /**
     * @see ILocalizableField::getIsLocalized()
     */
    public function getIsLocalized()
    {
        return (bool) count($this->getLocalizations());
    }

    /**
     * @see ILocalizableField::getLocalizations()
     */
    public function getLocalizations()
    {
        return $this->localizations;
    }

    /**
     * @see ILocalizableField::hasLocale()
     */
    public function hasLocale($localeId)
    {
        return isset($this->localizations[$localeId]);
    }

    /**
     * @see ILocalizableField::getLocaleColumnName()
     */
    public function getLocaleColumnName($localeId = null)
    {
        if (!$localeId) {
            return $this->getColumnName();
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
     * @see ILocalizableField::getLocaleDefaultValue()
     */
    public function getLocaleDefaultValue($localeId = null)
    {
        if (!$localeId) {
            return $this->getDefaultValue();
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
     * @see IField::persistProperty()
     */
    public function persistProperty(IObject $object, IProperty $property, IQueryBuilder $builder)
    {
        /**
         * @var IUpdateBuilder $builder
         */
        if ($builder instanceof IInsertBuilder || $builder instanceof IUpdateBuilder) {

            $localeId = $property instanceof ILocalizedProperty ? $property->getLocaleId() : null;

            if ($localeId && !$this->hasLocale($localeId)) {
                return $this;
            }

            $builder->set($this->getLocaleColumnName($localeId));
            $builder->bindValue(
                ':' . $this->getLocaleColumnName($localeId),
                $property->getDbValue(),
                $this->getDataType()
            );

        }

        return $this;
    }

    /**
     * @see BaseField::applyConfiguration()
     */
    protected function applyConfiguration(array $config)
    {
        $this->applyLocalizationsConfig($config);
    }

    /**
     * Разбирает и применяет конфигурацию для локализации поля
     * @param array $config конфигурация поля
     * @throws UnexpectedValueException при ошибках в конфигурации
     */
    protected function applyLocalizationsConfig($config)
    {
        if (isset($config['localizations'])) {
            $localizations = $config['localizations'];
            if (!is_array($localizations)) {
                throw new UnexpectedValueException($this->translate(
                    'Localization configuration for localizable field should be an array.'
                ));
            }
            $this->localizations = $localizations;
        }
    }
}
