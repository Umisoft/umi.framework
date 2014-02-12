<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\stream\toolbox;

use umi\stream\IStreamService;
use umi\toolkit\exception\UnsupportedServiceException;
use umi\toolkit\toolbox\IToolbox;
use umi\toolkit\toolbox\TToolbox;

/**
 * Инструменты работы с потоками.
 */
class StreamTools implements IToolbox
{
    /**
     * Имя набора инструментов
     */
    const NAME = 'stream';

    use TToolbox;

    /**
     * @var string $streamServiceClass класс сервиса работы с потоками
     */
    public $streamServiceClass = 'umi\stream\StreamService';

    /**
     * {@inheritdoc}
     */
    public function getService($serviceInterfaceName, $concreteClassName)
    {
        switch ($serviceInterfaceName) {
            case 'umi\stream\IStreamService':
            {
                return $this->getStreamService();
            }
        }
        throw new UnsupportedServiceException($this->translate(
            'Toolbox "{name}" does not support service "{interface}".',
            ['name' => self::NAME, 'interface' => $serviceInterfaceName]
        ));
    }

    /**
     * Возвращает сервис для работы с потоками.
     * @return IStreamService
     */
    protected function getStreamService()
    {
        return $this->getPrototype(
            $this->streamServiceClass,
            ['umi\stream\IStreamService']
        )
            ->createSingleInstance();
    }
}

 