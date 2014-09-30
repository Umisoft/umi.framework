<?php
/**
 * This file is part of UMI.CMS.
 *
 * @link http://umi-cms.ru
 * @copyright Copyright (c) 2007-2014 Umisoft ltd. (http://umisoft.ru)
 * @license For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace umi\orm\metadata\field\special;

use umi\orm\exception\OutOfBoundsException;
use umi\orm\exception\RuntimeException;
use umi\orm\exception\UnexpectedValueException;
use umi\orm\metadata\field\BaseField;
use umi\orm\metadata\field\ICalculableField;
use umi\orm\metadata\field\IScalarField;
use umi\orm\metadata\field\TCalculableField;
use umi\orm\metadata\field\TScalarField;
use umi\orm\object\IObject;

/**
 * Класс поля, значение которого вычисляется заданным методом после сохранения всех объектов.
 */
class DelayedField extends BaseField implements IScalarField, ICalculableField
{
    use TScalarField;
    use TCalculableField;

    /**
     * @var string $dataType php-тип данных поля
     */
    protected $dataType;
    /**
     * @var array $allowedDataTypes разрешенные php-тип для данных поля
     */
    protected $allowedDataTypes = ['string', 'boolean', 'integer', 'float'];
    /**
     * @var string $formula название метода для вычисления значения поля
     */
    protected $formula;

    /**
     * {@inheritdoc}
     */
    public function getDataType()
    {
        return $this->dataType;
    }

    /**
     * {@inheritdoc}
     */
    public function validateInputPropertyValue($propertyValue)
    {
        switch(true) {
            case $this->dataType === 'string': {
                return is_string($propertyValue);
            }
            case $this->dataType === 'boolean': {
                return is_bool($propertyValue);
            }
            case $this->dataType === 'integer': {
                return is_int($propertyValue);
            }
            case $this->dataType === 'float': {
                return is_numeric($propertyValue);
            }
            default: {
                return false;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function calculateDBValue(IObject $object, $localeId = null)
    {
        if (!method_exists($object, $this->formula)) {
            throw new RuntimeException($this->translate(
                'Cannot calculate value for field "{name}". Object class "{class}" does not contain method "{method}".',
                [
                    'name' => $this->getName(),
                    'class' => get_class($object),
                    'method' => $this->formula
                ]
            ));
        }

        return $object->{$this->formula}($localeId);
    }

    /**
     * {@inheritdoc}
     */
    protected function applyConfiguration(array $config)
    {
        if (!isset($config['dataType']) || !is_string($config['dataType'])) {
            throw new UnexpectedValueException($this->translate(
                'Delayed field configuration should contain data type and it should be a string.'
            ));
        }

        if (!in_array($config['dataType'], $this->allowedDataTypes)) {
            throw new OutOfBoundsException($this->translate(
                'Data type "{type}" is not supported for delayed field.',
                ['type' => $config['dataType']]
            ));
        }

        if (!isset($config['formula']) || !is_string($config['formula'])) {
            throw new UnexpectedValueException($this->translate(
                'Delayed field configuration should contain option "formula" and it should be a string.'
            ));
        }

        $this->dataType = $config['dataType'];
        $this->formula = $config['formula'];
    }

}
 