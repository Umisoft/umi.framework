<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\form;

use umi\form\exception\OutOfBoundsException;
use umi\form\exception\RequiredDependencyException;
use umi\form\exception\RuntimeException;

/**
 * Трейт для поддержки работы с формами.
 */
trait TFormAware
{
    /**
     * @var IEntityFactory $traitFormEntityFactory фабрика элементов формы
     */
    private $traitFormEntityFactory;

    /**
     * @see IFormAware::setFormEntityFactory()
     */
    public function setFormEntityFactory(IEntityFactory $formEntityFactory)
    {
        $this->traitFormEntityFactory = $formEntityFactory;
    }

    /**
     * Создает форму на основе конфига.
     * @param array $config конфигурация
     * @param array|object $object объект, с которым связана форма
     * @return IForm
     */
    protected function createForm(array $config, $object = null)
    {
        return $this->getFormEntityFactory()->createForm($config, $object);
    }

    /**
     * Создает элемент формы. Это может быть как просто элемент,
     * так и набор элементов.
     * @param string $name имя элемента
     * @param array $config конфигурация элемента, включая аттрибуты и опции
     * @throws RuntimeException если тип элемента не определен
     * @throws OutOfBoundsException если тип элемента не поддерживается
     * @return IFormEntity
     */
    protected function createFormEntity($name, array $config)
    {
        return $this->getFormEntityFactory()->createFormEntity($name, $config);
    }

    /**
     * Возвращает фабрику сущностей формы
     * @throws RequiredDependencyException если фабрика элементов формы не установлена
     * @return IEntityFactory
     */
    private function getFormEntityFactory()
    {
        if (!$this->traitFormEntityFactory) {
            throw new RequiredDependencyException(sprintf(
                'Form entity factory is not injected in class "%s".',
                get_class($this)
            ));
        }

        return $this->traitFormEntityFactory;
    }

}
