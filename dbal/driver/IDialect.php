<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\dbal\driver;

use Doctrine\DBAL\Connection;
use umi\dbal\builder\IDeleteBuilder;
use umi\dbal\builder\IInsertBuilder;
use umi\dbal\builder\ISelectBuilder;
use umi\dbal\builder\IUpdateBuilder;
use umi\dbal\exception\IException;

/**
 * Инкапсулирует специфику языка запросов соответствующего драйвера БД.
 */
interface IDialect
{
    /**
     * Инициализирует соединение, используя специфику драйвера.
     * Может быть переопределен в конкретном диалекте.
     *
     * @param Connection $connection
     */
    public function initConnection(Connection $connection);

    /**
     * Строит и возвращает sql-запрос для отключения индексов в отдельной таблице
     * @param string $tableName
     * @return string
     */
    public function getDisableKeysSQL($tableName);

    /**
     * Строит и возвращает sql-запрос для включения индексов в отдельной таблице
     * @param string $tableName
     * @return string
     */
    public function getEnableKeysSQL($tableName);

    /**
     * Строит и возвращает sql-запрос для отключения проверки внешних ключей в бд
     * @return string
     */
    public function getDisableForeignKeysSQL();

    /**
     * Строит и возвращает sql-запрос для включения проверки внешних ключей в бд
     * @return string
     */
    public function getEnableForeignKeysSQL();

    /**
     * Строит и возвращает sql-запрос на выборку данных
     * @param ISelectBuilder $query select-запрос
     * @throws IException если не удалось построить запрос
     * @return string
     */
    public function buildSelectQuery(ISelectBuilder $query);

    /**
     * Строит и возвращает sql-запрос на получение количества записей, удовлетворяюших SELECT-запросу
     * @param ISelectBuilder $query select-запрос
     * @throws IException если не удалось построить запрос
     * @return string
     */
    public function buildSelectFoundRowsQuery(ISelectBuilder $query);

    /**
     * Строит и возвращает sql-запрос на обновление данных.
     * @param IUpdateBuilder $query update-запрос
     * @throws IException если не удалось построить запрос
     * @return string
     */
    public function buildUpdateQuery(IUpdateBuilder $query);

    /**
     * Строит и возвращает sql-запрос на вставку данных.
     * @param IInsertBuilder $query insert-запрос
     * @throws IException если не удалось построить запрос
     * @return string
     */
    public function buildInsertQuery(IInsertBuilder $query);

    /**
     * Строит и возвращает sql-запрос на удаление данных
     * @param IDeleteBuilder $query delete-запрос
     * @throws IException если не удалось построить запрос
     * @return string
     */
    public function buildDeleteQuery(IDeleteBuilder $query);
}
