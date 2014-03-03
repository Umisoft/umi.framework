<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\i18n;

use umi\i18n\exception\RequiredDependencyException;

/**
 * Трейт для поддержки локалей.
 */
trait TLocalesAware
{
    /**
     * @var ILocalesService $traitLocalesService сервис для работы с локалями
     */
    private $traitLocalesService;

    /**
     * @see ILocalesAware::setLocalesService()
     */
    public function setLocalesService(ILocalesService $localesService)
    {
        $this->traitLocalesService = $localesService;
    }

    /**
     * Возвращает локаль по умолчанию
     * @return string
     */
    protected function getDefaultLocale()
    {
        return $this->getLocalesService()
            ->getDefaultLocale();
    }

    /**
     * Возвращает текущую локаль
     * @return string
     */
    protected function getCurrentLocale()
    {
        return $this->getLocalesService()
            ->getCurrentLocale();
    }

    /**
     * Устанавливает локаль по умолчанию
     * @param string $localeId
     * @return $this
     */
    protected function setDefaultLocale($localeId)
    {
        return $this->getLocalesService()
            ->setDefaultLocale($localeId);
    }

    /**
     * Устанавливает текущую локаль
     * @param string $localeId
     * @return $this
     */
    protected function setCurrentLocale($localeId)
    {
        return $this->getLocalesService()
            ->setCurrentLocale($localeId);
    }

    /**
     * Возвращает сервис для работы с локалями
     * @throws RequiredDependencyException если сервис не был внедрен
     * @return ILocalesService
     */
    private function getLocalesService()
    {
        if (!$this->traitLocalesService) {
            throw new RequiredDependencyException(sprintf(
                'Locales service is not injected in class "%s".',
                get_class($this)
            ));
        }

        return $this->traitLocalesService;
    }
}
