<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\validation;

use umi\validation\exception\RequiredDependencyException;

/**
 * Трейт для внедрения поддержки работы с валидаторами.
 */
trait TValidationAware
{
    /**
     * @var IValidatorFactory $traitValidatorFactory фабрика для создания валидаторов.
     */
    private $traitValidatorFactory;

    /**
     * @see IValidationAware::setValidatorFactory()
     */
    public function setValidatorFactory(IValidatorFactory $validatorFactory)
    {
        $this->traitValidatorFactory = $validatorFactory;
    }

    /**
     * Создает коллекцию валидаторов на основе массива.
     * @example ['regexp' => ['pattern' => '/[0-9]+/']]
     * @param array $config конфигурация валидаторов
     * @throws RequiredDependencyException если инструменты для валидации не установлены
     * @return IValidatorCollection
     */
    protected function createValidatorCollection(array $config = [])
    {
        return $this->getValidatorFactory()
            ->createValidatorCollection($config);
    }

    /**
     * Создает валидатор определенного типа. Устанавливет ему опции (если переданы).
     * @param string $type тип валидатора
     * @param array $options опции валидатора
     * @throws RequiredDependencyException если инструменты для валидации не установлены
     * @return IValidator созданный валидатор
     */
    protected function createValidator($type, array $options = [])
    {
        return $this->getValidatorFactory()
            ->createValidator($type, $options);
    }

    /**
     * Возвращает фабрику валидаторов.
     * @throws RequiredDependencyException если фабрика не была внедрена
     * @return IValidatorFactory
     */
    private function getValidatorFactory()
    {
        if (!$this->traitValidatorFactory) {
            throw new RequiredDependencyException(sprintf(
                'Validator factory is not injected in class "%s".',
                get_class($this)
            ));
        }

        return $this->traitValidatorFactory;
    }
}
