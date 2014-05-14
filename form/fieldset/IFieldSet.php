<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\form\fieldset;

use Traversable;
use umi\form\exception\OutOfBoundsException;
use umi\form\exception\RuntimeException;
use umi\form\IFormEntity;

/**
 * Интерфейс группы сущностей.
 */
interface IFieldSet extends IFormEntity, Traversable
{
    /**
     * Добавляет сущность.
     * @param IFormEntity $entity
     * @throws RuntimeException если сущность с заданным именем уже существует
     * @return self
     */
    public function add(IFormEntity $entity);

    /**
     * Возвращает сущность по имени.
     * @param string $name
     * @throws OutOfBoundsException если сущность с заданным именем не существует
     * @return IFormEntity
     */
    public function get($name);

    /**
     * Проверяет наличие сущности по имени.
     * @param string $name
     * @return bool
     */
    public function has($name);

    /**
     * Устанавливает данные.
     * @param array $data данные
     * @return self
     */
    public function setData(array $data);

    /**
     * Возвращает установленные данные.
     * @return array
     */
    public function getData();
}