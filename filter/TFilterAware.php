<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\filter;

use umi\filter\exception\RequiredDependencyException;

/**
 * Трейт для компонентов, поддерживающих фильтрацию.
 */
trait TFilterAware
{
    /**
     * @var IFilterFactory $traitFilterFactory фабрика для создания фильтров
     */
    private $traitFilterFactory;

    /**
     * @see IFilterAware::setFilterFactory()
     */
    public function setFilterFactory(IFilterFactory $filterFactory)
    {
        $this->traitFilterFactory = $filterFactory;
    }

    /**
     * Создает коллекцию фильтров на основе массива.
     * @example ['null' => []]
     * @param array $config конфигурация фильтров
     * @return IFilterCollection
     */
    protected function createFilterCollection(array $config = [])
    {
        return $this->getFilterFactory()
            ->createFilterCollection($config);
    }

    /**
     * Создает фильтр определенного типа. Устанавливет ему опции (если переданы).
     * @param string $type тип фильтра
     * @param array $options опции фильтра
     * @return IFilter созданный фильтр
     */
    protected function createFilter($type, array $options = [])
    {
        return $this->getFilterFactory()
            ->createFilter($type, $options);
    }

    /**
     * Возвращает фабрику фильтров.
     * @throws RequiredDependencyException если фабрика не внедрена
     * @return IFilterFactory
     */
    private function getFilterFactory()
    {
        if (!$this->traitFilterFactory) {
            throw new RequiredDependencyException(sprintf(
                'Filter factory is not injected in class "%s".',
                get_class($this)
            ));
        }

        return $this->traitFilterFactory;
    }
}
