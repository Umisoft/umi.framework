<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\orm\metadata\field\relation;

use umi\orm\collection\ICollection;
use umi\orm\collection\ICollectionManagerAware;
use umi\orm\collection\TCollectionManagerAware;
use umi\orm\exception\NotAllowedOperationException;
use umi\orm\metadata\field\BaseField;
use umi\orm\metadata\field\IRelationField;
use umi\orm\metadata\field\TRelationField;
use umi\orm\object\IObject;
use umi\orm\selector\ISelector;

/**
 * Класс поля связи "многие-ко-многим".
 */
class ManyToManyRelationField extends BaseField implements IRelationField, ICollectionManagerAware
{

    use TRelationField;
    use TCollectionManagerAware;

    /**
     * {@inheritdoc}
     */
    public function getTargetCollectionName()
    {
        return $this->targetCollectionName;
    }

    /**
     * {@inheritdoc}
     */
    public function getTargetCollection()
    {
        return $this->getCollectionManager()
            ->getCollection($this->targetCollectionName);
    }

    /**
     * Возвращает имя поля для связи с target-коллекцией
     * @return string
     */
    public function getTargetFieldName()
    {
        return $this->targetFieldName;
    }

    /**
     * Возвращает имя коллекции, которая является мостом для связи c target-коллекцией
     * @return string
     */
    public function getBridgeCollectionName()
    {
        return $this->bridgeCollectionName;
    }

    /**
     * Возвращает имя коллекции, которая является мостом для связи c target-коллекцией
     * @return ICollection
     */
    public function getBridgeCollection()
    {
        return $this->getCollectionManager()->getCollection($this->bridgeCollectionName);
    }

    /**
     * Возвращает имя связанного поля в bridge-коллекции
     * @return string
     */
    public function getRelatedFieldName()
    {
        return $this->relatedFieldName;
    }

    /**
     * {@inheritdoc}
     */
    public function getDataType()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    protected function applyConfiguration(array $config)
    {
        $this->applyTargetCollectionConfig($config);
        $this->applyTargetFieldConfig($config);
        $this->applyRelatedFieldConfig($config);
        $this->applyBridgeCollectionConfig($config);
    }

    /**
     * {@inheritdoc}
     */
    public function validateInputPropertyValue($propertyValue)
    {
        throw new NotAllowedOperationException($this->translate(
            'Cannot set value for property "{name}". Value should be appended to IManyToManyObjectSet.',
            ['name' => $this->getName()]
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function preparePropertyValue(IObject $object, $internalDbValue)
    {
        $targetCollection = $this->getTargetCollection();

        $selector = $targetCollection->getManyToManySelector($object, $this);
        $selectBuilder = $selector->getSelectBuilder();

        $targetPkAlias = $targetCollection->getSourceAlias() . ISelector::FIELD_SEPARATOR;
        $targetPkAlias .= $targetCollection->getIdentifyField()->getColumnName();

        $relationCollection = $object->getCollection();
        $relationTableName = $relationCollection->getMetadata()->getCollectionDataSource()->getSourceName();
        $relationTableAlias = $relationCollection->getSourceAlias();
        $relationPk = $relationCollection->getIdentifyField();
        $relationPkAlias = $relationTableAlias . ISelector::FIELD_SEPARATOR;
        $relationPkAlias .= $relationPk->getColumnName();

        $bridgeCollection = $this->getBridgeCollection();
        $bridgeCollectionAlias = $bridgeCollection->getSourceAlias();
        $bridgeMetadata = $bridgeCollection->getMetadata();
        $bridgeTableName = $bridgeMetadata->getCollectionDataSource()->getSourceName();
        $targetFieldAlias = $bridgeCollectionAlias . ISelector::FIELD_SEPARATOR;
        $targetFieldAlias .= $bridgeMetadata->getField($this->getTargetFieldName())->getColumnName();
        $relatedFieldAlias = $bridgeCollectionAlias . ISelector::FIELD_SEPARATOR;
        $relatedFieldAlias .= $bridgeMetadata->getField($this->getRelatedFieldName())->getColumnName();

        $selectBuilder
            ->leftJoin(array($bridgeTableName, $bridgeCollectionAlias))
            ->on($targetFieldAlias, '=', $targetPkAlias);

        $selectBuilder
            ->leftJoin(array($relationTableName, $relationTableAlias))
            ->on($relationPkAlias, '=', $relatedFieldAlias);

        $selectBuilder->where()
            ->expr($relationPkAlias, '=', ':objectId');
        $selectBuilder->bindValue(
            ':objectId',
            $object->getId(),
            $relationPk->getDataType()
        );

        $selectBuilder->groupBy($targetPkAlias);

        return $selector->result();
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function prepareDbValue(IObject $object, $propertyValue)
    {
        return null;
    }
}
