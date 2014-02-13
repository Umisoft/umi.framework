<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\acl;

use umi\acl\exception\RequiredDependencyException;

/**
 * Трейт для внедрения поддержки ACL.
 */
trait TAclAware
{
    /**
     * @var IAclFactory $_aclFactory
     */
    private $_aclFactory;

    /**
     * Устанавливает фабрику сущностей ACL.
     * @param IAclFactory $aclFactory
     * @return self
     */
    public function setAclFactory(IAclFactory $aclFactory)
    {
        $this->_aclFactory = $aclFactory;

        return $this;
    }

    /**
     * Возвращает фабрику сущностей ACL.
     * @throws RequiredDependencyException если фабрика не была внедрена
     * @return IAclFactory
     */
    protected function getAclFactory()
    {
        if (!$this->_aclFactory) {
            throw new RequiredDependencyException(sprintf(
                'ACL factory is not injected in class "%s".',
                get_class($this)
            ));
        }

        return $this->_aclFactory;
    }

}
 