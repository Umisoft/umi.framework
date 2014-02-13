<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\templating\engine\php;

use umi\templating\exception\RuntimeException;

/**
 * PHP шаблонизатор.
 */
class PhpTemplate
{
    /**
     * @var string $templatingDirectory директория с шаблонами
     */
    protected $templatingDirectory;
    /**
     * @var PhpTemplateEngine $templateEngine
     */
    protected $templateEngine;

    /**
     * Конструктор
     * @param string $directory
     * @param PhpTemplateEngine $templateEngine
     */
    public function __construct($directory = '.', PhpTemplateEngine $templateEngine)
    {
        $this->templatingDirectory = $directory;
        $this->templateEngine = $templateEngine;
    }

    /**
     * {@inheritdoc}
     */
    public function render($templateName, array $templateVariables = [])
    {
        $templateFilename = $this->templatingDirectory . DIRECTORY_SEPARATOR . $templateName;

        if (!is_readable($templateFilename)) {
            throw new RuntimeException(sprintf(
                'Cannot render template "%s". PHP template file "%s" is not readable.',
                $templateName,
                $templateFilename
            ));
        }

        extract($templateVariables);

        ob_start();
        try {
            /** @noinspection PhpIncludeInspection */
            require $templateFilename;
        } catch (\Exception $e) {
            ob_end_clean();
            throw $e;
        }

        return ob_get_clean();
    }

    /**
     * Magic method: вызывает помощник шаблонов.
     * @param string $name имя помощника шаблонов
     * @param array $arguments аргументы
     * @return string
     */
    public function __call($name, array $arguments)
    {
        return $this->templateEngine->callHelper($name, $arguments);
    }
}