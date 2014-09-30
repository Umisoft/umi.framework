<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\orm\metadata\field\special;

use umi\dbal\builder\IInsertBuilder;
use umi\dbal\builder\IQueryBuilder;
use umi\dbal\builder\IUpdateBuilder;
use umi\orm\metadata\field\BaseField;
use umi\orm\metadata\field\ICalculableField;
use umi\orm\metadata\field\IScalarField;
use umi\orm\metadata\field\TScalarField;
use umi\orm\object\IObject;
use umi\orm\object\property\IProperty;

/**
 * Slug для ЧПУ.
 */
class SlugField extends BaseField implements IScalarField, ICalculableField
{
    use TScalarField;

    /**
     * {@inheritdoc}
     */
    public function getDataType()
    {
        return 'string';
    }

    /**
     * {@inheritdoc}
     */
    public function validateInputPropertyValue($propertyValue)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function calculateDBValue(IObject $object, $localeId = null)
    {
        $setValue = $object->getValue($this->getName(), $localeId);

        return $setValue ?: $object->getId();
    }

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
            $value = $this->calculateDBValue($object, $localeId);
            if ($property->getPersistedValue() == $value) {
                return $this;
            }

            $builder->set($this->getColumnName($localeId));
            $builder->bindValue(':' . $this->getColumnName($localeId), $value, $this->getDataType());
            $property->setValue($value);
        }

        return $this;
    }
}
