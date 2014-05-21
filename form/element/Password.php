<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\form\element;

/**
 * Элемент формы - Пароль(password).
 * @example <input type="password" />
 */
class Password extends BaseFormInput
{
    /**
     * Тип элемента.
     */
    const TYPE_NAME = 'password';

    /**
     * {@inheritdoc}
     */
    protected $inputType = self::TYPE_NAME;
    /**
     * @var string $password пароль
     */
    protected $password;

    /**
     * {@inheritdoc}
     */
    public function setValue($value)
    {
        $this->password = $this->filter($value);
        $this->getDataAdapter()->setData($this, $this->password);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    protected function validate($value)
    {
        return parent::validate($this->password);
    }
}