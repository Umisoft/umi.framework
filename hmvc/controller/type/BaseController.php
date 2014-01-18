<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\hmvc\controller\type;

use umi\hmvc\component\IComponent;
use umi\hmvc\component\response\IHTTPComponentResponse;
use umi\hmvc\component\response\IComponentResponseFactory;
use umi\hmvc\component\response\model\DisplayModel;
use umi\hmvc\controller\IController;
use umi\hmvc\exception\RequiredDependencyException;
use umi\i18n\ILocalizable;
use umi\i18n\TLocalizable;

/**
 * Абстрактный базовый класс контроллера.
 * Реализует helper методы для контроллеров.
 */
abstract class BaseController implements IController, ILocalizable
{
    use TLocalizable;

    /**
     * @var IComponent $component компонент, которому принадлежит контроллер
     */
    private $component;
    /**
     * @var IComponentResponseFactory $responseFactory
     */
    private $responseFactory;

    /**
     * {@inheritdoc}
     */
    public function setComponent(IComponent $component)
    {
        $this->component = $component;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setComponentResponseFactory(IComponentResponseFactory $factory)
    {
        $this->responseFactory = $factory;
    }

    /**
     * Возвращает компонент, которому принадлежит контроллер.
     * @throws RequiredDependencyException если контроллер не был установлен
     * @return IComponent
     */
    protected function getComponent()
    {
        if (!$this->component) {
            throw new RequiredDependencyException(
                sprintf('Component is not injected in controller "%s".', __CLASS__)
            );
        }
        return $this->component;
    }

    /**
     * Создает HTTP ответ компонента.
     * @param string $content содержимое ответа
     * @param int $code код ответа
     * @return IHTTPComponentResponse
     */
    protected function createPlainResponse($content, $code = 200)
    {
        return $this->getComponentResponseFactory()
            ->createComponentResponse()
            ->setCode($code)
            ->setContent($content);
    }

    /**
     * Создает HTTP ответ компонента с содержимым, требующим отображения.

     * Этот ответ пройдет через View слой компонента.

     * @param string $template имя шаблона
     * @param array $variables переменные
     * @return IHTTPComponentResponse
     */
    protected function createDisplayResponse($template, array $variables)
    {
        return $this->getComponentResponseFactory()
            ->createComponentResponse()
                ->setContent(
                    new DisplayModel($template, $variables)
                );
    }

    /**
     * Устанавливает в ответ заголовок переадресации.
     * @param string $url URL для переадресации
     * @param int $code HTTP статус переадресации
     * @return IHTTPComponentResponse HTTP ответ
     */
    protected function createRedirectResponse($url, $code = 301)
    {
        $response = $this->getComponentResponseFactory()
            ->createComponentResponse();

        $response->setCode($code)
                 ->getHeaders()
                    ->setHeader('Location', $url);

        return $response;
    }

    /**
     * Возвращает фабрику для HTTP ответов компонента.
     * @return IComponentResponseFactory
     * @throws RequiredDependencyException если фабрика не была внедрена
     */
    private function getComponentResponseFactory()
    {
        if (!$this->responseFactory) {
            throw new RequiredDependencyException(
                sprintf('Component response factory is not injected in controller "%s".', __CLASS__)
            );
        }

        return $this->responseFactory;
    }
}
