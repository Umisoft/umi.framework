<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\hmvc\dispatcher;

use Exception;
use SplDoublyLinkedList;
use SplStack;
use umi\acl\IAclManager;
use umi\acl\IAclResource;
use umi\authentication\IAuthenticationAware;
use umi\authentication\TAuthenticationAware;
use umi\hmvc\acl\ComponentRoleProvider;
use umi\hmvc\acl\IComponentRoleResolver;
use umi\hmvc\component\IComponent;
use umi\hmvc\controller\IController;
use umi\hmvc\exception\acl\ResourceAccessForbiddenException;
use umi\hmvc\exception\http\HttpNotFound;
use umi\hmvc\exception\RuntimeException;
use umi\hmvc\exception\UnexpectedValueException;
use umi\hmvc\IMvcEntityFactoryAware;
use umi\hmvc\widget\IWidget;
use umi\hmvc\TMvcEntityFactoryAware;
use umi\hmvc\view\IView;
use umi\http\Request;
use umi\http\Response;
use umi\i18n\ILocalizable;
use umi\i18n\TLocalizable;
use umi\route\result\IRouteResult;

/**
 * Диспетчер MVC-компонентов.
 */
class Dispatcher implements IDispatcher, ILocalizable, IMvcEntityFactoryAware, IAuthenticationAware
{

    use TLocalizable;
    use TMvcEntityFactoryAware;
    use TAuthenticationAware;

    /**
     * @var array $controllerViewRenderErrorInfo информация об исключение рендеринга
     */
    protected $controllerViewRenderErrorInfo = [];

    /**
     * @var Request $currentRequest обрабатываемый HTTP-запрос
     */
    private $currentRequest;
    /**
     * @var IComponent $initialComponent начальный компонент HTTP-запроса
     */
    private $initialComponent;
    /**
     * @var IDispatchContext $currentContext текущий контекст
     */
    private $currentContext;

