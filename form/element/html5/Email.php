<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\form\element\html5;

use umi\form\element\BaseFormInput;
use umi\validation\IValidatorFactory;

/**
 * HTML5 элемент формы - Email (email).
 * @example <input type="email" />
 */
class Email extends BaseFormInput
{
    /**
     * Тип элемента.
     */
    const TYPE_NAME = 'email';

    /**
     * {@inheritdoc}
     */
    protected $type = 'email';
    /**
     * {@inheritdoc}
     */
    protected $inputType = 'email';

    /**
     * {@inheritdoc}
     */
    public function getValidatorsConfig()
    {
        $config = parent::getValidatorsConfig();
        $config[IValidatorFactory::TYPE_EMAIL] = [];

        return $config;
    }
}