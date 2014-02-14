<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\orm\persister;

use umi\orm\exception\RequiredDependencyException;

/**
 * Трейт для внедрения синхронизатора объектов с базой.
 */
trait TObjectPersisterAware
{
    /**
     * @var IObjectPersister $traitObjectPersister синхронизатор объектов
     */
    private $traitObjectPersister;

    /**
     * @see IObjectPersisterAware::setObjectPersister()
     */
    public function setObjectPersister(IObjectPersister $objectPersister)
    {
        $this->traitObjectPersister = $objectPersister;
    }

    /**
     * Возвращает синхронизатор объектов
     * @throws RequiredDependencyException если синхронизатор объектов не установлен
     * @return IObjectPersister
     */
    protected function getObjectPersister()
    {
        if (!$this->traitObjectPersister) {
            throw new RequiredDependencyException(sprintf(
                'Object persister is not injected in class "%s".',
                get_class($this)
            ));
        }

        return $this->traitObjectPersister;
    }
}
