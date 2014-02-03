<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\acl;

/**
 * Интерфейс для проверки прав на операцию с учетом заданных правил.
 */
interface IAclAssertionResolver
{
    /**
     * Проверяет права на операцию с учетом заданных правил.
     * @param IAclRoleProvider|string $role провайдер ролей или имя роли
     * @param string $operationName имя операции
     * @param array $assertions список правил проверки
     * @return bool
     */
    public function isAllowed($role, $operationName, array $assertions);
}
 