<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\http\toolbox\factory;

use umi\http\IHttpFactory;
use umi\http\Request;
use umi\toolkit\factory\IFactory;
use umi\toolkit\factory\TFactory;

/**
 * Фабрику HTTP сущностей.
 */
class HttpFactory implements IHttpFactory, IFactory
{

    use TFactory;

    /**
     * @var string $requestClass имя класса HTTP запроса
     */
    public $requestClass = 'umi\http\Request';
    /**
     * @var string $responseClass имя класса HTTP ответа
     */
    public $responseClass = 'umi\http\Response';

    /**
     * {@inheritdoc}
     */
    public function createRequest()
    {
        /**
         * @var Request $requestClass
         */
        $requestClass = $this->requestClass;
        return $requestClass::createFromGlobals();
    }

    /**
     * {@inheritdoc}
     */
    public function createResponse()
    {
        return new $this->responseClass;
    }
}
