<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\form\element;

use umi\form\FormEntityView;
use umi\session\ISessionAware;
use umi\session\TSessionAware;

/**
 * Элемент формы - Cross-site request forgery токен.
 * @example <input type="hidden" value="ca969a1bc97732d97b1e88ce8396c216" />
 */
class CSRF extends Hidden implements ISessionAware
{
    use TSessionAware;

    /**
     * Тип элемента.
     */
    const TYPE_NAME = 'csrf';

    /**
     * {@inheritdoc}
     */
    protected $type = 'csrf';

    /**
     * {@inheritdoc}
     */
    protected function extendView(FormEntityView $view)
    {
        parent::extendView($view);

        $view->attributes['value'] = $this->getValidToken();
    }

    /**
     * {@inheritdoc}
     */
    protected function validate($value)
    {
        $isValid = $value && ($value === $this->getValidToken());

        if (!$isValid) {
            $this->messages = ['Invalid csrf token.'];
        }

        return $isValid;
    }

    /**
     * Возвращает валидный токен.
     * @return string
     */
    protected function getValidToken()
    {
        $key = $this->getSessionKey();

        if (!$token = $this->getSessionVar($key)) {
            $token = sha1('csrf:' . time() . rand());
            $this->setSessionVar($key, $token);
        }

        return $token;
    }

    /**
     * Генерирует и возвращает уникальный ключ для хранения токена в сессии.
     * @return string
     */
    private function getSessionKey()
    {
        $names = $this->getName();

        $element = $this->getParent();
        while ($parent = $element->getParent()) {
            $names .= $parent->getName();
            $element = $parent;
        }

        return 't_' . md5($names);
    }
}