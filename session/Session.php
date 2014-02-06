<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\session;

use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\HttpFoundation\Session\Session as SymfonySession;

/**
 * Сервис для работы с сессией.
 */
class Session extends SymfonySession implements ISession
{
    /**
     * {@inheritdoc}
     */
    public function hasBag($name)
    {
        try {
            $this->getBag($name);

            return true;
        } catch (\InvalidArgumentException $e) {

            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addAttributeBag($name)
    {
        $bag = new AttributeBag('_umi_' . $name);
        $bag->setName($name);
        $this->registerBag($bag);

        return $this;
    }
}
