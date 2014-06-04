<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\hmvc\controller;

use umi\acl\IAclManager;
use umi\acl\IAclResource;
use umi\form\IForm;
use umi\hmvc\component\IComponent;
use umi\hmvc\dispatcher\IDispatchContext;
use umi\hmvc\exception\OutOfBoundsException;
use umi\hmvc\exception\RequiredDependencyException;
use umi\hmvc\view\IView;
use umi\hmvc\view\View;
use umi\http\IHttpAware;
use umi\http\Request;
use umi\http\Response;
use umi\http\THttpAware;

/**
 * Базовый класс контроллера.
 */
abstract class BaseController implements IController, IHttpAware
{
    use THttpAware;

    /**
     * @var string $name имя контроллера
     */
    protected $name;
    /**
     * @var IDispatchContext $context
     */
    private $context;
    /**
     * @var Request $request
     */
    private $request;

    /**
     * {@inheritdoc}
     */
    public function setContext(IDispatchContext $context)
    {
        $this->context = $context;

        return $this;
    }

    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @see IComponent::translate()
     */
    protected function translate($message, array $placeholders = [], $localeId = null)
    {
        return $this->getComponent()->translate($message, $placeholders, $localeId);
    }

    /**
     * Возвращает контекст вызова контроллера.
     * @throws RequiredDependencyException если контекст не был установлен
     * @return IDispatchContext
     */
    protected function getContext()
    {
        if (!$this->context) {
            throw new RequiredDependencyException(
                sprintf('Dispatch context is not injected in controller "%s".', get_class($this))
            );
        }
        return $this->context;
    }

    /**
     * Возвращает HTTP-запрос.
     * @throws RequiredDependencyException если запрос не был установлен
     * @return Request
     */
    protected function getRequest()
    {
        if (!$this->request) {
            throw new RequiredDependencyException(
                sprintf('HTTP request is not injected in controller "%s".', get_class($this))
            );
        }
        return $this->request;
    }

    /**
     * Возвращает компонент, которому принадлежит контроллер.
     * @return IComponent
     */
    protected function getComponent()
    {
        return $this->getContext()->getComponent();
    }

    /**
     * Возвращает переменную из параметров маршрутизации.
     * @param string $name имя параметра
     * @param mixed $default значение по умолчанию
     * @return mixed значение из GET
     */
    protected function getRouteVar($name, $default = null)
    {
        $routeParams = $this->getContext()->getRouteParams();

        return isset($routeParams[$name]) ? $routeParams[$name] : $default;
    }

    /**
     * Возвращает значение параметра из GET-параметров запроса.
     * @param string $name имя параметра
     * @param null $default значение по умолчанию
     * @return mixed
     */
    protected function getQueryVar($name, $default = null)
    {
        return $this->getRequest()->query->get($name, $default);
    }

    /**
     * Возвращает значения всех GET-параметров запроса.
     * @return array
     */
    protected function getAllQueryVars()
    {
        return $this->getRequest()->query->all();
    }

    /**
     * Возвращает значение параметра из POST-параметров запроса.
     * @param string $name имя параметра
     * @param null $default значение по умолчанию
     * @return mixed
     */
    protected function getPostVar($name, $default = null)
    {
        return $this->getRequest()->request->get($name, $default);
    }

    /**
     * Возвращает значения всех GET-параметров запроса.
     * @return array
     */
    protected function getAllPostVars()
    {
        return $this->getRequest()->request->all();
    }

    /**
     * Проверяет, что реальный метод запроса POST
     * @return bool
     */
    protected function isRequestMethodPost()
    {
        return $this->getRequest()->getRealMethod() == 'POST';
    }

    /**
     * Проверяет, что реальный метод запроса GET
     * @return bool
     */
    protected function isRequestMethodGet()
    {
        return $this->getRequest()->getRealMethod() == 'GET';
    }

    /**
     * Проверяет права текущего пользователя на выполнение операции над ресурсом
     * @param IAclResource|string $resource имя ресурса или ресурс
     * @param string $operationName имя операции
     * @return bool
     */
    protected function isAllowed($resource, $operationName = IAclManager::OPERATION_ALL)
    {
        return $this->getContext()->getDispatcher()->checkPermissions($this->getComponent(), $resource, $operationName);
    }

    /**
     * Выполняет редирект на указанный маршрут текущего компонента.
     * @param string $routeName имя маршрута
     * @param array $params параметры маршрута
     * @param bool $useQuery использовать ли GET-параметры HTTP-запроса при построении URL
     * @param int $code код ответа
     * @return Response
     */
    protected function redirectToRoute($routeName, array $params = [], $useQuery = false, $code = Response::HTTP_SEE_OTHER)
    {

        $baseUrl = $this->getContext()->getBaseUrl();

        $url = $baseUrl . $this->getComponent()->getRouter()->assemble($routeName, $params) ? : '/';

        if ($useQuery) {
            if($getParams = $this->getAllQueryVars()) {
                $url .= '?' . http_build_query($getParams);
            }
        }

        return $this->createRedirectResponse($url, $code);
    }

    /**
     * Создает результат работы контроллера.
     * @param string $content содержимое ответа
     * @param int $code код ответа
     * @return Response
     */
    protected function createResponse($content, $code = Response::HTTP_OK)
    {
        return $this->createHttpResponse()
            ->setStatusCode($code)
            ->setContent($content);
    }

    /**
     * Создает шаблонизируемый результат работы контроллера.
     * Этот ответ пройдет через View слой компонента.
     * @param string $templateName имя шаблона
     * @param array $variables переменные
     * @return Response
     */
    protected function createViewResponse($templateName, array $variables = [])
    {
        return $this->createResponse($this->createView($templateName, $variables));
    }

    /**
     * Создает View, требующий шаблонизации.
     * @param string $templateName имя шаблона
     * @param array $variables переменные
     * @return IView
     */
    protected function createView($templateName, array $variables = [])
    {
        return new View($this, $this->getContext(), $templateName, $variables);
    }

    /**
     * Устанавливает в ответ заголовок переадресации.
     * @param string $url URL для переадресации
     * @param int $code HTTP статус переадресации
     * @return Response
     */
    protected function createRedirectResponse($url, $code = Response::HTTP_SEE_OTHER)
    {
        $response = $this->createHttpResponse();
        $response
            ->setStatusCode($code)
            ->headers->set('Location', $url);

        return $response;
    }

    /**
     * Вызывает виджет.
     * @param string $widgetURI путь к виджету
     * @param array $params параметры вызова виджета
     * @return string|IView
     */
    protected function callWidget($widgetURI, array $params = [])
    {
        return $this->getContext()->getDispatcher()->executeWidget($widgetURI, $params);
    }

    /**
     * Возвращает форму компонента.
     * @param string $formName имя формы
     * @param array|object $object объект, с которым связана форма
     * @throws OutOfBoundsException если форма не существует
     * @return IForm
     */
    protected function getForm($formName, $object = null)
    {
        return $this->getComponent()->getForm($formName, $object);
    }
}
