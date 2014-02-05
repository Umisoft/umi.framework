<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\http;

use umi\http\exception\RequiredDependencyException;

/**
 * Трейт для использования HTTP-компонента.
 */
trait THttpAware
{
    /**
     * @var IHttpFactory $_httpFactory фабрика HTTP сущностей
     */
    private $_httpFactory;

    /**
     * Устанавливает фабрику HTTP сущностей.
     * @param IHttpFactory $httpFactory фабрика
     */
    public final function setHttpFactory(IHttpFactory $httpFactory)
    {
        $this->_httpFactory = $httpFactory;
    }

    /**
     * Создает HTTP-запрос к серверу из глобальных переменных.
     * @return Request
     */
    protected final function createHttpRequest()
    {
        return $this->getHttpFactory()
            ->createRequest();
    }

    /**
     * Создает HTTP-ответ к серверу.
     * @return Response
     */
    protected final function createHttpResponse()
    {
        return $this->getHttpFactory()
            ->createResponse();
    }

    /**
     * Возвращает фабрику сущностей HTTP.
     * @return IHttpFactory
     * @throws RequiredDependencyException если фабрика не установлена
     */
    private final function getHttpFactory()
    {
        if (!$this->_httpFactory) {
            throw new RequiredDependencyException(sprintf(
                'Http factory is not injected in class "%s".',
                get_class($this)
            ));
        }

        return $this->_httpFactory;
    }
}
