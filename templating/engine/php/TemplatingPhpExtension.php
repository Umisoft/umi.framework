<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\templating\engine\php;

use umi\templating\helper\pagination\PaginationHelper;
use umi\templating\helper\TranslationHelper;

/**
 * Расширение для подключения помощников шаблонов в PHP-шаблонах.
 */
class TemplatingPhpExtension implements IPhpExtension
{
    /**
     * @var string $paginationFunctionName имя функции для генерации постраничной навигации
     */
    public $paginationFunctionName = 'pagination';
    /**
     * @var string $translateFunctionName имя функции для перевода
     */
    public $translateFunctionName = 'translate';

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return __CLASS__;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            $this->paginationFunctionName => $this->getPaginationHelper(),
            $this->translateFunctionName => [$this->getTranslationHelper(), 'translate']
        ];
    }

    /**
     * Возвращает помощник шаблонов для вывода постраничной навигации.
     * @return callable
     */
    protected function getPaginationHelper()
    {
        return function () {
            static $helper;

            if (!$helper) {
                $helper = new PaginationHelper();
            }

            return $helper;
        };
    }

    /**
     * Возвращает помощник шаблонов для локализации.
     * @return TranslationHelper
     */
    protected function getTranslationHelper()
    {
        static $helper;

        if (!$helper) {
            $helper = new TranslationHelper();
        }

        return $helper;

    }

}
 