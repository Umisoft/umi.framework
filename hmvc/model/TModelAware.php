<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\hmvc\model;

use umi\toolkit\exception\RequiredDependencyException;

/**
 * Трейт для внедрения поддержки работы с моделями.
 */
trait TModelAware
{
    /**
     * @var IModelFactory $traitModelFactory фабрика моделей
     */
    private $traitModelFactory;

    /**
     * @see IModelAware::setModelFactory()
     */
    public function setModelFactory(IModelFactory $factory)
    {
        $this->traitModelFactory = $factory;
    }

    /**
     * Создает новую модель по символическому имени.
     * @param string $name
     * @return IModel|object
     */
    protected function createModelByName($name)
    {
        return $this->getModelFactory()
            ->createByName($name);
    }

    /**
     * Создает новую модель по имени класса.
     * @param string $class класс
     * @return IModel|object
     */
    protected function createModelByClass($class)
    {
        return $this->getModelFactory()
            ->createByClass($class);
    }

    /**
     * Возвращает фабрику моделей.
     * @throws RequiredDependencyException если фабрика не внедрена
     * @return IModelFactory фабрика
     */
    private function getModelFactory()
    {
        if (!$this->traitModelFactory) {
            throw new RequiredDependencyException(sprintf(
                'Model factory is not injected in class "%s".',
                get_class($this)
            ));
        }

        return $this->traitModelFactory;
    }
}
