<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\form\adapter;

use umi\form\element\IFormElement;
use umi\form\element\IChoiceFormElement;
use umi\form\exception\UnexpectedValueException;
use umi\orm\collection\ICollectionManagerAware;
use umi\orm\collection\TCollectionManagerAware;
use umi\orm\metadata\field\IRelationField;
use umi\orm\metadata\field\relation\BelongsToRelationField;
use umi\orm\metadata\field\relation\HasManyRelationField;
use umi\orm\metadata\field\relation\HasOneRelationField;
use umi\orm\metadata\field\relation\ManyToManyRelationField;
use umi\orm\metadata\field\relation\ObjectRelationField;
use umi\orm\object\IObject;
use umi\orm\objectset\IManyToManyObjectSet;
use umi\orm\objectset\IObjectSet;

/**
 * Адаптер формы для данных в виде ORM-объекта
 */
class ObjectFormAdapter implements IDataAdapter, ICollectionManagerAware
{
    use TCollectionManagerAware;

    /**
     * @var IObject $data провайдер данных для формы
     */
    protected $data;

    /**
     * Конструктор.
     * @param IObject $data провайдер данных для формы
     */
    public function __construct(IObject $data)
    {
        $this->data = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function setData(IFormElement $element, $data)
    {
        if ($dataSource = $element->getDataSource()) {

            $field = $this->data->getPropertyByPath($dataSource)->getField();
            switch(true) {
                case $field instanceof HasManyRelationField: {
                    $this->setObjectSetData($dataSource, $data);
                    break;
                }
                case $field instanceof BelongsToRelationField: {
                    $this->setObjectData($dataSource, $data);
                    break;
                }
                case $field instanceof ManyToManyRelationField: {
                    $this->setManyToManyObjectSetData($dataSource, $data);
                    break;
                }
                case $field instanceof ObjectRelationField: {
                    $this->setObjectRelationData($dataSource, $data);
                    break;
                }
                default: {
                    $this->setScalarData($dataSource, $data);
                }
            }

        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getData(IFormElement $element)
    {
        if ($dataSource = $element->getDataSource()) {

            $field = $this->data->getPropertyByPath($dataSource)->getField();
            switch(true) {
                case $field instanceof HasOneRelationField:
                case $field instanceof BelongsToRelationField: {
                    return $this->getObjectData($dataSource);
                }
                case $field instanceof HasManyRelationField:
                case $field instanceof ManyToManyRelationField: {
                    return $this->getObjectSetData($dataSource);
                }
                case $field instanceof ObjectRelationField: {
                    return $this->getObjectRelationData($dataSource);
                }
                default: {
                    return $this->getScalarData($dataSource);
                }
            }

        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function validate(IFormElement $element, $value)
    {
        if ($dataSource = $element->getDataSource()) {
            return $this->data->getPropertyByPath($dataSource)->validate($value);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getValidationErrors(IFormElement $element)
    {
        if ($dataSource = $element->getDataSource()) {
            return $this->data->getPropertyByPath($dataSource)->getValidationErrors();
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getChoices(IChoiceFormElement $element)
    {
        $choices = [];
        $dataSource = $element->getDataSource();
        $choiceValueSource = $element->getChoiceValueSource();

        if ($dataSource && $choiceValueSource) {

            $field = $this->data->getPropertyByPath($dataSource)->getField();
            if ($field instanceof IRelationField) {

                $choiceLabelSource = $element->getChoiceLabelSource();

                //TODO оптимизировать селектор для одного запроса
                $select = $field->getTargetCollection()
                    ->select();

                /**
                 * @var IObject $choice
                 */
                foreach($select as $choice) {
                    $value = $choice->getValueByPath($choiceValueSource);
                    $label = $choiceLabelSource ? $choice->getValueByPath($choiceLabelSource) : $value;
                    $choices[$value] = $label;
                }
            }

        }

        return $choices;
    }

    /**
     * Возвращает данные формы для источников со скалярным значением.
     * @param string $dataSource идентификатор источника данных для элемента
     * @return mixed|null
     */
    private function getScalarData($dataSource)
    {
        return $this->data->getValueByPath($dataSource);
    }

    /**
     * Устанавливает скалярные данные в провайдер данных.
     * @param string $dataSource идентификатор источника данных для элемента
     * @param mixed $data данные
     * @throws UnexpectedValueException если данные в неверном формате
     */
    private function setScalarData($dataSource, $data)
    {
        $this->data->setValueByPath($dataSource, $data);
    }


    /**
     * Возвращает данные формы для источников со значением в виде IObject.
     * @param string $dataSource идентификатор источника данных для элемента
     * @return int|null
     */
    private function getObjectData($dataSource)
    {
        $value = $this->data->getValueByPath($dataSource);
        if ($value instanceof IObject) {
            $value = $value->getId();
        }

        return $value;
    }

    /**
     * Устанавливает в провайдер данных данные в поле типа BelongsToRelationField.
     * @param string $dataSource идентификатор источника данных для элемента
     * @param int $data данные
     * @throws UnexpectedValueException если данные в неверном формате
     */
    private function setObjectData($dataSource, $data)
    {
        if (!is_numeric($data) && !is_null($data)) {
            throw new UnexpectedValueException('Cannot set data. Data should be numeric or null.');
        }
        $property = $this->data->getPropertyByPath($dataSource);
        /**
         * @var BelongsToRelationField $field
         */
        $field = $property->getField();
        $value = $data ? $field->getTargetCollection()->getById($data) : null;

        $this->data->setValueByPath($dataSource, $value);
    }

    /**
     * Возвращает данные формы для источников со значением в виде IObject.
     * @param string $dataSource идентификатор источника данных для элемента
     * @return string|null
     */
    private function getObjectRelationData($dataSource)
    {
        $value = $this->data->getValueByPath($dataSource);
        if ($value instanceof IObject) {
            $value = $value->getCollectionName() . ObjectRelationField::SEPARATOR . $value->getGUID();
        }

        return $value;
    }

    /**
     * Устанавливает в провайдер данных данные в поле типа ObjectRelationField.
     * @param string $dataSource идентификатор источника данных для элемента
     * @param string $data данные
     * @throws UnexpectedValueException если данные в неверном формате
     */
    private function setObjectRelationData($dataSource, $data)
    {
        if (!is_string($data) && !is_null($data)) {
            throw new UnexpectedValueException('Cannot set data. Data should be string or null.');
        }

        $info = explode(ObjectRelationField::SEPARATOR, $data);
        if (count($info) != 2) {
            throw new UnexpectedValueException('Cannot set data. Invalid data format.');
        }
        list ($collectionName, $guid) = $info;

        $value = $this->getCollectionManager()->getCollection($collectionName)->get($guid);

        $this->data->setValueByPath($dataSource, $value);
    }

    /**
     * Возвращает данные формы для источников со значением в виде IObjectSet.
     * @param string $dataSource идентификатор источника данных для элемента
     * @return int|null
     */
    private function getObjectSetData($dataSource)
    {
        $values = [];
        /**
         * @var IObjectSet $value
         */
        $value = $this->data->getValueByPath($dataSource);
        /**
         * @var IObject $object
         */
        foreach($value as $object) {
            $values[] = $object->getId();
        }

        return $values;
    }

    /**
     * Устанавливает в провайдер данных данные в поле типа HasManyRelationField.
     * @param string $dataSource идентификатор источника данных для элемента
     * @param array $data
     * @throws UnexpectedValueException если данные в неверном формате
     */
    private function setObjectSetData($dataSource, $data)
    {
        if (!is_array($data)) {
            throw new UnexpectedValueException('Cannot set data for HasManyRelationField. Data should be an array.');
        }

        $property = $this->data->getPropertyByPath($dataSource);
        /**
         * @var HasManyRelationField $field
         */
        $field = $property->getField();
        $targetCollection = $field->getTargetCollection();

        /**
         * @var IObjectSet $objectSet
         */
        $objectSet = $this->data->getValueByPath($dataSource);
        /**
         * @var IObject $object
         */
        foreach($objectSet as $object) {
            $object->setValue($field->getTargetFieldName(), null);
        }

        foreach ($data as $id) {
            $targetCollection->getById($id)->setValue($field->getTargetFieldName(), $this->data);
        }
    }

    /**
     * Устанавливает в провайдер данных данные в поле типа ManyToManyRelationField.
     * @param string $dataSource идентификатор источника данных для элемента
     * @param array $data
     * @throws UnexpectedValueException если данные в неверном формате
     */
    private function setManyToManyObjectSetData($dataSource, $data)
    {
        if (!is_array($data)) {
            throw new UnexpectedValueException('Cannot set data for ManyToManyRelationField. Data should be an array.');
        }

        $property = $this->data->getPropertyByPath($dataSource);
        /**
         * @var ManyToManyRelationField $field
         */
        $field = $property->getField();
        $targetCollection = $field->getTargetCollection();

        /**
         * @var IManyToManyObjectSet $objectSet
         */
        $objectSet = $this->data->getValueByPath($dataSource);
        $objectSet->detachAll();

        foreach ($data as $id) {
            $objectSet->link($targetCollection->getById($id));
        }
    }

}
 