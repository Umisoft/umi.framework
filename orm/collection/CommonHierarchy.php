<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\orm\collection;

use umi\orm\metadata\IObjectType;
use umi\orm\object\IHierarchicObject;
use umi\orm\object\IObject;

/**
 * Общая иерархия.
 */
class CommonHierarchy extends BaseHierarchicCollection implements ICommonHierarchy, ICollectionManagerAware
{
    use TCollectionManagerAware;

    /**
     * {@inheritdoc}
     */
    public function contains(IObject $object)
    {
        if (parent::contains($object)) {
            return true;
        }

        if ($object->getCollection() instanceof ILinkedHierarchicCollection) {
            return $object
                ->getCollection()
                ->getCommonHierarchy() === $this;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function add(
        ILinkedHierarchicCollection $linkedCollection,
        $slug,
        $typeName = IObjectType::BASE,
        IHierarchicObject $branch = null,
        $guid = null
    )
    {
        return $linkedCollection->add($slug, $typeName, $branch, $guid);
    }
}
