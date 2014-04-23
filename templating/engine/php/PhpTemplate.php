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
     * @var PhpTemplateEngine $templateEngine
     */
    protected $templateEngine;

    /**
     * Конструктор
     * @param PhpTemplateEngine $templateEngine
     */
    public function __construct(PhpTemplateEngine $templateEngine)
    {
        $this->templateEngine = $templateEngine;
    }

    /**
     * {@inheritdoc}
     */
    public function render($templateName, array $templateVariables = [])
    {
        $templateFilePath = $this->findTemplate($templateName);
        if (!is_readable($templateFilePath)) {
            throw new RuntimeException(sprintf(
                'Cannot render template "%s". PHP template file "%s" is not readable.',
                $templateName,
                $templateFilePath
            ));
        }

        extract($templateVariables);

        ob_start();
        try {
            /** @noinspection PhpIncludeInspection */
            require $templateFilePath;
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

    protected function findTemplate($templateName)
    {
        $directories = $this->templateEngine->getTemplateDirectories();

        foreach($directories as $directory) {
            $templateFilePath = $directory . DIRECTORY_SEPARATOR . $templateName;
            if (is_file($templateFilePath)) {
                return $templateFilePath;
            }
        }
        throw new RuntimeException(
            sprintf('Unable to find template "%s" (looked into: %s).', $templateName, implode(', ', $directories))
        );
    }
}