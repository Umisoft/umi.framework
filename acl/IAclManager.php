<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\acl;

use umi\acl\exception\NonexistentEntityException;
use umi\acl\exception\RuntimeException;

/**
 * ACL-менеджер.
 */
interface IAclManager
{

    /**
     * Алиас, задающий все ресурсы
     */
    const RESOURCE_ALL = '*';
    /**
     * Алиас, задающий все операции
     */
    const OPERATION_ALL = '*';

    /**
     * Добавляет роль.
     * @param string $roleName имя роли
     * @param array $parentRoleNames список имен родительских ролей
     * @throws RuntimeException если невозможно добавить роль
     * @return self
     */
    public function addRole($roleName, array $parentRoleNames = []);

    /**
     * Добавляет ресурс.
     * @param string $resourceName имя ресурса
     * @return self
     */
    public function addResource($resourceName);

    /**
     * Проверяет, существует ли роль.
     * @param string $roleName имя роли
     * @return bool
     */
    public function hasRole($roleName);

    /**
     * Проверяет, существует ли ресурс.
     * @param string $resourceName имя ресурса
     * @return bool
     */
    public function hasResource($resourceName);

    /**
     * Устанавливает разрешения для операции над ресурсом.
     * @param string $roleName имя роли
     * @param string $resourceName имя ресурса
     * @param string $operationName имя операции
     * @param array $assertions список дополнительных проверок
     * @return self
     */
    public function allow($roleName, $resourceName = self::RESOURCE_ALL, $operationName = self::OPERATION_ALL, array $assertions = []);

    /**
     * Проверяет разрешение на операцию над ресурсом для роли.
     * @param IAclRoleProvider|string $role провайдер ролей или имя роли
     * @param IAclResource|string $resource ресурс или имя ресурса
     * @param string $operationName имя операции
     * @throws NonexistentEntityException если роль, ресурс или операция не существуют
     * @return bool
     */
    public function isAllowed($role, $resource = self::RESOURCE_ALL, $operationName = self::OPERATION_ALL);

}
 