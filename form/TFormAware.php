<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\form;

use umi\form\exception\RequiredDependencyException;

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
     * @throws RequiredDependencyException если фабрика элементов формы не установлена
     * @return IForm
     */
    protected function createForm(array $config)
    {
        if (!$this->traitFormEntityFactory) {
            throw new RequiredDependencyException(sprintf(
                'Form entity factory is not injected in class "%s".',
                get_class($this)
            ));
        }

        return $this->traitFormEntityFactory->createForm($config);
    }
}
