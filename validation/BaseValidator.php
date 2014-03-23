<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\validation;

use umi\i18n\ILocalizable;
use umi\i18n\TLocalizable;

/**
 * Базовый класс валидатора.
 */
abstract class BaseValidator implements ILocalizable, IValidator
{
    use TLocalizable;

    /**
     * @var array $options опции валидатора
     */
    protected $options = [];
    /**
     * @var array $messages сообщение об ошибке валидации
     */
    protected $message;
    /**
     * @var string $defaultErrorLabel текст для генерации ошибки
     */
    protected $defaultErrorLabel = '';

    /**
     * Конструктор.
     * @param array $options опции валидатора
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function getErrorLabel()
    {
        return empty($this->options['errorLabel']) ? $this->defaultErrorLabel : $this->options['errorLabel'];
    }

    /**
     * {@inheritdoc}
     */
    public function getMessage()
    {
        return $this->message;
    }
}
 