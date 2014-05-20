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
    use TLocalizable {
        TLocalizable::getI18nDictionaryNames as getI18nDictionaryNamesInternal;
    }

    /**
     * @var string $type тип валидатора
     */
    protected $type;
    /**
     * @var array $options опции валидатора
     */
    protected $options = [];
    /**
     * @var string $message сообщение об ошибке валидации
     */
    protected $message;
    /**
     * @var string $defaultErrorLabel текст для генерации ошибки
     */
    protected $defaultErrorLabel = '';

    /**
     * Конструктор.
     * @param string $type тип валидатора
     * @param array $options опции валидатора
     */
    public function __construct($type, array $options = [])
    {
        $this->type = $type;
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return $this->options;
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

    /**
     * {@inheritdoc}
     */
    protected function getI18nDictionaryNames()
    {
        $dictionaries = [];
        if (isset($this->options['dictionaries'])) {
            $dictionaries = $this->options['dictionaries'];
        };

        return array_merge($dictionaries, $this->getI18nDictionaryNamesInternal());
    }
}
 