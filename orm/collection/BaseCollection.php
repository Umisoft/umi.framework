<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\orm\collection;

use umi\i18n\ILocalesAware;
use umi\i18n\ILocalesService;
use umi\i18n\ILocalizable;
use umi\i18n\TLocalesAware;
use umi\i18n\TLocalizable;
use umi\orm\exception\InvalidArgumentException;
use umi\orm\exception\LoadEntityException;
use umi\orm\exception\NonexistentEntityException;
use umi\orm\exception\NotAllowedOperationException;
use umi\orm\exception\RuntimeException;
use umi\orm\manager\IObjectManagerAware;
use umi\orm\manager\TObjectManagerAware;
use umi\orm\metadata\field\IField;
use umi\orm\metadata\field\relation\ManyToManyRelationField;
use umi\orm\metadata\field\special\FormulaField;
use umi\orm\metadata\IMetadata;
use umi\orm\metadata\IObjectType;
use umi\orm\object\IObject;
use umi\orm\object\property\calculable\ICalculableProperty;
use umi\orm\object\property\IProperty;
use umi\orm\persister\IObjectPersisterAware;
use umi\orm\persister\TObjectPersisterAware;
use umi\orm\selector\ISelector;
use umi\orm\selector\ISelectorFactory;

/**
 * Базовый класс коллекции объектов.
 */
