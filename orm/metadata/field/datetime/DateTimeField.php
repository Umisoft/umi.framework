<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\orm\metadata\field\datetime;

use umi\orm\exception\NotAllowedOperationException;
use umi\orm\metadata\field\BaseField;
use umi\orm\object\IObject;
use umi\orm\object\property\datetime\DateTime;
use umi\orm\object\property\datetime\IDateTimeProperty;

/**
 * Класс поля для даты с учетом времени.
 */
class DateTimeField extends BaseField
{
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
        throw new NotAllowedOperationException($this->translate(
            'Cannot set value for property "{name}". Use DateTime methods to set value.',
            ['name' => $this->getName()]
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function preparePropertyValue(IObject $object, $internalDbValue)
    {
        /**
         * @var IDateTimeProperty $property
         */
        $property = $object->getProperty($this->getName());

        return new DateTime($internalDbValue, null, $property);
    }

    /**
     * {@inheritdoc}
     */
    public function prepareDbValue(IObject $object, $propertyValue)
    {
        $dbValue = null;
        if ($propertyValue instanceof DateTime && $propertyValue->getIsTimeSet()) {

            $dbValue = $propertyValue->format('Y-m-d H:i:s');
        }

        return $dbValue;
    }
}
