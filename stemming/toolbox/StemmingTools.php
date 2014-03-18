<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\stemming\toolbox;

use phpMorphy;
use umi\stemming\IStemming;
use umi\stemming\IStemmingAware;
use umi\stemming\Stemming;
use umi\toolkit\exception\UnsupportedServiceException;
use umi\toolkit\toolbox\IToolbox;
use umi\toolkit\toolbox\TToolbox;

/**
 * Набор инструментов для морфологических преобразований и анализа отдельных слов.
 * Использует библиотеку {@link http://phpmorphy.sourceforge.net/dokuwiki/ phpMorphy}
 */
class StemmingTools implements IToolbox
{
    /**
     * Имя набора инструментов.
     */
    const NAME = 'stemming';

    /**
     * Папка, в которой следует искать словари штемминга.
     * Скомпилированные словари можно скачать с
     * {@link http://phpmorphy.sourceforge.net/dokuwiki/download#словари сайта библиотеки}.
     * По умолчанию в папке toolbox/dictionaries уже присутствуют словари для самых распространенных языков.
     * @var string $dictionariesDir
     */
    public $dictionariesDir;

    /**
     * Опции штемминга, передаваемые в {@see phpMorphy_MorphyNative::__construct()}.
     * @var array $options
     */
    public $options = array(
        'storage' => phpMorphy::STORAGE_FILE,
        'predict_by_suffix' => true,
        'predict_by_db' => true,
    );

    /**
     * Язык штемминга, например ru_RU
     * @var string $language
     */
    public $language = 'ru_RU';

    /**
     * Сервис штемминга
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
     * Возвращает сервис штемминга.
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
     * Создает сервис штемминга PHPMorphy.
     * @return phpMorphy
     */
    private function createPhpMorphy()
    {
        if (is_null($this->dictionariesDir)) {
            $this->dictionariesDir = __DIR__ . '/dictionaries';
        }
        return new phpMorphy($this->dictionariesDir, $this->language, $this->options);
    }

}
