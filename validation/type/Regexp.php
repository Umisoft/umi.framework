<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\validation\type;

use umi\validation\BaseValidator;
use umi\validation\exception\RuntimeException;

/**
 * Валидатор по регулярному выражению.
 */
class Regexp extends BaseValidator
{
    /**
     * {@inheritdoc}
     */
    protected $defaultErrorLabel = 'String does not meet regular expression.';

    /**
     * {@inheritdoc}
     */
    public function isValid($value)
    {
        if (empty($this->options['pattern'])) {
            throw new RuntimeException($this->translate(
                'No regular expression pattern.'
            ));
        }

        $pattern = $this->options['pattern'];

        $this->message = null;

        $valid = filter_var($value, FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => $pattern]]) !== false;
        if (!$valid) {
            $this->message = $this->translate($this->getErrorLabel());
        }

        return $valid;
    }
}