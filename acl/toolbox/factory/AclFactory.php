<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\acl\toolbox\factory;

use umi\acl\exception\UnexpectedValueException;
use umi\acl\IAclFactory;
use umi\acl\IAclManager;
use umi\toolkit\factory\IFactory;
use umi\toolkit\factory\TFactory;

/**
 * Фабрика для сущностей ACL.
 */
class AclFactory implements IAclFactory, IFactory
{
    use TFactory;

    /**
     * @var string $aclManagerClass класс менеджера ACL
     */
    public $aclManagerClass = 'umi\acl\AclManager';

    /**
     * {@inheritdoc}
     */
    public function createAclManager(array $config = [])
    {
        /**
         * @var IAclManager $aclManager
         */
        $aclManager = $this->getPrototype(
            $this->aclManagerClass,
            ['umi\acl\IAclManager']
        )
            ->createInstance();

        return $this->configureAclManager($aclManager, $config);
    }

    /**
     * Конфигурирует ACL-менеджер.
     * @param IAclManager $aclManager
     * @param array $config
     * @throws UnexpectedValueException при неверно заданной конфигурации
     * @return IAclManager
     */
    protected function configureAclManager(IAclManager $aclManager, array $config)
    {
        if (isset($config[self::OPTION_ROLES])) {
            $this->configureAclRoles($aclManager, $config[self::OPTION_ROLES]);
        }
        if (isset($config[self::OPTION_RESOURCES])) {
            $this->configureAclResources($aclManager, $config[self::OPTION_RESOURCES]);
        }
        if (isset($config[self::OPTION_RULES])) {
            $this->configureAclRules($aclManager, $config[self::OPTION_RULES]);
        }

        return $aclManager;
    }

    /**
     * Конфигурирует роли.
     * @param IAclManager $aclManager
     * @param array $rolesConfig
     * @throws UnexpectedValueException
     */
    private function configureAclRoles(IAclManager $aclManager, $rolesConfig)
    {
        if (!is_array($rolesConfig)) {
            throw new UnexpectedValueException(
                $this->translate(
                    'Roles configuration for ACL Manager should be an array.'
                )
            );
        }

        foreach ($rolesConfig as $roleName => $parentRoles) {

            if (!is_array($parentRoles)) {
                throw new UnexpectedValueException(
                    $this->translate(
                        'Parent roles configuration for role "{role}" should be an array.',
                        ['role' => $roleName]
                    )
                );
            }

            $aclManager->addRole($roleName, $parentRoles);
        }
    }

    /**
     * Конфигурирует ресурсы и операции над ними.
     * @param IAclManager $aclManager
     * @param array $resourcesConfig
     * @throws UnexpectedValueException
     */
    private function configureAclResources(IAclManager $aclManager, $resourcesConfig)
    {
        if (!is_array($resourcesConfig)) {
            throw new UnexpectedValueException(
                $this->translate(
                    'Resources configuration for ACL Manager should be an array.'
                )
            );
        }

        foreach ($resourcesConfig as $resourceName) {
            $aclManager->addResource($resourceName);
        }
    }

    /**
     * Конфигурирует разрешения на операции над ресурсами для ролей.
     * @param IAclManager $aclManager
     * @param array $rulesConfig
     * @throws UnexpectedValueException
     */
    private function configureAclRules(IAclManager $aclManager, $rulesConfig)
    {
        if (!is_array($rulesConfig)) {
            throw new UnexpectedValueException(
                $this->translate(
                    'Rules configuration for ACL Manager should be an array.'
                )
            );
        }

        foreach ($rulesConfig as $roleName => $resources) {

            if (!is_array($resources)) {
                throw new UnexpectedValueException(
                    $this->translate(
                        'Rules for role "{role}" should be an array.',
                        ['role' => $roleName]
                    )
                );
            }

            if (empty($resources)) {
                $resources = [IAclManager::RESOURCE_ALL => []];
            }

            foreach ($resources as $resourceName => $operations) {
                if (!is_array($operations)) {
                    throw new UnexpectedValueException(
                        $this->translate(
                            'Allowed list of operations for role "{role}" and for resource "{resource}" should be an array.',
                            [
                                'role' => $roleName,
                                'resource' => $resourceName
                            ]
                        )
                    );
                }

                if (empty($operations)) {
                    $operations = [IAclManager::OPERATION_ALL => []];
                }

                foreach ($operations as $operationName => $assertions) {
                    if (!is_array($assertions)) {
                        throw new UnexpectedValueException(
                            $this->translate(
                                'Assertions for operation "{operation}", for role "{role}" and for resource "{resource}" should be an array.',
                                [
                                    'operation' => $operationName,
                                    'role' => $roleName,
                                    'resource' => $resourceName
                                ]
                            )
                        );
                    }
                    $aclManager->allow($roleName, $resourceName, $operationName, $assertions);
                }
            }
        }
    }
}
 