    /**
     * {@inheritdoc}
     */
    public function setCurrentRequest(Request $request)
    {
        $this->currentRequest = $request;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentRequest()
    {
        if (!$this->currentRequest) {
            throw new RuntimeException(
                $this->translate('Current HTTP request is unknown.')
            );
        }
        return $this->currentRequest;
    }

    /**
     * {@inheritdoc}
     */
    public function setInitialComponent(IComponent $component)
    {
        $this->initialComponent = $component;
        $this->controllerViewRenderErrorInfo = [];
        $this->currentContext = null;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getInitialComponent()
    {
        if (!$this->initialComponent) {
            throw new RuntimeException(
                $this->translate('Initial component is unknown.')
            );
        }

        return $this->initialComponent;
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch($routePath = null, $baseUrl = '')
    {
        $callStack = $this->createCallStack();

        if (is_null($routePath)) {
            $routePath = $this->getCurrentRequest()->getPathInfo();
        }
        $routePath = urldecode($routePath);

        try {
            $response = $this->processRequest($this->getInitialComponent(), $routePath, $callStack, $baseUrl);
        } catch (Exception $e) {
            return $this->processError($e, $callStack);
        }

        $content = (string) $response->getContent();

        if ($this->controllerViewRenderErrorInfo) {
            /**
             * @var Exception $e
             * @var IDispatchContext $failureContext
             */
            list ($e, $failureContext) = $this->controllerViewRenderErrorInfo;
            $this->controllerViewRenderErrorInfo = [];

            return $this->processError($e, $failureContext->getCallStack());
        }

        return $response->setContent($content);
    }

    /**
     * {@inheritdoc}
     */
    public function getComponentByPath($componentPath)
    {
        $componentPathParts = explode(IComponent::PATH_SEPARATOR, $componentPath);
        $component = $this->getInitialComponent();

        if ($component->getName() != array_shift($componentPathParts)) {
            throw new RuntimeException(
                $this->translate(
                    'Cannot resolve component path "{path}".',
                    ['path' => $componentPath]
                )
            );
        }

        while ($componentName = array_shift($componentPathParts)) {
            $component = $component->getChildComponent($componentName);
        }

        return $component;
    }

    /**
     * {@inheritdoc}
     */
    public function reportViewRenderError(Exception $e, IDispatchContext $failureContext, $viewOwner)
    {
        if ($viewOwner instanceof IWidget) {
            if ($viewOwner->getName() === IComponent::ERROR_WIDGET) {
                return $e->getMessage();
            }
            return (string) $this->processWidgetError($e, $failureContext);
        }

        $this->controllerViewRenderErrorInfo = [$e, $failureContext];

        return $e->getMessage();
    }

    /**
     * {@inheritdoc}
     */
    public function executeWidget($widgetUri, array $params = [])
    {
        list ($component, $callStack, $componentURI) = $this->resolveWidgetContext($widgetUri);

        try {
            $widget = $this->dispatchWidget($component, $widgetUri, $params, $callStack, $componentURI);

            return $this->invokeWidget($widget);

        } catch (Exception $e) {

            $context = $this->createDispatchContext($component);
            $context->setCallStack(clone $callStack);

            return $this->processWidgetError($e, $context);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function switchCurrentContext(IDispatchContext $context)
    {
        $previousContext = $this->currentContext;
        $this->currentContext = $context;

        return $previousContext;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentContext()
    {
        if (!$this->currentContext) {
            throw new RuntimeException(
                'Current dispatch context is unknown.'
            );
        }

        return $this->currentContext;
    }

    /**
     * {@inheritdoc}
     */
    public function checkPermissions(IComponent $component, $resource, $operationName = IAclManager::OPERATION_ALL)
    {
        $authManager = $this->getDefaultAuthManager();
        if (!$authManager->isAuthenticated()) {
            return false;
        }

        $identity = $authManager->getStorage()->getIdentity();

        if (!$identity instanceof IComponentRoleResolver) {
            return false;
        }
        $roleProvider = new ComponentRoleProvider($component, $identity);

        $aclManager = $component->getAclManager();
        return $aclManager->isAllowed($roleProvider, $resource, $operationName);
    }

    /**
     * Формирует результат виджета с учетом произошедшей исключительной ситуации.
     * @param Exception $e
     * @param IDispatchContext $context контекст вызова виджета
     * @throws Exception если исключительная ситуация не была обработана
     * @return string
     */
    protected function processWidgetError(Exception $e, IDispatchContext $context)
    {
        $callStack = $context->getCallStack();
        /**
         * @var IDispatchContext $context
         */
        foreach ($callStack as $context) {

            $component = $context->getComponent();
            if (!$component->hasWidget(IComponent::ERROR_WIDGET)) {
                continue;
            }

            $errorWidget = $component->getWidget(
                IComponent::ERROR_WIDGET,
                ['exception' => $e]
            );

            $context = $this->createDispatchContext($component);
            $context->setCallStack(clone $callStack);

            $errorWidget->setContext($context);

            try {
                return $this->invokeWidget($errorWidget);
            } catch (Exception $e) { }
        }

        return $e->getMessage();
    }

    /**
     * Диспетчеризирует вызов виджета.
     * @param IComponent $component компонент для поиска
     * @param string $widgetUri путь виджета относительно компонента
     * @param array $params параметры вызова виджета
     * @param SplStack $callStack стек вызова компонентов
     * @param string $matchedWidgetUri известная часть пути вызова виджета
     * @throws ResourceAccessForbiddenException при отстуствии прав на вызов виджета
     * @return IWidget
     */
    protected function dispatchWidget(IComponent $component, $widgetUri, array $params, SplStack $callStack, $matchedWidgetUri = '')
    {
        $routeResult = $component->getRouter()->match($widgetUri, $matchedWidgetUri);
        $routeMatches = $routeResult->getMatches();

        $context = $this->createDispatchContext($component);
        $callStack->push($context);

        $context
            ->setRouteParams($routeMatches)
            ->setBaseUrl($matchedWidgetUri)
            ->setCallStack(clone $callStack);

        if (isset($routeMatches[IComponent::MATCH_COMPONENT]) && $component->hasChildComponent($routeMatches[IComponent::MATCH_COMPONENT])) {

            $childComponent = $component->getChildComponent($routeMatches[IComponent::MATCH_COMPONENT]);
            $matchedWidgetUri .= $routeResult->getMatchedUrl();

            return $this->dispatchWidget($childComponent, $routeResult->getUnmatchedUrl(), $params, $callStack, $matchedWidgetUri);

        } else {
            $widget = $component->getWidget(ltrim($widgetUri, self::WIDGET_URI_SEPARATOR), $params)
                ->setContext($context);

            /**
             * @var IWidget|IAclResource $widget
             */
            if ($widget instanceof IACLResource && !$this->checkPermissions($component, $widget)) {
                throw new ResourceAccessForbiddenException(
                    $widget,
                    $this->translate(
                        'Cannot execute widget "{name}" for component "{path}". Access denied.',
                        [
                            'name' => $widget->getName(),
                            'path' => $component->getPath()
                        ]
                    )
                );
            }

            return $widget;
        }
    }

    /**
     * Вызывает виджет.
     * @param IWidget $widget
     * @throws UnexpectedValueException если виджет вернул неверный результат
     * @return IView|string
     */
    protected function invokeWidget(IWidget $widget)
    {
        $widgetResult = $widget();

        if (!$widgetResult instanceof IView && !is_string($widgetResult)) {
            throw new UnexpectedValueException($this->translate(
                'Widget "{widget}" returns unexpected value. String or instance of IView expected.',
                ['widget' => get_class($widget)]
            ));
        }

        return $widgetResult;
    }

    /**
     * Возвращает результат работы компонента.
     * @param IComponent $component
     * @param string $routePath запрос для маршрутизации
     * @param SplStack $callStack
     * @param string $matchedRoutePath обработанная часть начального маршрута
     * @throws HttpNotFound если невозможно сформировать результат.
     * @return Response
     */
    protected function processRequest(IComponent $component, $routePath, SplStack $callStack, $matchedRoutePath = '')
    {
        $routeResult = $component->getRouter()->match($routePath, $matchedRoutePath);
        $routeMatches = $routeResult->getMatches();

        $context = $this->createDispatchContext($component);
        $callStack->push($context);

        $context
            ->setRouteParams($routeMatches)
            ->setBaseUrl($matchedRoutePath)
            ->setCallStack(clone $callStack);

        $response = $component->onDispatchRequest($context, $this->getCurrentRequest());
        if ($response instanceof Response) {
            if ($response->isRedirect()) {

                return $response;
            }

            return $this->processResponse($response, $callStack);
        }

        if (isset($routeMatches[IComponent::MATCH_COMPONENT])) {

            return $this->processChildComponentRequest($component, $routeResult, $callStack, $matchedRoutePath);

        } elseif (isset($routeMatches[IComponent::MATCH_CONTROLLER]) && !$routeResult->getUnmatchedUrl()) {

            return $this->processControllerRequest($component, $context, $callStack, $routeMatches);

        } else {
            throw new HttpNotFound(
                $this->translate(
                    'URL not found by router.'
                )
            );
        }
    }

    /**
     * Формирует HTTP-ответ с учетом произошедшей исключительной ситуации.
     * @param Exception $e произошедшая исключительная ситуация
     * @param SplStack $callStack
     * @throws Exception если не удалось обработать исключительную ситуацию
     * @return Response
     */
    protected function processError(Exception $e, SplStack $callStack)
    {
        /**
         * @var IDispatchContext $context
         */
        foreach ($callStack as $context) {

            $component = $context->getComponent();
            if (!$component->hasController(IComponent::ERROR_CONTROLLER)) {
                continue;
            }

            $errorController = $component->getController(IComponent::ERROR_CONTROLLER, [$e])
                ->setContext($context)
                ->setRequest($this->getCurrentRequest());


            try {
                $errorResponse = $this->invokeController($errorController);
                $layoutResponse = $this->processResponse($errorResponse, $callStack);
            } catch (Exception $e) {
                continue;
            }
            $content = (string) $layoutResponse->getContent();

            if ($this->controllerViewRenderErrorInfo) {
                list ($renderException) = $this->controllerViewRenderErrorInfo;
                throw $renderException;
            }

            return $layoutResponse->setContent($content);
        }

        throw $e;
    }

    /**
     * Вызывает контроллер компонента.
     * @param IController $controller контроллер
     * @throws UnexpectedValueException если контроллер вернул неожиданный результат
     * @return Response
     */
    protected function invokeController(IController $controller)
    {
        $componentResponse = $controller();

        if (!$componentResponse instanceof Response) {
            throw new UnexpectedValueException($this->translate(
                'Controller "{controller}" returns unexpected value. Instance of Response expected.',
                ['controller' => get_class($controller)]
            ));
        }

        return $componentResponse;
    }

    /**
     * Обрабатывает результат запроса по всему стеку вызова компонентов.
     * @param Response $response
     * @param SplStack $callStack
     * @return Response
     */
    protected function processResponse(Response $response, SplStack $callStack)
    {
        /**
         * @var IDispatchContext $context
         */
        foreach ($callStack as $context) {

            $component = $context->getComponent();

            if (!$response->getIsCompleted()) {

                if ($component->hasController(IComponent::LAYOUT_CONTROLLER)) {

                    $layoutController = $component->getController(IComponent::LAYOUT_CONTROLLER, [$response])
                        ->setContext($context)
                        ->setRequest($this->getCurrentRequest());
                    $response = $this->invokeController($layoutController);
                }

            }

            $response = $component->onDispatchResponse($context, $response);
        }

        return $response;
    }

    /**
     * Создает контекст вызова компонента.
     * @param IComponent $component
     * @return IDispatchContext
     */
    protected function createDispatchContext(IComponent $component)
    {
        return new DispatchContext($component, $this);
    }

    /**
     * Возвращает информацию о контексте вызова виджета.
     * @param string $widgetUri путь виджета
     * @throws RuntimeException если контекст не существует
     * @return array
     */
    protected function resolveWidgetContext(&$widgetUri)
    {
        if (strpos($widgetUri, self::WIDGET_URI_SEPARATOR) !== 0) {
            if (!$this->currentContext) {
                throw new RuntimeException(
                    $this->translate(
                        'Context for executing widget "{widget}" is unknown.',
                        ['widget' => $widgetUri]
                    )
                );
            }

            $widgetUri = self::WIDGET_URI_SEPARATOR . $widgetUri;

            return [
                $this->currentContext->getComponent(),
                clone $this->currentContext->getCallStack(),
                $this->currentContext->getBaseUrl()
            ];
        }

        return [
            $this->getInitialComponent(),
            $this->createCallStack(),
            ''
        ];
    }

    /**
     * Возвращает результат работы дочернего компонента.
     * @param IComponent $component
     * @param IRouteResult $routeResult
     * @param SplStack $callStack
     * @param string $matchedRoutePath
     * @throws HttpNotFound если дочерний компонент не существует
     * @throws ResourceAccessForbiddenException если доступ к дочернему компоненту не разрешен
     * @return Response
     */
    private function processChildComponentRequest(IComponent $component, IRouteResult $routeResult, SplStack $callStack, $matchedRoutePath)
    {
        $routeMatches = $routeResult->getMatches();
        if (!$component->hasChildComponent($routeMatches[IComponent::MATCH_COMPONENT])) {

            throw new HttpNotFound(
                $this->translate(
                    'Child component "{name}" not found in component "{componentPath}".',
                    ['name' => $routeMatches[IComponent::MATCH_COMPONENT], 'componentPath' => $component->getPath()]
                )
            );
        }

        /**
         * @var IComponent|IACLResource $childComponent
         */
        $childComponent = $component->getChildComponent($routeMatches[IComponent::MATCH_COMPONENT]);

        if ($childComponent instanceof IACLResource && !$this->checkPermissions($component, $childComponent)) {

            throw new ResourceAccessForbiddenException(
                $childComponent,
                $this->translate(
                    'Cannot execute component "{path}". Access denied.',
                    ['path' => $childComponent->getPath()]
                )
            );
        }

        $matchedRoutePath .= $routeResult->getMatchedUrl();

        return $this->processRequest($childComponent, $routeResult->getUnmatchedUrl(), $callStack, $matchedRoutePath);
    }

    /**
     * Возвращает результат работы контроллера компонента.
     * @param IComponent $component
     * @param IDispatchContext $context
     * @param SplStack $callStack
     * @param array $routeMatches
     * @throws ResourceAccessForbiddenException
     * @throws HttpNotFound
     * @return Response
     */
    private function processControllerRequest(IComponent $component, IDispatchContext $context, SplStack $callStack, array $routeMatches)
    {
        if (!$component->hasController($routeMatches[IComponent::MATCH_CONTROLLER])) {
            throw new HttpNotFound(
                $this->translate(
                    'Controller "{name}" not found.',
                    ['name' => $routeMatches[IComponent::MATCH_CONTROLLER]]
                )
            );
        }

        /**
         * @var IController|IACLResource $controller
         */
        $controller = $component->getController($routeMatches[IComponent::MATCH_CONTROLLER])
            ->setContext($context)
            ->setRequest($this->getCurrentRequest());

        if ($controller instanceof IACLResource && !$this->checkPermissions($component, $controller)) {
            throw new ResourceAccessForbiddenException(
                $controller,
                $this->translate(
                    'Cannot execute controller "{name}" for component "{path}". Access denied.',
                    [
                        'name' => $controller->getName(),
                        'path' => $component->getPath()
                    ]
                )
            );
        }

        $componentResponse = $this->invokeController($controller);

        return $this->processResponse($componentResponse, $callStack);
    }

    /**
     * Создает пустой стек вызова.
     * @return SplStack
     */
    private function createCallStack()
    {
        $callStack = new SplStack();
        $callStack->setIteratorMode(SplDoublyLinkedList::IT_MODE_LIFO | SplDoublyLinkedList::IT_MODE_DELETE);

        return $callStack;
    }


}
 