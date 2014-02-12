<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\stream;

use umi\stream\exception\AlreadyRegisteredException;
use umi\stream\exception\NotRegisteredException;
use umi\stream\exception\RuntimeException;

/**
 * Сервис для работы с потоками.
 */
class StreamService implements IStreamService
{
    /**
     * @var callable[] $streams зарегистрированные потоки
     */
    public $streams = [];

    /**
     * Конструктор.
     */
    public function __construct() {
        StreamWrapper::setStreamService($this);
    }

    /**
     * {@inheritdoc}
     */
    public function hasStream($protocol)
    {
        return isset($this->streams[$protocol]);
    }

    /**
     * {@inheritdoc}
     */
    public function registerStream($protocol, callable $handler)
    {
        if ($this->hasStream($protocol)) {
            throw new AlreadyRegisteredException(sprintf('Protocol "%s" already registered.', $protocol));
        }

        if (!stream_wrapper_register($protocol, 'umi\stream\StreamWrapper')) {
            throw new RuntimeException(sprintf('Cannot register stream wrapper for protocol "%s".', $protocol));
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function unregisterStream($protocol)
    {
        if ($this->hasStream($protocol)) {
            throw new NotRegisteredException(sprintf('Protocol "%s" is not registered.', $protocol));
        }
        unset($this->streams[$protocol]);

        return stream_wrapper_unregister($protocol);
    }

    /**
     * {@inheritdoc}
     */
    public function executeStream($uri)
    {
        $protocol = parse_url($uri, PHP_URL_SCHEME);

        if ($this->hasStream($protocol)) {
            throw new NotRegisteredException(sprintf('Cannot execute stream. Protocol "%s" is not registered.', $protocol));
        }

        return $this->streams[$protocol]($uri);
    }

}
