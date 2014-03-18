<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */
namespace umi\stemming;

use umi\stemming\exception\RuntimeException;

/**
 * Реализация поведения {@link IStemmingAware}.
 */
trait TStemmingAware
{
    /**
     * Сервис штемминга, внедренный в трейт
     * @var IStemming $traitStemming
     */
    private $traitStemming;

    /**
     * @see IStemmingAware::setStemming()
     */
    public function setStemming(IStemming $stemmingService)
    {
        $this->traitStemming = $stemmingService;
    }

    /**
     * Возвращает внедренный сервис штемминга.
     * @throws RuntimeException
     * @return IStemming
     */
    public function getStemming()
    {
        if (is_null($this->traitStemming)) {
            throw new RuntimeException("Stemming service is not injected");
        }
        return $this->traitStemming;
    }
}
