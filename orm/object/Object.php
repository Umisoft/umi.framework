<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\orm\object;

use umi\i18n\ILocalesAware;
use umi\i18n\ILocalesService;
use umi\i18n\ILocalizable;
use umi\i18n\TLocalesAware;
use umi\i18n\TLocalizable;
use umi\orm\collection\ICollection;
use umi\orm\exception\InvalidArgumentException;
use umi\orm\exception\NonexistentEntityException;
use umi\orm\exception\NotAllowedOperationException;
use umi\orm\exception\ReadOnlyEntityException;
use umi\orm\exception\RuntimeException;
use umi\orm\manager\IObjectManagerAware;
use umi\orm\manager\TObjectManagerAware;
use umi\orm\metadata\field\IRelationField;
use umi\orm\metadata\field\relation\BelongsToRelationField;
use umi\orm\metadata\IObjectType;
use umi\orm\object\property\IProperty;
use umi\orm\object\property\IPropertyFactory;
use umi\orm\persister\IObjectPersisterAware;
use umi\orm\persister\TObjectPersisterAware;
use umi\orm\selector\ISelector;

/**
 * Базовый объект данных.
 */
class Object implements IObject, ILocalizable, ILocalesAware, IObjectManagerAware, IObjectPersisterAware
{

    use TLocalizable;
    use TLocalesAware;
    use TObjectManagerAware;
    use TObjectPersisterAware;

    /**
     * @var bool $isModified флаг "измененный"
     */
    protected $isModified = false;
    /**
     * @var bool $isNew флаг "новый"
     */
    protected $isNew = false;
    /**
     * @var bool $isUnloaded флаг "выгружен из менеджера объектов"
     */
    protected $isUnloaded = false;
    /**
     * @var string $collectionName имя коллекции, к которой принадлежит объект
     */
    protected $collectionName;
    /**
     * @var string $typeName имя типа объекта
     */
    protected $typeName;
    /**
     * @var ICollection $collection коллекция, к которой принадлежит объект
     */
    protected $collection;
    /**
     * @var IObjectType $type тип объекта
     */
    protected $type;
    /**
     * @var array $initialValues значения свойств при инициализации в формате
     * array(propName => internalValue, propName => array('localeId' => internalValue), ...)
     */
    protected $initialValues = [];
    /**
     * @var IProperty[] $properties массив свойств вида array('propName' => IProperty, 'propName#localeId' => IProperty)
     */
    protected $properties = [];
    /**
     * @var array $validationErrors массив ошибок валидации в формате
     * array('propName' => array('error string', ...), ...)
     */
    protected $validationErrors = [];
    /**
     * @var IPropertyFactory $propertyFactory фабрика свойств объекта
     */
    protected $propertyFactory;
    /**
     * @var string $localization настройки изначальной загрузки объекта
     */
    private $localization;

    /**
     * Конструктор.
     * @param ICollection $collection коллекция, к которой принадлежит объект
     * @param IObjectType $objectType тип объекта
     * @param IPropertyFactory $propertyFactory фабрика свойств объекта
     */
    public function __construct(ICollection $collection, IObjectType $objectType, IPropertyFactory $propertyFactory)
    {
        $this->type = $objectType;
        $this->collection = $collection;
        $this->propertyFactory = $propertyFactory;
        $this->isUnloaded = false;
    }

    /**
     * Сериализует объект
     * @throws RuntimeException если объект модифицирован либо новый
     * @return string строковое представление объекта
     */
    public function serialize()
    {
        if ($this->getIsNew()) {
            throw new RuntimeException($this->translate(
                'Cannot serialize new object.'
            ));
        }

        if ($this->collection && $this->type) {
            $this->fullyLoad(ILocalesService::LOCALE_ALL);

            $values = [];
            foreach ($this->getLoadedProperties() as $property) {
                $values[$property->getFullName()] = $property->getPersistedDbValue();
            }
        } else {
            $values = $this->initialValues;
        }

        $data = [
            $values,
            $this->getCollectionName(),
            $this->getTypeName()
        ];

        return serialize($data);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        list($this->initialValues, $this->collectionName, $this->typeName) = unserialize($serialized);
        $this->isUnloaded = true;
    }

