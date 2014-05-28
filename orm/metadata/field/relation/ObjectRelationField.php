<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\orm\metadata\field\relation;

use umi\orm\collection\ICollectionManagerAware;
use umi\orm\collection\TCollectionManagerAware;
use umi\orm\exception\InvalidArgumentException;
use umi\orm\metadata\field\BaseField;
use umi\orm\object\IObject;

/**
 * Поле, для хранения ссылки на объект произвольной коллекции.
 */
class ObjectRelationField extends BaseField implements ICollectionManagerAware
{
    use TCollectionManagerAware;

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
        if (!$propertyValue instanceof IObject) {
            throw new InvalidArgumentException($this->translate(
                'Value must be instance of IObject.'
            ));
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function preparePropertyValue(IObject $object, $internalDbValue)
    {
        if ($internalDbValue) {
            list($collectionName, $id) = unserialize($internalDbValue);
            if ($this->getCollectionManager()->hasCollection($collectionName)) {
                try {
                    return $this->getCollectionManager()
                        ->getCollection($collectionName)
                        ->getById($id);
                } catch (\Exception $e) {
                    return null;
                }
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareDbValue(IObject $object, $propertyValue)
    {
        if ($propertyValue instanceof IObject) {
            return serialize([
                $propertyValue->getCollectionName(),
                $propertyValue->getId()
            ]);
        }

        return null;
    }
}
 