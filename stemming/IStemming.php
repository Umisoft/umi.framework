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
 * Интерфейс сервиса штемминга.
 * @package umi\stemming
 */
interface IStemming
{
    /**
     * Алгоритм лемматизации с использованием предсказания
     */
    const LEMM_NORMAL = \phpMorphy_MorphyInterface::NORMAL;
    /**
     * Алгоритм лемматизации без использования предсказания
     */
    const LEMM_IGNORE_PREDICT = \phpMorphy_MorphyInterface::IGNORE_PREDICT;
    /**
     * Алгоритм лемматизации с использованием только предсказания
     */
    const LEMM_ONLY_PREDICT = \phpMorphy_MorphyInterface::ONLY_PREDICT;
    /**
     * Алгоритм лемматизации, оставляющий максимально близкую к оригиналу основу слова.
     * Полезно для поиска
     */
    const LEMM_SEARCHABLE = 64;

    /**
     * Определяет возможные базовые формы слова.
     * @param string $word
     * @param int $type Алгоритм лемматизации
     * @return array
     */
    public function getBaseForm($word, $type = IStemming::LEMM_NORMAL);

    /**
     * Определяет все возможные формы слова.
     * @param string $word
     * @param int $type Алгоритм лемматизации
     * @return array
     */
    public function getAllForms($word, $type = IStemming::LEMM_NORMAL);

    /**
     * Определяет возможные части речи, возвращает кириллическую аббревиатуру для каждой.
     * @param string $word
     * @param int $type Алгоритм лемматизации
     * @return string
     */
    public function getPartOfSpeech($word, $type = IStemming::LEMM_NORMAL);

    /**
     * Вычисляет общий корень для всех форм слова. Вернет пустую строку, если общего корня нет.
     * @param string $word
     * @param int $type Алгоритм лемматизации
     * @return string
     */
    public function getCommonRoot($word, $type = IStemming::LEMM_NORMAL);

    /**
     * Вычисляет корень слова, максимально близкий к исходному по набору символов.
     * Полезен для поиска, где необходимо отбросить слова, слишком далекие по смыслу от искомого слова.
     * @param string $word
     * @param int $limit Минимальная длина возвращаемого слова
     * @param int $type Алгоритм лемматизации
     * @return string
     */
    public function getSearchableRoot($word, $limit, $type = IStemming::LEMM_NORMAL);
}
