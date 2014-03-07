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
 * Серви штемминга, декорирующий phpMorphy
 */
class Stemming implements IStemming
{
    /**
     * @var \phpMorphy $phpmorphy
     */
    private $phpmorphy;

    /**
     * @param \phpMorphy $phpmorphy
     */
    public function __construct(\phpMorphy $phpmorphy)
    {
        $this->phpmorphy = $phpmorphy;
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseForm($word, $type = IStemming::LEMM_NORMAL)
    {
        $baseForms = $this->phpmorphy->getBaseForm(mb_strtoupper($word, 'utf-8'), $type);
        return is_array($baseForms) ? $baseForms : [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAllForms($word, $type = IStemming::LEMM_NORMAL)
    {
        return $this->phpmorphy->getAllForms(mb_strtoupper($word, 'utf-8'), $type);
    }

    /**
     * {@inheritdoc}
     */
    public function getPartOfSpeech($word, $type = IStemming::LEMM_NORMAL)
    {
        return $this->phpmorphy->getPartOfSpeech(mb_strtoupper($word, 'utf-8'), $type);
    }

    /**
     * {@inheritdoc}
     */
    public function getCommonRoot($word, $type = IStemming::LEMM_NORMAL)
    {
        $pseudoRoots = $this->phpmorphy->getPseudoRoot(mb_strtoupper($word, 'utf-8'), $type);
        return current($pseudoRoots);
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchableRoot($word, $limit, $type = IStemming::LEMM_NORMAL)
    {
        $word = mb_strtoupper($word, 'utf-8');
        $partsOfSpeech = $this->phpmorphy->getPartOfSpeech($word);
        $commonRoot = $this->getCommonRoot($word, $type);
        $searchableRoot = mb_strlen($commonRoot, 'utf-8') >= $limit ? $commonRoot : $word;
        //todo if word longer than limit, find shortest form

        if(count($partsOfSpeech) == 1){
            return $searchableRoot;
        } else {
            if(array_search('ПРЕДЛ', $partsOfSpeech, true) !== false) {
                return $word;
            }
            return $searchableRoot;
        }
    }
}
