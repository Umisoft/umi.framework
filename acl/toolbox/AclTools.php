<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\acl\toolbox;

use umi\acl\IAclAware;
use umi\acl\IAclFactory;
use umi\toolkit\toolbox\IToolbox;
use umi\toolkit\toolbox\TToolbox;

/**
 * Инструменты для создания ACL.
 */
class AclTools implements IToolbox
{
    /**
     * Имя набора инструментов
     */
    const NAME = 'acl';

    use TToolbox;

    /**
     * @var string $aclManagerClass класс менеджера ACL
     */
    public $aclFactoryClass = 'umi\acl\toolbox\factory\AclFactory';

    /**
     * Конструктор.
     */
    public function __construct()
    {
        $this->registerFactory(
            'acl',
            $this->aclFactoryClass,
            ['umi\acl\IAclFactory']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function injectDependencies($object)
    {
        if ($object instanceof IAclAware) {
            $object->setAclFactory($this->getAclFactory());
        }
    }

    /**
     * Возвращает фабрику сущностей ACL
     * @return IAclFactory
     */
    protected function getAclFactory()
    {
        return $this->getFactory('acl');
    }
}

 