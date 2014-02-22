<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\hmvc\exception\http;

use umi\hmvc\exception\IException;

/**
 * HTTP исключения.
 */
class HttpException extends \Exception implements IException
{
    /**
     * {@inheritdoc}
     */
    public function __construct($code, $message, \Exception $prev = null)
    {
        parent::__construct($message, $code, $prev);
    }
}