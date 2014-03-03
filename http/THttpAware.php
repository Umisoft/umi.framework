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
     * @var IHttpFactory $traitHttpFactory фабрика HTTP-сущностей
     */
    private $traitHttpFactory;

    /**
     * @see IHttpAware::setHttpFactory()
     */
    public function setHttpFactory(IHttpFactory $httpFactory)
    {
        $this->traitHttpFactory = $httpFactory;
    }

    /**
     * Создает HTTP-запрос к серверу из глобальных переменных.
     * @return Request
     */
    protected function createHttpRequest()
    {
        return $this->getHttpFactory()
            ->createRequest();
    }

    /**
     * Создает HTTP-ответ к серверу.
     * @return Response
     */
    protected function createHttpResponse()
    {
        return $this->getHttpFactory()
            ->createResponse();
    }

    /**
     * Возвращает фабрику сущностей HTTP.
     * @throws RequiredDependencyException если фабрика не установлена
     * @return IHttpFactory
     */
    private function getHttpFactory()
    {
        if (!$this->traitHttpFactory) {
            throw new RequiredDependencyException(sprintf(
                'Http factory is not injected in class "%s".',
                get_class($this)
            ));
        }

        return $this->traitHttpFactory;
    }
}
