<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\hmvc\exception\http;

use umi\http\Response;

/**
 * Исключение для неподдерживаемого ресурсом HTTP-метода.
 */
class HttpMethodNotAllowed extends HttpException
{
    /**
     * @var array $allowedMethods
     */
    protected $allowedMethods;

    /**
     * Конструктор.
     * @param string $message текст исключения
     * @param array $allowedMethods список поддерживаемых методов
     * @param \Exception $previous предыдущее исключение
     */
    public function __construct($message, array $allowedMethods = [], \Exception $previous = null)
    {
        parent::__construct(Response::HTTP_METHOD_NOT_ALLOWED, $message, $previous);
        $this->allowedMethods = $allowedMethods;
    }

    /**
     * Возвращает список поддерживаемых ресурсом HTTP-методов
     * @return array
     */
    public function getAllowedMethods()
    {
        return $this->allowedMethods;
    }
}