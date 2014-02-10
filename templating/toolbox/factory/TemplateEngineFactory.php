<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\templating\toolbox\factory;

use umi\templating\engine\ITemplateEngine;
use umi\templating\engine\ITemplateEngineFactory;
use umi\templating\exception\OutOfBoundsException;
use umi\toolkit\factory\IFactory;
use umi\toolkit\factory\TFactory;

/**
 * Фабрика шаблонизаторов.
 */
class TemplateEngineFactory implements ITemplateEngineFactory, IFactory
{
    use TFactory;

    /**
     * @var string $engineClasses классы существующих шаблонизаторов
     */
    public $engineClasses = [
        self::PHP_ENGINE => 'umi\templating\engine\php\PhpTemplateEngine'
    ];

    /**
     * @var array $defaultOptions опции шаблонизаторов по умолчанию
     */
    public $defaultOptions = [
        self::PHP_ENGINE => []
    ];

    /**
     * @var array $initializers
     */
    protected $initializers = [];

    /**
     * {@inheritdoc}
     */
    public function createTemplateEngine($type, array $options = [])
    {

        if (!isset($this->engineClasses[$type])) {
            throw new OutOfBoundsException($this->translate(
                'Cannot create template engine "{type}". Template engine "{type}" is not registered.',
                [
                    'type' => $type
                ]
            ));
        }

        /**
         * @var ITemplateEngine $engine
         */
        $engine = $this->getPrototype(
                $this->engineClasses[$type],
                ['umi\templating\engine\ITemplateEngine']
            )
            ->createInstance();

        $options = $this->configToArray($options, true);

        if (isset($this->defaultOptions[$type])) {
            $defaultOptions = $this->configToArray($this->defaultOptions[$type], true);
            $options = $this->mergeConfigOptions($options, $defaultOptions);
        }

        $engine->setOptions($options);

        if (isset($this->initializers[$type])) {
            $this->initializers[$type]($engine);
        }

        return $engine;
    }

    /**
     * {@inheritdoc}
     */
    public function setInitializer($type, callable $initializer)
    {
        if (!isset($this->engineClasses[$type])) {
            throw new OutOfBoundsException($this->translate(
                'Cannot set initializer for template engine "{type}". Template engine "{type}" is not registered.',
                [
                    'type' => $type
                ]
            ));
        }

        $this->initializers[$type] = $initializer;

        return $this;
    }
}
