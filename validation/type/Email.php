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

/**
 * Валидатор E-mail.
 */
class Email extends BaseValidator
{

    /**
     * {@inheritdoc}
     */
    protected $defaultErrorLabel = 'Wrong email format.';

    /**
     * {@inheritdoc}
     */
    public function isValid($value)
    {
        $this->message = null;

        if ($value !== null && filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            $this->message = $this->translate($this->getErrorLabel());

            return false;
        }

        return true;
    }
}