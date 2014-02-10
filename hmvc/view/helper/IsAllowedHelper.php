<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\hmvc\view\helper;

use umi\acl\IAclManager;
use umi\acl\IAclResource;
use umi\hmvc\dispatcher\IDispatcher;

/**
 * Помощник вида для проверки прав в текущем компоненте.
 */
class IsAllowedHelper
{
    /**
     * @var IDispatcher $dispatcher
     */
    protected $dispatcher;

    /**
     * Конструктор.
     * @param IDispatcher $dispatcher диспетчер компонентов
     */
    public function __construct(IDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Проверяет права на ресурс.
     * @param IAclResource|string $resource ресурс или имя ресурса
     * @param string $operationName имя операции над ресурсом
     * @return bool
     */
    public function __invoke($resource, $operationName = IAclManager::OPERATION_ALL)
    {
        $context = $this->dispatcher->getCurrentContext();

        return $this->dispatcher->checkPermissions($context->getComponent(), $resource, $operationName);
    }
}
 