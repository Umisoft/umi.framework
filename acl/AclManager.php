<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\acl;

use umi\acl\exception\AlreadyExistentEntityException;
use umi\acl\exception\NonexistentEntityException;
use umi\acl\exception\RuntimeException;
use umi\i18n\ILocalizable;
use umi\i18n\TLocalizable;

/**
 * ACL-менеджер.
 */
class AclManager implements IAclManager, ILocalizable
{
    use TLocalizable;

    /**
     * @var array $roles список ролей
     */
    protected $roles = [];
    /**
     * @var array $resources список ресурсов
     */
    protected $resources = [
        self::RESOURCE_ALL => null
    ];
    /**
     * @var array $rules правила разрешений
     */
    protected $rules = [];

    /**
     * {@inheritdoc}
     */
    public function getRoleList()
    {
        return $this->roles;
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceList()
    {
        return array_keys($this->resources);
    }

    /**
     * {@inheritdoc}
     */
    public function addRole($roleName, array $parentRoleNames = [])
    {
        if ($this->hasRole($roleName)) {
            throw new AlreadyExistentEntityException(
                $this->translate(
                    'Cannot add role "{name}". Role already exists.',
                    ['name' => $roleName]
                )
            );
        }

        foreach ($parentRoleNames as $parentRoleName) {

            if (!$this->hasRole($parentRoleName)) {
                throw new NonexistentEntityException(
                    $this->translate(
                        'Cannot add role "{name}". Parent role {parentName} does not exist.',
                        [
                            'name' => $roleName,
                            'parentName' => $parentRoleName
                        ]
                    )
                );
            }
        }

        $this->roles[$roleName] = $parentRoleNames;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addResource($resourceName)
    {
        if ($this->hasResource($resourceName)) {
            throw new AlreadyExistentEntityException(
                $this->translate(
                    'Resource "{name}" already exists.',
                    ['name' => $resourceName]
                )
            );
        }
        $this->resources[$resourceName] = null;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasRole($roleName)
    {
        return isset($this->roles[$roleName]);
    }

    /**
     * {@inheritdoc}
     */
    public function hasResource($resourceName)
    {
        return array_key_exists($resourceName, $this->resources);
    }

    /**
     * {@inheritdoc}
     */
    public function allow($roleName, $resourceName = self::RESOURCE_ALL, $operationName = self::OPERATION_ALL, array $assertions = [])
    {

        if (!$this->hasRole($roleName)) {
            throw new NonexistentEntityException(
                $this->translate(
                    'Cannot set rule. Role "{name}" is unknown.',
                    ['name' => $roleName]
                )
            );
        }

        if (!$this->hasResource($resourceName)) {
            throw new NonexistentEntityException(
                $this->translate(
                    'Cannot set rule. Resource "{name}" is unknown.',
                    ['name' => $resourceName]
                )
            );
        }

        if (!isset($this->rules[$roleName])) {
            $this->rules[$roleName] = [];
        }
        if (!isset($this->rules[$roleName][$resourceName])) {
            $this->rules[$roleName][$resourceName] = [];
        }

        $this->rules[$roleName][$resourceName][$operationName] = $assertions;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isAllowed($role, $resource = self::RESOURCE_ALL, $operationName = self::OPERATION_ALL)
    {
        if ($role instanceof IAclRoleProvider) {
            $roleNames = $role->getRoleNames();
        } else {
            $roleNames = [$role];
        }

        $resourceName = $resource instanceof IAclResource ? $resource->getAclResourceName() : $resource;

        if (!$this->hasResource($resourceName)) {
            throw new NonexistentEntityException(
                $this->translate(
                    'Cannot check permission. Resource "{name}" is unknown.',
                    ['name' => $resourceName]
                )
            );
        }

        foreach ($roleNames as $roleName) {
            if (!$this->hasRole($roleName)) {
                throw new NonexistentEntityException(
                    $this->translate(
                        'Cannot check permission. Role "{name}" is unknown.',
                        ['name' => $roleName]
                    )
                );
            }

            $result = $this->getPermission($roleName, $resourceName, $operationName);

            if (is_array($result)) {

                if (!$resource instanceof IAclAssertionResolver) {
                    throw new RuntimeException(
                        $this->translate(
                            'Cannot check permission for role "{roleName}" for resource "{resourceName}".
                            Resource should be instance of IAclAssertionResolver',
                            [
                                'roleName' => $roleName,
                                'resourceName' => $resourceName
                            ]
                        )
                    );
                }

                $result = $resource->isAllowed($role, $operationName, $result);
            }

            if ($result === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * Проверяет разрешение на операцию над ресурсом для роли.
     * @param string $roleName имя роли
     * @param string $resourceName имя ресурса
     * @param string $operationName имя операции
     * @return bool|array bool в случае явно заданных разрешений, либо список дополнительных правил
     */
    protected function getPermission($roleName, $resourceName, $operationName)
    {
        if (isset($this->rules[$roleName][self::RESOURCE_ALL])) {

            if (isset($this->rules[$roleName][self::RESOURCE_ALL][self::OPERATION_ALL])) {
                $assertions = $this->rules[$roleName][self::RESOURCE_ALL][self::OPERATION_ALL];
            } elseif ($this->rules[$roleName][self::RESOURCE_ALL][$operationName]) {
                $assertions = $this->rules[$roleName][self::RESOURCE_ALL][$operationName];
            }

        } elseif (isset($this->rules[$roleName][$resourceName][self::OPERATION_ALL])) {
            $assertions = $this->rules[$roleName][$resourceName][self::OPERATION_ALL];
        } elseif (isset($this->rules[$roleName][$resourceName][$operationName])) {
            $assertions = $this->rules[$roleName][$resourceName][$operationName];
        }

        if (isset($assertions)) {
            return $assertions ?: true;
        }

        foreach ($this->roles[$roleName] as $parentRoleName) {
            $assertions = $this->getPermission($parentRoleName, $resourceName, $operationName);
            if ($assertions !== false) {
                return $assertions ?: true;
            }
        }

        return false;
    }


}
 