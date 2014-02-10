<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\hmvc\controller;

use umi\hmvc\dispatcher\IDispatchContext;
use umi\http\Request;
use umi\http\Response;

/**
 * Интерфейс контроллера.
 */
interface IController
{
    /**
     * Вызывает контроллер.
     * @return Response
     */
    public function __invoke();

    /**
     * Устанавливает контекст вызова контроллера.
     * @param IDispatchContext $context
     * @return self
     */
    public function setContext(IDispatchContext $context);

    /**
     * Устанавливает HTTP-запрос.
     * @param Request $request
     * @return self
     */
    public function setRequest(Request $request);

    /**
     * Устанавливает имя контроллера.
     * @param string $name
     * @return self
     */
    public function setName($name);

    /**
     * Возвращает имя контроллера.
     * @return string
     */
    public function getName();

}
