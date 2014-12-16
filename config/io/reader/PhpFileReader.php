<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\config\io\reader;

use umi\config\entity\factory\IConfigEntityFactoryAware;
use umi\config\entity\factory\TConfigEntityFactoryAware;
use umi\config\entity\IConfigSource;
use umi\config\entity\ISeparateConfigSource;
use umi\config\entity\value\ConfigValue;
use umi\config\entity\value\IConfigValue;
use umi\config\exception\InvalidArgumentException;
use umi\config\exception\RuntimeException;
use umi\config\exception\UnexpectedValueException;
use umi\config\io\IConfigAliasResolverAware;
use umi\config\io\TConfigAliasResolverAware;
use umi\i18n\ILocalizable;
use umi\i18n\TLocalizable;

/**
 * Считыватель конфигурации из PHP файла.
 */
class PhpFileReader implements IReader, ILocalizable, IConfigAliasResolverAware, IConfigEntityFactoryAware
{

    use TLocalizable;
    use TConfigAliasResolverAware;
    use TConfigEntityFactoryAware;

    /**
     * {@inheritdoc}
     */
    public function read($configAlias)
    {
        $files = $this->getFilesByAlias($configAlias);

        $masterFilename = isset($files[0]) ? $files[0] : null;
        $localFilename = isset($files[1]) ? $files[1] : null;

        $masterConfigExists = file_exists($masterFilename);

        if ($masterConfigExists) {

            if (!is_readable($masterFilename) || !is_file($masterFilename)) {
                throw new RuntimeException($this->translate(
                    'Cannot read configuration from "{file}".',
                    [
                        'file' => $masterFilename
                    ]
                ));
            }

            /** @noinspection PhpIncludeInspection */
            $config = require $masterFilename;

            if (!is_array($config)) {
                throw new UnexpectedValueException(
                    $this->translate(
                        'Configuration file "{file}" should return an array.',
                        ['file' => $masterFilename]
                    )
                );
            }

            array_walk_recursive(
                $config,
                function (&$v) {
                    $value = $this->createMasterEntity($v);
                    $v = $value;
                }
            );

        } else {
            $config = [];
        }

        if (is_readable($localFilename) && is_file($localFilename)) {
            /** @noinspection PhpIncludeInspection */
            $localSource = require $localFilename;

            if (!is_array($localSource)) {
                throw new UnexpectedValueException(
                    $this->translate(
                        'Configuration file "{file}" should return an array.',
                        ['file' => $localFilename]
                    )
                );
            }

            array_walk_recursive(
                $localSource,
                function (&$v) {
                    $value = $this->createLocalEntity($v);
                    $v = $value;
                }
            );

            $this->mergeConfig($config, $localSource);
        } elseif (!$masterConfigExists) {
            throw new RuntimeException($this->translate(
                'Master configuration file "{master}" or local configuration file "{local}" is not found.',
                [
                    'master' => $masterFilename,
                    'local' => $localFilename
                ]
            ));
        }

        return $this->createConfigSource($configAlias, $config);
    }

