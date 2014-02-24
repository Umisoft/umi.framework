<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\stemming;

/**
 * Интерфейс сервиса штемминга
 * @package umi\stemming
 */
interface IStemming
{
    /**
     * Тип лемматизации с использованием предсказания
     */
    const LEMM_NORMAL = \phpMorphy_MorphyInterface::NORMAL;
    /**
     * Тип лемматизации без использования предсказания
     */
    const LEMM_IGNORE_PREDICT = \phpMorphy_MorphyInterface::IGNORE_PREDICT;
    /**
     * Тип лемматизации с испольщованием только предсказания
     */
    const LEMM_ONLY_PREDICT = \phpMorphy_MorphyInterface::ONLY_PREDICT;

    /**
     * @param $word
     * @param int $type
     * @return array
     */
    public function getBaseForm($word, $type = IStemming::LEMM_NORMAL);

    /**
     * @param $word
     * @param int $type
     * @return array
     */
    public function getAllForms($word, $type = IStemming::LEMM_NORMAL);

    /**
     * @param $word
     * @param int $type
     * @return string
     */
    public function getPartOfSpeech($word, $type = IStemming::LEMM_NORMAL);

    /**
     * @param $word
     * @param int $type
     * @return \phpMorphy_Paradigm_Collection
     */
    public function getWordParadigms($word, $type = IStemming::LEMM_NORMAL);
}
