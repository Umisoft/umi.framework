<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\orm\metadata\field;

use umi\dbal\builder\IQueryBuilder;
use umi\dbal\builder\IUpdateBuilder;
use umi\orm\object\IObject;
use umi\orm\object\property\IProperty;

/**
 * Трейт для полей с вычисляемым значением.
 */
trait TCalculableField
{

    /**
     * @see IField::getDataType()
     */
    abstract public function getDataType();

    /**
     * @see IField::getColumnName()
     */
    abstract public function getColumnName($localeId = null);

    /**
     * @see IField::hasLocale()
     */
    abstract public function hasLocale($localeId);

    /**
     * @see ICalculableField::calculateDBValue()
     */
    abstract public function calculateDBValue(IObject $object);

    /**
     * @see IField::persistProperty()
     */
    public function persistProperty(IObject $object, IProperty $property, IQueryBuilder $builder)
    {

        if ($builder instanceof IUpdateBuilder) {

            $localeId = $property->getLocaleId();

            if ($localeId && !$this->hasLocale($localeId)) {
                return $this;
            }

            $builder->set($this->getColumnName($localeId));
            $value = $this->calculateDBValue($object, $localeId);
            $builder->bindValue(':' . $this->getColumnName($localeId), $value, $this->getDataType());
            $property->setValue($value);
        }

        return $this;
    }
}
