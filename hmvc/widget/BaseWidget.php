<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\hmvc\widget;

use umi\hmvc\component\IComponent;
use umi\hmvc\dispatcher\IDispatchContext;
use umi\hmvc\exception\RequiredDependencyException;
use umi\hmvc\view\IView;
use umi\hmvc\view\View;
use umi\i18n\ILocalizable;
use umi\i18n\TLocalizable;

/**
 * Базовая реализация виджета компонента.
 */
abstract class BaseWidget implements IWidget, ILocalizable
{
    use TLocalizable;

    /**
     * @var string $name имя виджета
     */
    protected $name;
    /**
     * @var IDispatchContext $context контекст вызова виджета
     */
    private $context;

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
     * {@inheritdoc}
     */
    public function setContext(IDispatchContext $context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function getI18nDictionaryNames()
    {
        $pathParts = explode(IComponent::PATH_SEPARATOR, $this->getComponent()->getPath());

        $dictionaries = [];
        for ($i = count($pathParts); $i > 0; $i--) {
            $dictionaries[] = implode('.', array_slice($pathParts, 0, $i));
        }

        return $dictionaries;
    }

    /**
     * Возвращает контекст вызова виджета.
     * @throws RequiredDependencyException если контекст не был установлен
     * @return IDispatchContext
     */
    protected function getContext()
    {
        if (!$this->context) {
            throw new RequiredDependencyException(
                sprintf('Context is not injected in widget "%s".', get_class($this))
            );
        }

        return $this->context;
    }

    /**
     * Возвращает компонент, которому принадлежит контроллер.
     * @throws RequiredDependencyException если контроллер не был установлен
     * @return IComponent
     */
    protected function getComponent()
    {
        return $this->getContext()->getComponent();
    }

    /**
     * Создает результат работы виджета, требующий шаблонизации.
     * @param string $templateName имя шаблона
     * @param array $variables переменные
     * @return IView
     */
    protected function createResult($templateName, array $variables)
    {
        return new View($this, $this->getContext(), $templateName, $variables);
    }


}

 