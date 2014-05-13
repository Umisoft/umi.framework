<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\orm\toolbox\factory;

use umi\orm\metadata\field\ICalculableField;
use umi\orm\metadata\field\IField;
use umi\orm\metadata\field\special\CounterField;
use umi\orm\metadata\field\special\FileField;
use umi\orm\object\IObject;
use umi\orm\object\property\calculable\ICalculableProperty;
use umi\orm\object\property\calculable\ICounterProperty;
use umi\orm\object\property\file\IFileProperty;
use umi\orm\object\property\IProperty;
use umi\orm\object\property\IPropertyFactory;
use umi\toolkit\factory\IFactory;
use umi\toolkit\factory\TFactory;

/**
 * Фабрика свойств объекта.
 */
class PropertyFactory implements IPropertyFactory, IFactory
{

    use TFactory;

    /**
     * @var string $defaultClass класс свойства по умолчанию
     */
    public $defaultPropertyClass = 'umi\orm\object\property\Property';
    /**
     * @var string $defaultCalculablePropertyClass класс свойства с вычисляемым значением
     */
    public $defaultCalculablePropertyClass = 'umi\orm\object\property\calculable\CalculableProperty';
    /**
     * @var string $defaultCounterPropertyClass класс свойства-счетчика
     */
    public $defaultCounterPropertyClass = 'umi\orm\object\property\calculable\CounterProperty';
    /**
     * @var string $defaultFilePropertyClass класс свойства со значением типа файл
     */
    public $defaultFilePropertyClass = 'umi\orm\object\property\file\FileProperty';

    /**
     * {@inheritdoc}
     */
    public function createProperty(IObject $object, IField $field, $localeId = null)
    {
        /**
         * @var CounterField|FileField|ICalculableField|IField $field
         */
        switch (true) {
            case ($field instanceof CounterField):
            {
                return $this->createCounterProperty($object, $field, $localeId);
            }
            case ($field instanceof FileField):
            {
                return $this->createFileProperty($object, $field, $localeId);
            }
            case ($field instanceof ICalculableField):
            {
                return $this->createCalculableProperty($object, $field, $localeId);
            }
            default:
            {
                return $this->createCommonProperty($object, $field, $localeId);
            }
        }
    }

    /**
     * Создает экземпляр обычного свойства для указанного объекта
     * @param IObject $object объект
     * @param IField $field поле типа данных
     * @param string $localeId идентификатор локали для свойства
     * @return IProperty
     */
    protected function createCommonProperty(IObject $object, IField $field, $localeId = null)
    {
        $property = $this->getPrototype(
            $this->defaultPropertyClass,
            ['umi\orm\object\property\IProperty']
        )
        ->createInstance([$object, $field, $localeId]);

        return $property;
    }

    /**
     * Создает экземпляр вычисляемого свойства для указанного объекта
     * @param IObject $object объект
     * @param ICalculableField $field поле типа данных
     * @param string $localeId идентификатор локали для свойства
     * @return ICalculableProperty
     */
    protected function createCalculableProperty(IObject $object, ICalculableField $field, $localeId = null)
    {
        $property = $this->getPrototype(
            $this->defaultCalculablePropertyClass,
            ['umi\orm\object\property\calculable\ICalculableProperty']
        )
        ->createInstance([$object, $field, $localeId]);

        return $property;
    }

    /**
     * Создает экземпляр обычного свойства для указанного объекта
     * @param IObject $object объект
     * @param CounterField $field поле типа данных
     * @param string $localeId идентификатор локали для свойства
     * @return ICounterProperty
     */
    protected function createCounterProperty(IObject $object, CounterField $field, $localeId = null)
    {
        $property = $this->getPrototype(
            $this->defaultCounterPropertyClass,
            ['umi\orm\object\property\calculable\ICounterProperty']
        )
        ->createInstance([$object, $field, $localeId]);

        return $property;
    }

    /**
     * Создает экземпляр свойства со значением типа файл для указанного объекта
     * @param IObject $object объект
     * @param FileField $field поле типа данных
     * @param string $localeId идентификатор локали для свойства
     * @return IFileProperty
     */
    protected function createFileProperty(IObject $object, FileField $field, $localeId = null)
    {
        $property = $this->getPrototype(
            $this->defaultFilePropertyClass,
            ['umi\orm\object\property\file\IFileProperty']
        )
            ->createInstance([$object, $field, $localeId]);

        return $property;
    }

}
