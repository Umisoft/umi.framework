<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\templating\engine\php;

use umi\hmvc\dispatcher\IDispatcher;
use umi\hmvc\view\helper\IsAllowedHelper;
use umi\hmvc\view\helper\UrlHelper;

/**
 * Расширение для подключения помощников вида в PHP-шаблонах.
 */
class ViewPhpExtension implements IPhpExtension
{
    /**
     * @var string $widgetFunctionName имя функции для вызова виджета
     */
    public $widgetFunctionName = 'widget';
    /**
     * @var string $urlFunctionName имя функции для генерации URL
     */
    public $urlFunctionName = 'url';
    /**
     * @var string $isAllowedFunctionName имя функции для проверки прав
     */
    public $isAllowedFunctionName = 'isAllowed';

    /**
     * @var IDispatcher $dispatcher диспетчер
     */
    protected $dispatcher;

    /**
     * @var UrlHelper $urlHelper
     */
    private $urlHelper;
    /**
     * @var IsAllowedHelper $isAllowedHelper
     */
    private $isAllowedHelper;

    /**
     * Конструктор.
     * @param IDispatcher $dispatcher диспетчер
     */
    public function __construct(IDispatcher $dispatcher) {
        $this->dispatcher = $dispatcher;
    }

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
            $this->widgetFunctionName => $this->getWidgetHelper(),
            $this->urlFunctionName => $this->getUrlHelper(),
            $this->isAllowedFunctionName => $this->getIsAllowedHelper()
        ];
    }

    /**
     * Возвращает помощник вида для генерации URL.
     * @return callable
     */
    protected function getUrlHelper()
    {
        if (!$this->urlHelper) {
            $this->urlHelper = new UrlHelper($this->dispatcher);
        }
        return $this->urlHelper;
    }

    /**
     * Возвращает помощник вида для проверки прав.
     * @return callable
     */
    protected function getIsAllowedHelper()
    {
        if (!$this->isAllowedHelper) {
            $this->isAllowedHelper = new IsAllowedHelper($this->dispatcher);
        }
        return $this->isAllowedHelper;
    }

    /**
     * Возвращает помощник вида для вызова виджетов
     * @return callable
     */
    protected function getWidgetHelper()
    {
        return function($widgetPath, array $args = []) {
            return $this->dispatcher->executeWidget($widgetPath, $args);
        };
    }

}
 