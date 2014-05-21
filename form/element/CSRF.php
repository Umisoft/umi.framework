<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\form\element;

use umi\i18n\ILocalizable;
use umi\i18n\TLocalizable;
use umi\session\ISessionAware;
use umi\session\TSessionAware;

/**
 * Элемент формы - Cross-site request forgery токен.
 * @example <input type="hidden" value="ca969a1bc97732d97b1e88ce8396c216" />
 */
class CSRF extends Hidden implements ILocalizable, ISessionAware
{
    use TLocalizable;
    use TSessionAware;

    /**
     * Тип элемента.
     */
    const TYPE_NAME = 'csrf';
    /**
     * Пространство имен сессии для хранения данных о CSRF.
     */
    const SESSION_NAMESPACE = 'csrf_protection';

    /**
     * @var string $token CSRF токен
     */
    protected $token;
    /**
     * @var string $value значение токена из формы
     */
    protected $value;

    /**
     * {@inheritdoc}
     */
    public function setValue($value)
    {
        if (!$this->token) {
            $this->initToken();
        }

        $value = $this->filter($value);
        $this->value = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        if (!$this->token) {
            $this->initToken();
        }

        return $this->token;
    }

    /**
     * {@inheritdoc}
     */
    protected function validate($value)
    {
        return $this->token == $this->value;
    }

    /**
     * Восстанавливает значение токена из сессии,
     * либо генерирует новый токен.
     */
    protected function initToken()
    {
        $this->token = $this->getSessionVar('token');

        if (!$this->token) {
            $this->token = sha1('token:' . time() . rand());
            $this->setSessionVar('token', $this->token);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getSessionNamespacePath()
    {
        return self::SESSION_NAMESPACE;
    }
}