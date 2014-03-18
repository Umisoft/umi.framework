<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */
namespace umi\messages\exception;

use Exception;

/**
 * Исключение, возникающее при провале отправки письма хотя бы одному адресату.
 */
class FailedRecipientsException extends \RuntimeException
{
    /**
     * @var array $addresses
     */
    private $addresses;

    /**
     * Конструктор исключения
     * @param string $message Сообщение об ошибке
     * @param array $addresses Список адресов, на которые не удалось отправить почту
     * @param int $code Код ошибки
     * @param Exception $previous Предыдущее исключение
     */
    public function __construct($message, array $addresses, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->addresses = $addresses;
    }

    /**
     * Вернет список адресов, на которые не удалось отправить почту
     * @return array
     */
    public function getAddresses()
    {
        return $this->addresses;
    }
}