    /**
     * Выполняет слияние мастер и локальной конфигурации.
     * @param array $master мастер конфигурация
     * @param array $local локальная конфигурация
     * @throws \Exception
     */
    protected function mergeConfig(array &$master, array &$local)
    {
        foreach ($local as $key => &$localValue) {
            if (isset($master[$key])) {
                $masterValue = & $master[$key];

                if ($masterValue instanceof IConfigSource) {
                    $masterValue = $masterValue->getSource();
                }

                if ($localValue instanceof IConfigSource) {
                    $localValue = $localValue->getSource();
                }

                if (is_array($masterValue)) {
                    if (!is_array($localValue)) {
                        throw new UnexpectedValueException($this->translate(
                            'Local property "{key}" should be array.',
                            [
                                'key' => $key
                            ]
                        ));
                    }

                    $this->mergeConfig($masterValue, $localValue);
                } elseif ($masterValue instanceof IConfigValue) {

                    if ($localValue instanceof IConfigValue) {
                        $localValue = $localValue->get();
                    }
                    try {
                        $masterValue->set($localValue, IConfigValue::KEY_LOCAL)
                            ->save();
                    } catch (InvalidArgumentException $e) {
                        throw new UnexpectedValueException($this->translate(
                                'Local property "{key}" should be scalar.',
                                ['key' => $key]
                            ),
                            0,
                            $e
                        );
                    }
                } else {
                    throw new UnexpectedValueException($this->translate(
                        'Unexpected property type "{type}" with key "{key}".',
                        [
                            'key' => $key,
                            'type' => gettype($masterValue) . (is_object($masterValue) ? ':' . get_class($masterValue) : '')
                        ]
                    ));
                }
            } else {
                if ($localValue instanceof IConfigSource || $localValue instanceof IConfigValue) {
                    $master[$key] = $localValue;
                } elseif (is_array($localValue)) {
                    array_walk_recursive(
                        $localValue,
                        function (&$v) {
                            $v = $this->createConfigValue(
                                [
                                    IConfigValue::KEY_LOCAL => $v
                                ]
                            );
                        }
                    );

                    $master[$key] = $localValue;
                } else {
                    $master[$key] = $this->createConfigValue(
                        [
                            IConfigValue::KEY_LOCAL => $localValue,
                        ]
                    );
                }
            }
        }
    }

    /**
     * Создает сущности на основе конфигурации.
     * @param string $value значение
     * @throws UnexpectedValueException если значение не скалярное
     * @return IConfigSource|ISeparateConfigSource|IConfigValue
     */
    protected function createMasterEntity($value)
    {
        if (!is_scalar($value) && !is_null($value)) {
            throw new UnexpectedValueException($this->translate(
                'Unexpected configuration value. Configuration can contain only scalar values.'
            ));
        }
        if (preg_match('/^{#(\S+):(.+)}$/', $value, $matches)) {
            list(, $command, $value) = $matches;

            switch ($command) {
                case self::COMMAND_PART:
                    return $this->read($value);
                case self::COMMAND_LAZY:
                    return $this->createSeparateConfigSource('lazy', $value);
                case self::LOCAL_DIR: {
                    $files = $this->getFilesByAlias($value);
                    return $this->createConfigValue(
                        [
                            IConfigValue::KEY_LOCAL => $files[IConfigValue::KEY_LOCAL]
                        ]
                    );
                }
                case self::MASTER_DIR: {
                    $files = $this->getFilesByAlias($value);
                    return $this->createConfigValue(
                        [
                            IConfigValue::KEY_MASTER => $files[IConfigValue::KEY_MASTER]
                        ]
                    );
                }
            }
        }

        return $this->createConfigValue(
            [
                IConfigValue::KEY_MASTER => $value
            ]
        );
    }

    /**
     * Создает сущности на основе конфигурации.
     * @param string $value значение
     * @throws UnexpectedValueException если значение не скалярное
     * @return IConfigSource|ISeparateConfigSource|IConfigValue
     */
    protected function createLocalEntity($value)
    {
        if (!is_scalar($value) && !is_null($value)) {
            throw new UnexpectedValueException($this->translate(
                'Unexpected configuration value. Configuration can contain only scalar values.'
            ));
        }
        if (preg_match('/^{#(\S+):(.+)}$/', $value, $matches)) {
            list(, $command, $value) = $matches;

            switch ($command) {
                case self::COMMAND_PART:
                    return $this->read($value);
                case self::COMMAND_LAZY:
                    return $this->createSeparateConfigSource('lazy', $value);
                case self::LOCAL_DIR: {
                    $files = $this->getFilesByAlias($value);
                    return $this->createConfigValue(
                        [
                            IConfigValue::KEY_LOCAL => $files[IConfigValue::KEY_LOCAL]
                        ]
                    );
                }
                case self::MASTER_DIR: {
                    $files = $this->getFilesByAlias($value);
                    return $this->createConfigValue(
                        [
                            IConfigValue::KEY_MASTER => $files[IConfigValue::KEY_MASTER]
                        ]
                    );
                }
            }
        }

        return $value;

    }

    /**
     * @param array $values
     * @return ConfigValue
     */
    protected function createConfigValue(array $values = [])
    {
        return new ConfigValue($values);
    }
}