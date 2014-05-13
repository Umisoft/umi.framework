<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\hmvc\exception\acl;

use Exception;
use umi\acl\IAclResource;
use umi\hmvc\exception\IException;

/**
 * Исключение, связанное с недостаточностью прав на доступ к ресурсу.
 */
class ResourceAccessForbiddenException extends Exception implements IException
{
    /**
     * @var IAclResource $resource ресурс, доступ к которому ограничен
     */
    protected $resource;

    /**
     * Конструктор.
     * @param IAclResource $resource
     * {@inheritdoc}
     */
    public function __construct(IAclResource $resource, $message = "", $code = 0, Exception $previous = null)
    {
        $this->resource = $resource;
        parent::__construct($message, $code, $previous);
    }

    /**
     * Возвращает ресурс, доступ к которому ограничен.
     * @return IAclResource
     */
    public function getResource()
    {
        return $this->resource;
    }
}
 