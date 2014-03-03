<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\stemming\toolbox;

use umi\stemming\IStemming;
use umi\stemming\IStemmingAware;
use umi\stemming\Stemming;
use umi\toolkit\exception\UnsupportedServiceException;
use umi\toolkit\toolbox\IToolbox;
use umi\toolkit\toolbox\TToolbox;

/**
 * Набор инструментов для работы с сессиями.
 */
class StemmingTools implements IToolbox
{
    /**
     * Имя набора инструментов.
     */
    const NAME = 'stemming';

    /**
     * @var string $dictionariesDir
     */
    public $dictionariesDir;

    /**
     * @var array $options
     */
    public $options = array(
        'storage' => \phpMorphy::STORAGE_FILE,
        'predict_by_suffix' => true,
        'predict_by_db' => true,
    );
    /**
     * @var string $language
     */
    public $language = 'ru_RU';
    /**
     * @var IStemming $stemming
     */
    private $stemming;

    use TToolbox;

    /**
     * {@inheritdoc}
     */
    public function getService($serviceInterfaceName, $concreteClassName)
    {
        switch ($serviceInterfaceName) {
            case 'umi\stemming\IStemming':
                return $this->getStemming();
        }
        throw new UnsupportedServiceException($this->translate(
            'Toolbox "{name}" does not support service "{interface}".',
            ['name' => self::NAME, 'interface' => $serviceInterfaceName]
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function injectDependencies($object)
    {
        if ($object instanceof IStemmingAware) {
            $object->setStemming($this->getStemming());
        }
    }

    /**
     * Возвращает сервис сессии.
     * @return IStemming
     */
    protected function getStemming()
    {
        if (!$this->stemming) {
            $this->stemming = new Stemming($this->createPhpMorphy());
        }
        return $this->stemming;
    }

    /**
     * @return \phpMorphy
     */
    private function createPhpMorphy()
    {
        if (is_null($this->dictionariesDir)) {
            $this->dictionariesDir = __DIR__ . '/dictionaries';
        }
        return new \phpMorphy($this->dictionariesDir, $this->language, $this->options);
    }

}
