<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\orm\metadata\field\datetime;

use DateTime;
use umi\orm\metadata\field\BaseField;
use umi\orm\object\IObject;

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
        return ($propertyValue instanceof DateTime);
    }

    /**
     * {@inheritdoc}
     */
    public function preparePropertyValue(IObject $object, $internalDbValue)
    {
        if (is_null($internalDbValue)) {
            return null;
        }

        return new DateTime($internalDbValue);
    }

    /**
     * {@inheritdoc}
     */
    public function prepareDbValue(IObject $object, $propertyValue)
    {
        $dbValue = null;
        if ($propertyValue instanceof DateTime) {

            $dbValue = $propertyValue->format('Y-m-d H:i:s');
        }

        return $dbValue;
    }
}
