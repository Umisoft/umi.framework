<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\http;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Реализация HTTP ответа сервера.
 */
class Response extends SymfonyResponse
{
    /**
     * @var bool $isCompleted статус доступности для обработки
     */
    protected $isCompleted = false;

    /**
     * {@inheritdoc}
     */
    public function setContent($content)
    {
        if (null !== $content && !is_string($content) && !is_numeric($content) && !is_callable(array($content, '__toString'))) {
            throw new \UnexpectedValueException(sprintf('The Response content must be a string or object implementing __toString(), "%s" given.', gettype($content)));
        }

        $this->content = $content;

        return $this;
    }

    /**
     * Помечает HTTP-ответ как полностью сформированный.
     * @return self
     */
    public function setIsCompleted()
    {
        $this->isCompleted = true;

        return $this;
    }

    /**
     * Проверяет, является ли HTTP-ответ полностью сформированным.
     * @return bool
     */
    public function getIsCompleted()
    {
        return $this->isCompleted;
    }
}