    /**
     * {@inheritdoc}
     */
    public function setInitialValues(array $initialValues)
    {
        foreach ($initialValues as $propertyFullName => $value) {
            list ($propertyName, $localeId) = $this->splitFullPropName($propertyFullName);
            if (
                !$this->getType()->getFieldExists($propertyName) ||
                ($localeId && !$this->getType()->getField($propertyName)->hasLocale($localeId))
            ) {
                unset($initialValues[$propertyFullName]);
            }
        }

        $this->initialValues = array_merge($this->initialValues, $initialValues);
        foreach ($initialValues as $fullPropName => $internalValue) {
            if (isset($this->properties[$fullPropName])) {
                /**
                 * @var IProperty $property
                 */
                $property = $this->properties[$fullPropName];
                if (!$property->getIsLoaded()) {
                    $property->setInitialValue($internalValue);
                }
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getInitialValues()
    {
        return $this->initialValues;
    }

    /**
     * {@inheritdoc}
     */
    public function getCollectionName()
    {
        if ($this->collectionName) {
            return $this->collectionName;
        }

        return $this->collectionName = $this->collection->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeName()
    {
        if ($this->typeName) {
            return $this->typeName;
        }

        return $this->typeName = $this->type->getName();
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
    public function getTypePath()
    {
        $typePath = $this->collection->getName() . IObjectType::PATH_SEPARATOR . $this->type->getName();

        return $typePath;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        $primaryKeyProperty = $this->getProperty(self::FIELD_IDENTIFY);
        if (!$primaryKeyProperty->getIsLoaded()) {
            return null;
        }

        return $primaryKeyProperty->getValue();
    }

    /**
     * {@inheritdoc}
     */
    public function getGUID()
    {
        $guidProperty = $this->getProperty(self::FIELD_GUID);

        return $guidProperty->getValue();
    }

    /**
     * {@inheritdoc}
     */
    public function setGUID($guid)
    {
        if (!$this->getIsNew()) {
            throw new NotAllowedOperationException(
                $this->translate(
                    'Cannot set GUID. GUID can be set only for new objects.'
                )
            );
        }

        $guidProperty = $this->getProperty(self::FIELD_GUID);
        $oldGuid = $guidProperty->getValue();
        $guidProperty->setValue($guid);

        $this->getObjectManager()->changeObjectGuid($this, $oldGuid);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        $versionFieldProperty = $this->getProperty(self::FIELD_VERSION);

        return $versionFieldProperty->getValue();
    }

    /**
     * {@inheritdoc}
     */
    public function setVersion($version)
    {
        $versionFieldProperty = $this->getProperty(self::FIELD_VERSION);
        $versionFieldProperty->setInitialValue($version);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLoadedProperties()
    {
        $properties = [];
        foreach ($this->initialValues as $fullPropName => $internalValue) {
            list ($propName, $localeId) = $this->splitFullPropName($fullPropName);
            $properties[$fullPropName] = $this->getProperty($propName, $localeId);
        }

        return $properties;
    }

    /**
     * {@inheritdoc}
     */
    public function getModifiedProperties()
    {
        $result = [];

        foreach ($this->properties as $fullName => $property) {
            if ($property->getIsModified()) {
                $result[$fullName] = $property;
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllProperties()
    {
        foreach ($this->type->getFields() as $fieldName => $field) {
            if ($field->getIsLocalized()) {
                foreach ($field->getLocalizations() as $localeId => $fieldInfo) {
                    $this->getProperty($fieldName, $localeId);
                }
            } else {
                $this->getProperty($fieldName);
            }
        }

        return $this->properties;
    }

    /**
     * {@inheritdoc}
     */
    public function hasProperty($propName, $localeId = null)
    {
        if (!$this->type->getFieldExists($propName)) {
            return false;
        }
        $field = $this->type->getField($propName);
        if (!$field->getIsLocalized()) {
            return is_null($localeId);
        } else {
            if (is_null($localeId)) {
                $localeId = ($this->getLoadLocalization() === ILocalesService::LOCALE_CURRENT) ?
                    $this->getCurrentDataLocale() : $this->getLoadLocalization();
            }

            return $field->hasLocale($localeId);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getProperty($propName, $localeId = null)
    {
        if ($this->isUnloaded) {
            throw new RuntimeException($this->translate(
                'Cannot get property "{name}". Object "{class}" is unloaded.',
                ['name' => $propName, 'class' => __CLASS__]
            ));
        }

        $fullPropName = $propName;
        $field = $this->type->getField($propName);

        if (!$localeId && $field->getIsLocalized()) {
            $localeId = ($this->getLoadLocalization() === ILocalesService::LOCALE_CURRENT) ?
                $this->getCurrentDataLocale() : $this->getLoadLocalization();
        }
        if ($localeId) {
            $fullPropName .= IProperty::LOCALE_SEPARATOR . $localeId;
        }

        if (isset($this->properties[$fullPropName])) {
            return $this->properties[$fullPropName];
        }

        if (!$this->hasProperty($propName, $localeId)) {
            throw new NonexistentEntityException($this->translate(
                'Property "{name}" does not exist in "type".',
                ['name' => $fullPropName, 'type' => $this->getTypePath()]
            ));
        }

        $property = $this->propertyFactory->createProperty($this, $field, $localeId);

        $this->initializeProperty($property);

        return $this->properties[$fullPropName] = $property;
    }

    /**
     * {@inheritdoc}
     */
    public function getPropertyByPath($propPath, $localeId = null)
    {
        $propNameParts = explode(ISelector::FIELD_SEPARATOR, $propPath);

        $propName = array_pop($propNameParts);
        if (!count($propNameParts)) {
            return $this->getProperty($propName, $localeId);
        }

        $object = $this->getValueByPath(implode(ISelector::FIELD_SEPARATOR, $propNameParts));
        if (!$object instanceof IObject) {

            throw new InvalidArgumentException(
                $this->translate(
                    'Cannot resolve property path "{path}".',
                    ['path' => $propPath]
                )
            );
        }

        return $object->getProperty($propName, $localeId);
    }

    /**
     * {@inheritdoc}
     */
    public function getValue($propName, $localeId = null)
    {
        if (!$this->hasProperty($propName, $localeId)) {
            return null;
        }
        $property = $this->getProperty($propName, $localeId);
        $accessorMethod = $property->getAccessor();
        if (!empty($accessorMethod) && method_exists($this, $accessorMethod)) {
            return $this->$accessorMethod($propName, $localeId);
        }

        if ($property->getField()->getIsLocalized()) {
            $value = $this->getLocalizedValue($property, $localeId);
        } else {
            $value = $property->getValue();
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getValueByPath($propPath, $localeId = null)
    {
        $propNameParts = explode(ISelector::FIELD_SEPARATOR, $propPath);

        $value = $this;
        for ($i = 0; $i < count($propNameParts); $i++) {

            if (!$value->hasProperty($propNameParts[$i])) {
                return null;
            }

            $valueLocaleId = $localeId;
            $field = $value->getProperty($propNameParts[$i])->getField();
            if (!$field->getIsLocalized()) {
                $valueLocaleId = null;
            }

            $value = $value->getValue($propNameParts[$i], $valueLocaleId);
            if (is_null($value)) return null;

            if (($i < count($propNameParts) - 1) && !$value instanceof IObject) {
                throw new InvalidArgumentException(
                    $this->translate(
                        'Cannot resolve property path "{path}".',
                        ['path' => $propPath]
                    )
                );
            }

        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultValue($propName, $localeId = null)
    {
        if ($this->hasProperty($propName, $localeId)) {
            $this->getProperty($propName, $localeId)
                ->setDefaultValue();
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($propName, $value, $localeId = null)
    {

        if ($this->isUnloaded) {
            throw new RuntimeException($this->translate(
                'Cannot set value for object. Object "{class}" is unloaded.',
                ['class' => __CLASS__]
            ));
        }

        $property = $this->getProperty($propName, $localeId);

        if ($property->getIsReadOnly()) {
            throw new ReadOnlyEntityException($this->translate(
                'Cannot set value for property "{name}". Property is read only.',
                ['name' => $property->getFullName()]
            ));
        }
        $mutatorMethod = $property->getMutator();

        if (!empty($mutatorMethod) && method_exists($this, $mutatorMethod)) {
            $this->$mutatorMethod($value, $localeId);
        } else {
            $property->setValue($value);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setValueByPath($propPath, $value, $localeId = null)
    {
        $propNameParts = explode(ISelector::FIELD_SEPARATOR, $propPath);

        $propName = array_pop($propNameParts);
        if (!count($propNameParts)) {
            return $this->setValue($propName, $value, $localeId);
        }

        $object = $this->getValueByPath(implode(ISelector::FIELD_SEPARATOR, $propNameParts));
        if (!$object instanceof IObject) {
            throw new InvalidArgumentException(
                $this->translate(
                    'Cannot resolve property path "{path}".',
                    ['path' => $propPath]
                )
            );
        }

        return $object->setValue($value, $localeId);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsNew()
    {
        return $this->isNew;
    }

    /**
     * {@inheritdoc}
     */
    public function getIsUnloaded()
    {
        return $this->isUnloaded;
    }

    /**
     * {@inheritdoc}
     */
    public function setIsNew($new = true)
    {
        $this->isNew = (bool) $new;

        return $this;
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
    public function setIsModified()
    {
        if (!$this->isModified) {
            $this->isModified = true;
            $this->getObjectPersister()
                ->markAsModified($this);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setIsConsistent()
    {
        $this->isModified = false;
        foreach ($this->properties as $property) {
            $property->setIsConsistent();
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function rollback()
    {
        if ($this->getIsModified()) {
            foreach ($this->getModifiedProperties() as $property) {
                $property->rollback();
            }
            $this->isModified = false;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function validate()
    {
        $this->validationErrors = [];

        if (!$this->getIsModified() && !$this->getIsNew()) {
            return true;
        }

        $result = true;

        foreach ($this->getAllProperties() as $property) {
            if (!$property->validate()) {
                $this->validationErrors[$property->getFullName()] = $property->getValidationErrors();
                $result = false;
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getValidationErrors()
    {
        return $this->validationErrors;
    }

    /**
     * {@inheritdoc}
     */
    public function fullyLoad($localization = ILocalesService::LOCALE_CURRENT)
    {
        $this->collection->fullyLoadObject($this, $localization);
    }

    /**
     * {@inheritdoc}
     */
    public function unload()
    {
        $this->getObjectManager()
            ->unloadObject($this);

        $this->properties = [];
        $this->initialValues = [];
        $this->isUnloaded = true;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        $properties = [];
        if (isset($this->properties[self::FIELD_IDENTIFY])) {
            $properties[self::FIELD_IDENTIFY] = $this->properties[self::FIELD_IDENTIFY];
        }
        if (isset($this->properties[self::FIELD_GUID])) {
            $properties[self::FIELD_GUID] = $this->properties[self::FIELD_GUID];
        }
        if (isset($this->properties[self::FIELD_TYPE])) {
            $properties[self::FIELD_TYPE] = $this->properties[self::FIELD_TYPE];
        }
        $this->properties = $properties;

        $initialValues = [];
        if (isset($this->initialValues[self::FIELD_IDENTIFY])) {
            $initialValues[self::FIELD_IDENTIFY] = $this->initialValues[self::FIELD_IDENTIFY];
        }
        if (isset($this->initialValues[self::FIELD_GUID])) {
            $initialValues[self::FIELD_GUID] = $this->initialValues[self::FIELD_GUID];
        }
        if (isset($this->initialValues[self::FIELD_TYPE])) {
            $initialValues[self::FIELD_TYPE] = $this->initialValues[self::FIELD_TYPE];
        }
        $this->initialValues = $initialValues;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setLoadLocalization($localization = ILocalesService::LOCALE_CURRENT)
    {
        if (!$this->localization) {
            $this->localization = $localization;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLoadLocalization()
    {
        if ($this->localization) {
            return $this->localization;
        }

        return ILocalesService::LOCALE_CURRENT;
    }

    /**
     * {@inheritdoc}
     */
    public function __set($propName, $value)
    {
        $this->setValue($propName, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function __get($propName)
    {
        return $this->getValue($propName);
    }

    /**
     * {@inheritdoc}
     */
    public function __isset($propName)
    {
        return $this->hasProperty($propName);
    }

    /**
     * {@inheritdoc}
     */
    public function __unset($propName)
    {
        $this->setDefaultValue($propName);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return $this->hasProperty($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->getValue($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->setValue($offset, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        $this->setDefaultValue($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        $currentFullPropName = key($this->initialValues);
        list ($propName, $localeId) = $this->splitFullPropName($currentFullPropName);

        return $this->getValue($propName, $localeId);
    }
    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return key($this->initialValues);
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        next($this->initialValues);
        $nextFullPropName = key($this->initialValues);
        list ($propName, $localeId) = $this->splitFullPropName($nextFullPropName);

        return $this->getValue($propName, $localeId);
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        reset($this->initialValues);
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return !is_null(key($this->initialValues));
    }

    /**
     * Возвращает имя свойства и его локаль по полному имени свойства
     * @param string $fullPropName полное имя свойства
     * @return array
     */
    protected function splitFullPropName($fullPropName)
    {
        $propInfo = explode(IProperty::LOCALE_SEPARATOR, $fullPropName);
        $propName = $propInfo[0];
        $localeId = isset($propInfo[1]) ? $propInfo[1] : null;

        return [$propName, $localeId];
    }

    /**
     * Инициализирует совойство
     * @param IProperty $property
     */
    protected function initializeProperty(IProperty $property)
    {
        $field = $property->getField();
        $propFullName = $property->getFullName();
        if ($field instanceof IRelationField && !$field instanceof BelongsToRelationField) {
            $property->setInitialValue(null);
        } elseif (array_key_exists($propFullName, $this->initialValues)) {
            $property->setInitialValue($this->initialValues[$propFullName]);
        }
    }

    /**
     * Возвращает значение локализованого свойства. <br/>
     * Если поле локализовано, и значение в текущей локали отсутствует, возвращается значение дефолтной локали.
     * @param IProperty $property
     * @param string $localeId идентификатор локали
     * @return mixed
     */
    protected function getLocalizedValue(IProperty $property, $localeId)
    {

        if ($localeId || !is_null($property->getValue()) || $property->getLocaleId() === $this->getDefaultDataLocale()) {
            return $property->getValue();
        }
        $defaultProperty = $this->getProperty($property->getName(), $this->getDefaultDataLocale());

        return $defaultProperty->getValue();

    }
}