abstract class BaseCollection
    implements ICollection, ILocalizable, ILocalesAware, IObjectManagerAware, IObjectPersisterAware
{

    use TLocalizable;
    use TObjectManagerAware;
    use TObjectPersisterAware;
    use TLocalesAware;

    /**
     * @var callable $selectorInitializer инициализатор для селектора
     */
    protected static $selectorInitializer;
    /**
     * @var string $name имя коллекции
     */
    protected $name;
    /**
     * @var IMetadata $metadata метаданные коллекции
     */
    protected $metadata;
    /**
     * @var ISelectorFactory $selectorFactory
     */
    protected $selectorFactory;
    /**
     * @var array $config конфигурация
     */
    protected $config;

    /**
     * Устанавливает инициализатор для селектора
     * @param callable $initializer
     */
    public static function setSelectorInitializer(callable $initializer = null)
    {
        self::$selectorInitializer = $initializer;
    }

    /**
     * Конструктор
     * @param string $collectionName имя коллекции
     * @param IMetadata $metadata метаданные коллекции
     * @param ISelectorFactory $selectorFactory фабрика селекторов
     * @param array $config конфигурация коллекции
     */
    public function __construct($collectionName, IMetadata $metadata, ISelectorFactory $selectorFactory, array $config = [])
    {
        $this->name = $collectionName;
        $this->metadata = $metadata;
        $this->selectorFactory = $selectorFactory;
        $this->config = $config;
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
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * {@inheritdoc}
     */
    public function contains(IObject $object)
    {
        return $object->getCollection() === $this;
    }

    /**
     * {@inheritdoc}
     */
    public function get($guid, $localization = ILocalesService::LOCALE_CURRENT)
    {
        if (!$this->getGUIDField()->checkGUIDFormat($guid)) {
            throw new InvalidArgumentException($this->translate(
                'Cannot get object by GUID "{guid}". Wrong GUID format.',
                ['guid' => $guid]
            ));
        }

        if (!$object = $this->getObjectManager()->getObjectInstanceByGuid($guid)) {
            $objectsSet = $this->select()
                ->where(
                    $this->getGUIDField()
                        ->getName()
                )
                ->equals(strtolower($guid))
                ->localization($localization)
                ->result();

            //closing cursor explicitly for SQLite
            $all = $objectsSet->fetchAll();
            $result = array_shift($all);

            if (!$object = $result) {
                throw new NonexistentEntityException($this->translate(
                    'Cannot get object with GUID "{guid}" from collection "{collection}".',
                    ['guid' => $guid, 'collection' => $this->getName()]
                ));
            }
        }

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($objectId, $localization = ILocalesService::LOCALE_CURRENT)
    {
        if (!$object = $this->getObjectManager()->getObjectInstanceById($this, $objectId)) {
            $objectsSet = $this->select()
                ->where(
                    $this->getIdentifyField()
                        ->getName()
                )
                ->equals($objectId)
                ->localization($localization)
                ->result();

            //closing cursor explicitly for SQLite
            $all = $objectsSet->fetchAll();
            $result = array_shift($all);
            if (!$object = $result) {
                throw new RuntimeException($this->translate(
                    'Cannot get object with id "{id}" from collection "{collection}".',
                    ['id' => $objectId, 'collection' => $this->getName()]
                ));
            }
        }

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function loadObject(IObjectType $objectType, array $objectInfo)
    {
        $identify = $this->getIdentifyField()->getName();
        $guid = $this->getGUIDField()->getName();

        if (!isset($objectInfo[$identify])) {
            throw new LoadEntityException($this->translate(
                'Cannot load object. Identify field value is not found.'
            ));
        }
        if (!isset($objectInfo[$guid])) {
            throw new LoadEntityException($this->translate(
                'Cannot load object. GUID value is not found.'
            ));
        }

        $object = $this->getObjectManager()
            ->registerLoadedObject($this, $objectType, $objectInfo[$identify], $objectInfo[$guid]);
        $object->setInitialValues($objectInfo);

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function fullyLoadObject(IObject $object, $localization = ILocalesService::LOCALE_CURRENT)
    {
        if (!$object->getId()) {
            throw new LoadEntityException($this->translate(
                'Cannot load object. Object id required.'
            ));
        }

        $fieldsToLoad = [];
        $loadedValues = $object->getInitialValues();

        foreach ($object->getType()->getFields() as $fieldName => $field) {

            if (!array_key_exists($fieldName, $loadedValues) ||
                ($localization === ILocalesService::LOCALE_ALL && $field->getIsLocalized())
            ) {
                $fieldsToLoad[] = $fieldName;
            }
        }

        if (count($fieldsToLoad)) {

            $pkFiledName = $this->getIdentifyField()->getName();

            $objectsSet = $this->select()
                ->fields($fieldsToLoad)
                ->localization($localization)
                ->where($pkFiledName)
                ->equals($object->getId())
                ->result();

            if (!$objectsSet->fetch()) {
                throw new LoadEntityException($this->translate(
                    'Cannot load object with id "{id}" from collection "{collection}".',
                    ['id' => $object->getId(), 'collection' => $this->getName()]
                ));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function select()
    {
        return $this->selectorFactory->createSelector($this);
    }

    /**
     * {@inheritdoc}
     */
    public function emptySelect()
    {
        return $this->selectorFactory->createEmptySelector($this);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(IObject $object)
    {
        if (!$this->contains($object)) {
            throw new NotAllowedOperationException($this->translate(
                'Cannot delete object. Object from another collection given.'
            ));
        }
        $this->getObjectPersister()->markAsDeleted($object);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getManyToManySelector(IObject $object, ManyToManyRelationField $manyToManyRelationField)
    {
        return $this->selectorFactory->createManyToManySelector($object, $manyToManyRelationField, $this);
    }

    /**
     * {@inheritdoc}
     */
    public function getForcedFieldsToLoad()
    {
        return [
            IObject::FIELD_IDENTIFY => $this->getRequiredField(IObject::FIELD_IDENTIFY),
            IObject::FIELD_GUID     => $this->getRequiredField(IObject::FIELD_GUID),
            IObject::FIELD_TYPE     => $this->getRequiredField(IObject::FIELD_TYPE),
            IObject::FIELD_VERSION  => $this->getRequiredField(IObject::FIELD_VERSION)
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifyField()
    {
        return $this->getRequiredField(IObject::FIELD_IDENTIFY);
    }

    /**
     * {@inheritdoc}
     */
    public function getGUIDField()
    {
        return $this->getRequiredField(IObject::FIELD_GUID);
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectTypeField()
    {
        return $this->getRequiredField(IObject::FIELD_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function getVersionField()
    {
        return $this->getRequiredField(IObject::FIELD_VERSION);
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceAlias()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldAlias($fieldName)
    {
        return $this->name . ISelector::ALIAS_SEPARATOR . $fieldName;
    }

    /**
     * Запускает запросы на добавление в БД нового объекта коллекции.
     * @internal
     * @param IObject $object
     * @throws RuntimeException
     * @return mixed
     */
    public function persistNewObject(IObject $object)
    {
        if (!$this->contains($object)) {
            throw new NotAllowedOperationException($this->translate(
                'Cannot persist new object. Object from another collection given.'
            ));
        }

        $dataSource = $this->getMetadata()
            ->getCollectionDataSource();
        $identifyColumnName = $this->getIdentifyField()
            ->getColumnName();

        $insertBuilder = $dataSource->insert();

        // set object id
        if ($object->getId()) {
            $insertBuilder
                ->set($identifyColumnName)
                ->bindValue(
                    ':' . $identifyColumnName,
                    $object->getId(),
                    $this->getIdentifyField()->getDataType()
                );
        }

        // set type
        $typeName = $object->getTypePath();
        $objectTypeField = $this->getObjectTypeField();
        $columnName = $objectTypeField->getColumnName();
        $insertBuilder->set($columnName);
        $insertBuilder->bindValue(':' . $columnName, $typeName, $objectTypeField->getDataType());

        foreach ($object->getModifiedProperties() as $property) {
            if ($this->getMetadata()->getFieldExists($property->getName())) {
                $field = $this->getMetadata()->getField($property->getName());
                $field->persistProperty($object, $property, $insertBuilder);
            }
        }

        $insertBuilder->execute();

        if (!$object->getId()) {
            $objectId = $insertBuilder->getConnection()->lastInsertId();
            if (!$objectId) {
                throw new RuntimeException($this->translate(
                    'Cannot persist object. Cannot get last inserted id for object.'
                ));
            }
            $object->getProperty(IObject::FIELD_IDENTIFY)
                ->setInitialValue($objectId);
        }

    }

    /**
     * {@inheritdoc}
     */
    public function persistRecalculatedObject(IObject $object, array $formulaProperties)
    {
        if (!$this->contains($object)) {
            throw new NotAllowedOperationException($this->translate(
                'Cannot persist modified object. Object from another collection given.'
            ));
        }
        if (count($formulaProperties)) {
            $this->executeUpdate($object, $formulaProperties);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function persistModifiedObject(IObject $object)
    {
        if (!$this->contains($object)) {
            throw new NotAllowedOperationException($this->translate(
                'Cannot persist modified object. Object from another collection given.'
            ));
        }

        $modifiedProperties = [];
        $formulaProperties = [];

        foreach ($object->getModifiedProperties()as $property) {
            if ($property->getField() instanceof FormulaField) {
                $formulaProperties[] = $property;
            } else {
                $modifiedProperties[] = $property;
            }
        }

        if ($formulaProperties) {
            $this->getObjectPersister()->storeRecalculatedObject($object, $formulaProperties);
        }

        if (count($modifiedProperties)) {
            $this->executeUpdate($object, $modifiedProperties);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function persistDeletedObject(IObject $object)
    {
        if (!$this->contains($object)) {
            throw new NotAllowedOperationException($this->translate(
                'Cannot persist deleted object. Object from another collection given.'
            ));
        }
        $dataSource = $this->getMetadata()->getCollectionDataSource();

        $deleteBuilder = $dataSource->delete();

        $deleteBuilder
            ->where()
            ->expr($this->getIdentifyField()->getColumnName(), '=', ':objectId');

        $deleteBuilder->bindValue(
            ':objectId',
            $object->getId(),
            $this->getIdentifyField()->getDataType()
        );

        $result = $deleteBuilder->execute();
        if ($result->rowCount() != 1) {
            throw new RuntimeException($this->translate(
                'Cannot delete object with id "{id}" and type "{type}". Database row is not modified.',
                ['id' => $object->getId(), 'type' => $object->getTypePath()]
            ));
        }
    }

    /**
     * Возвращает обязательное поле коллекции.
     * @param $fieldName
     * @throws NonexistentEntityException если поле не существует
     * @return IField
     */
    protected function getRequiredField($fieldName)
    {
        if (!$this->metadata->getFieldExists($fieldName)) {
            throw new NonexistentEntityException($this->translate(
                'Collection "{collection}" does not contain required field "{name}".',
                ['collection' => $this->name, 'name' => $fieldName]
            ));
        }

        return $this->metadata->getField($fieldName);
    }

    /**
     * Запускает обновление объекта по указанным свойствам
     * @param IObject $object
     * @param IProperty[] $properties
     * @throws RuntimeException если обновление не прошло
     */
    protected function executeUpdate(IObject $object, array $properties)
    {
        $dataSource = $this->getMetadata()->getCollectionDataSource();
        $identifyColumnName = $this->getIdentifyField()->getColumnName();
        $updateBuilder = $dataSource->update();

        foreach ($properties as $property) {
            if ($this->getMetadata()->getFieldExists($property->getName())) {
                $field = $this->getMetadata()->getField($property->getName());
                $field->persistProperty($object, $property, $updateBuilder);
            }
        }

        if ($updateBuilder->getUpdatePossible()) {

            $versionProperty = $object->getProperty(IObject::FIELD_VERSION);
            $version = (int) ($versionProperty->getPersistedDbValue() ? : $versionProperty->getDbValue());
            $newVersion = $version + 1;
            $versionProperty->setValue($newVersion);

            $versionColumnName = $this->getVersionField()
                ->getColumnName();

            $this->getVersionField()
                ->persistProperty($object, $versionProperty, $updateBuilder);

            $updateBuilder->where()
                ->expr($identifyColumnName, '=', ':objectId');
            $updateBuilder->bindValue(
                ':objectId',
                $object->getId(),
                $this->getIdentifyField()
                    ->getDataType()
            );

            $updateBuilder->where()
                ->expr($versionColumnName, '=', ':' . $versionColumnName);
            $updateBuilder->bindValue(
                ':' . $versionColumnName,
                $version,
                $this->getVersionField()
                    ->getDataType()
            );

            $result = $updateBuilder->execute();

            if ($result->rowCount() != 1) {

                $selectBuilder = $dataSource->select($versionColumnName);
                $selectBuilder->where()
                    ->expr($identifyColumnName, '=', ':objectId');
                $selectBuilder->bindValue(
                    ':objectId',
                    $object->getId(),
                    $this->getIdentifyField()
                        ->getDataType()
                );

                $selectResult = $selectBuilder->execute();
                $selectResultRow = $selectResult->fetch();

                if (is_array($selectResultRow) && $selectResultRow[$versionColumnName] != $version) {
                    throw new RuntimeException($this->translate(
                        'Cannot modify object with id "{id}" and type "{type}". Object is out of date.',
                        ['id' => $object->getId(), 'type' => $object->getTypePath()]
                    ));
                }

                throw new RuntimeException($this->translate(
                    'Cannot modify object with id "{id}" and type "{type}". Database row is not modified.',
                    ['id' => $object->getId(), 'type' => $object->getTypePath()]
                ));
            }
        }
    }
}
