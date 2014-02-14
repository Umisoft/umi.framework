<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\orm\metadata\field;

use umi\orm\object\IObject;

/**
 * Трейт для поддержки скалярных полей
 */
trait TScalarField
{
    /**
     * @see IField::getName()
     */
    abstract public function getName();

    /**
     * @see IField::getDataType()
     */
    abstract public function getDataType();

    /**
     * @see IField::preparePropertyValue()
     */
    public function preparePropertyValue(IObject $object, $internalDbValue)
    {
        if (is_null($internalDbValue)) {
            return null;
        }

        @settype($internalDbValue, $this->getDataType());

        return $internalDbValue;
    }

    /**
     * @see IField::prepareDbValue()
     */
    public function prepareDbValue(IObject $object, $propertyValue)
    {
        return $propertyValue;
    }
}
