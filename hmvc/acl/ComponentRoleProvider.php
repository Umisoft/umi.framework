<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\hmvc\acl;

use umi\acl\IAclRoleProvider;
use umi\hmvc\component\IComponent;

/**
 * Интерфейс субъекта, которому назначены роли для MVC-компонента.
 */
class ComponentRoleProvider implements IAclRoleProvider
{
    /**
     * @var IComponent $component
     */
    protected $component;
    /**
     * @var IComponentRoleResolver $identity
     */
    protected $identity;

    public function __construct(IComponent $component, IComponentRoleResolver $identity)
    {
        $this->component = $component;
        $this->identity = $identity;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoleNames()
    {
        return $this->identity->getRoleNames($this->component);
    }

    /**
     * Возвращает пользователя.
     * @return IComponentRoleResolver
     */
    public function getIdentity()
    {
        return $this->identity;
    }
}
 