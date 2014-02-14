<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\syntax;

use umi\syntax\exception\RequiredDependencyException;

/**
 * Трейт для внедрения поддержки работы с синтаксисом.
 */
trait TSyntaxAware
{
    /**
     * @var IParserFactory $traitSyntaxParserFactory фабрика
     */
    private $traitSyntaxParserFactory;

    /**
     * @see ISyntaxAware::setSyntaxParserFactory()
     */
    public function setSyntaxParserFactory(IParserFactory $parserFactory)
    {
        $this->traitSyntaxParserFactory = $parserFactory;
    }

    /**
     * Создает экземпляр парсера с заданной грамматикой.
     * @param array $grammar грамматика
     * @param array $rules правила
     * @throws RequiredDependencyException если инструменты не были внедрены
     * @return IParser
     */
    protected function createParser(array $grammar, array $rules)
    {
        if (!$this->traitSyntaxParserFactory) {
            throw new RequiredDependencyException(sprintf(
                'Syntax parser factory is not injected in class "%s".',
                get_class($this)
            ));
        }

        return $this->traitSyntaxParserFactory->createParser($grammar, $rules);
    }
}