<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\authentication\provider;

use umi\http\Request;
use umi\i18n\ILocalizable;
use umi\i18n\TLocalizable;

/**
 * Провайдер HTTP авторизации.
 */
class HttpProvider implements IAuthProvider, ILocalizable
{

    use TLocalizable;

    /**
     * @var Request $request HTTP запрос
     */
    protected $request = null;

    /**
     * Конструктор.
     * @param Request $request HTTP запрос
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function getCredentials()
    {
        $username = $this->request->getUser();
        $password = $this->request->getPassword();

        if ($username && $password) {
            return [$username, $password];
        } else {
            return false;
        }
    }
}