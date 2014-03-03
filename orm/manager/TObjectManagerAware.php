<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\orm\manager;

use umi\orm\exception\RequiredDependencyException;

/**
 * Трейт для внедрения менеджера объектов.
 */
trait TObjectManagerAware
{
    /**
     * @var IObjectManager $traitObjectManager менеджер объектов
     */
    private $traitObjectManager;

    /**
     * @see IObjectManagerAware::setObjectManager()
     */
    public function setObjectManager(IObjectManager $objectManager)
    {
        $this->traitObjectManager = $objectManager;
    }

    /**
     * Возвращает менеджер объектов
     * @throws RequiredDependencyException если менеджер объектов не установлен
     * @return IObjectManager
     */
    protected function getObjectManager()
    {
        if (!$this->traitObjectManager) {
            throw new RequiredDependencyException(sprintf(
                'Object manager is not injected in class "%s".',
                get_class($this)
            ));
        }

        return $this->traitObjectManager;
    }
}
