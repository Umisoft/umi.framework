<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\templating\engine\php;

use umi\templating\engine\ITemplateEngine;
use umi\templating\exception\RuntimeException;

/**
 * PHP шаблонизатор.
 */
class PhpTemplateEngine implements ITemplateEngine
{

    /**
     * Опция для задания директорий расположения шаблонов
     */
    const OPTION_TEMPLATE_DIRECTORIES = 'directories';
    /**
     * Опция для задания расширения файлов шаблонов
     */
    const OPTION_TEMPLATE_FILE_EXTENSION = 'extension';

    /**
     * @var array $options опции
     */
    protected $options = [];

    /**
     * @var callable[] $functions
     */
    protected $functions = [];

    /**
     * @var array $templateDirectories директории расположения шаблонов
     */
    private $templateDirectories;

    /**
     * {@inheritdoc}
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function render($templateFile, array $variables = [])
    {
        return (new PhpTemplate($this))
            ->render($this->getTemplateFilename($templateFile), $variables);
    }

    /**
     * Дабавляет расширение с функциями.
     * @param IPhpExtension $extension
     * @return $this
     */
    public function addExtension(IPhpExtension $extension)
    {
        foreach ($extension->getFunctions() as $functionName => $function) {
            $this->functions[$functionName] = $function;
        }

        return $this;
    }

    /**
     * Magic method: вызывает помошник вида.
     * @param string $name имя помошника вида
     * @param array $arguments аргументы
     * @throws RuntimeException если коллекция помощников вида не была внедрена
     * @return string
     */
    public function callHelper($name, array $arguments)
    {
        if (!isset($this->functions[$name])) {
            throw new RuntimeException(sprintf('Function "%s" does not exist', $name));
        }

        return call_user_func_array($this->functions[$name], $arguments);
    }

    /**
     * Возвращает директории расположения шаблонов.
     * @return array
     */
    public function getTemplateDirectories()
    {
        if (is_null($this->templateDirectories)) {
            $this->templateDirectories = isset($this->options[self::OPTION_TEMPLATE_DIRECTORIES]) ? $this->options[self::OPTION_TEMPLATE_DIRECTORIES] : [];
        }

        return (array) $this->templateDirectories;
    }

    /**
     * Возрващает имя файла шаблона по имени шаблона.
     * @param string $templateName имя шаблона
     * @return string
     */
    protected function getTemplateFilename($templateName)
    {
        if (isset($this->options[self::OPTION_TEMPLATE_FILE_EXTENSION])) {
            $templateName .= '.' . $this->options[self::OPTION_TEMPLATE_FILE_EXTENSION];
        }

        return $templateName;
    }
}