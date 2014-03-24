<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\hmvc\widget;

use umi\acl\IAclResource;

/**
 * Базовый класс виджета, доступ к которому может контролироваться через ACL.
 */
abstract class BaseSecureWidget extends BaseWidget implements IAclResource
{
    const ACL_RESOURCE_PREFIX = 'widget:';

    /**
     * {@inheritdoc}
     */
    public function getAclResourceName()
    {
        return self::ACL_RESOURCE_PREFIX . $this->name;
    }
}
 