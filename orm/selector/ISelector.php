<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\orm\selector;

use umi\dbal\builder\ISelectBuilder;
use umi\i18n\ILocalesService;
use umi\orm\collection\ICollection;
use umi\orm\exception\NonexistentEntityException;
use umi\orm\metadata\field\IField;
use umi\orm\objectset\IObjectSet;
use umi\orm\selector\condition\IFieldCondition;
use umi\orm\selector\condition\IFieldConditionGroup;

/**
 * Инструмент для формирования выборок объектов из коллекции,
 * а так же связанных объектов.
 */
interface ISelector extends \IteratorAggregate
{
    /**
     * Сортировка по возрастанию
     */
    const ORDER_ASC = 'ASC';
    /**
     * Сортировка по убыванию
     */
    const ORDER_DESC = 'DESC';

    /**
     * Префикс для плейсхолдера
     */
    const PLACEHOLDER_PREFIX = ':value';
    /**
     * Разделитель для плейсхолдера
     */
    const PLACEHOLDER_SEPARATOR = '_';
    /**
     * Разделитель для доступа к связанным полям
     */
    const FIELD_SEPARATOR = '.';
    /**
     * Разделитель для алиасов.
     * Не может быть "." так как это стандартный разделитель для полей в mysql
     */
    const ALIAS_SEPARATOR = ':';
    /**
     * Постфикс для алиаса bridge - таблицы
     */
    const BRIDGE_ALIAS_POSTFIX = '_bridge';
    /**
     * Постфикс для имен полей и типов, означающий выборку всех дочерних элементов
     */
    const ASTERISK = '*';

    /**
     * Сбрасывает результаты выборки для того, чтобы селектор можно было использовать повторно.
     * @return self
     */
    public function resetResult();

    /**
     * Уточняет выбираемые типы данных.
     * @param array $typeNames массив имен типов. Если после имени типа будет указан asterisk (*),
     * будут выбраны так же все дочерние типы.
     * Пример: 'notebook*' - будет выбран тип notebook и все дочерние от него, '*' - будут выбран все типы,
     * включая базовый)
     * @throws NonexistentEntityException если какого-либо из указанных типов не существует в коллекции
     * @return self
     */
    public function types(array $typeNames);

    /**
     * Уточняет массив имен полей для выборки.<br />
     * Если поля не уточнены, будут загружены все поля объекта для указанных типов. <br />
     * @param array $fieldNames имена полей (Ex: array('login', 'profile'))
     * @throws NonexistentEntityException если какое-либо из указанных полей не существует в коллекции
     * @return self
     */
    public function fields(array $fieldNames = []);

    /**
     * Возвращает список явно запрошенных для выборки полей.
     * @return IField[]
     */
    public function getFields();

    /**
     * Загрузить связанную через belongs-to сущность вместе с объектом (одним запросом).
     * @param string $relationFieldPath путь к полю, через которое связанна сущность (Ex: city.country).
     * @param array $fieldNames уточняющий список имен полей связанной сущности, которые нужно загрузить.
     * Если не указан, будут загружены все поля связанной сущности.
     * @throws NonexistentEntityException если какое-либо из указанных полей не существует
     * @return self
     */
    public function with($relationFieldPath, array $fieldNames = []);

    /**
     * Возвращает информацию о загружаемых вместе с объектом belongs-to сущностях
     * @return array в формате [$relationFieldPath => [BelongsToRelationField, IField[]], ...]
     */
    public function getWithInfo();

    /**
     * Устанавливает настройки для загрузки локализованных свойств объектов.
     * @param string $localization указание на локаль, в которой загружаются объекты.
     * По умолчанию объекты загружаются в текущей локали. Можно указать конкретную локаль
     * или ILocalesService::LOCALE_ALL для загрузки объектов во всех локалях.
     * @return self
     */
    public function localization($localization = ILocalesService::LOCALE_ALL);

    /**
     * Возвращает настройки для загрузки локализованных свойств объектов.
     * @return string
     */
    public function getLocalization();

    /**
     * Начинает новую группу выражений.<br />
     * Группы выражений можно вкладывать друг в друга. <br />
     * Группа становится текущей до вызова ISelector::end().
     * @param string $mode режим сложения выражений внутри группы (AND, OR, XOR)
     * @return self
     */
    public function begin($mode = IFieldConditionGroup::MODE_AND);

    /**
     * Завершает текущую группу выражений.
     * Текущей становится родительская группа.
     * @return self
     */
    public function end();

    /**
     * Добавляет условие выборки по полю коллекции.
     * @param string $fieldPath имя поля, либо путь к связанному полю (Ex: profile.lname)
     * @param null|string $localeId идентификатор локали, для локализованных полей.
     * Если для локализованного поля $localeId не указан, будет взята текущая локаль
     * @throws NonexistentEntityException если поле не существует в коллекции
     * @return IFieldCondition
     */
    public function where($fieldPath, $localeId = null);

    /**
     * Добавляет условие сортировки.
     * @param string $fieldPath имя поля, либо путь к связанному полю (Ex: profile.lname)
     * @param string $direction направление сортировки, ASC по умолчанию
     * @throws NonexistentEntityException если поле не существует в коллекции
     * @return self
     */
    public function orderBy($fieldPath, $direction = self::ORDER_ASC);

    /**
     * Ограничивает выборку.
     * @param int $limit выбираемое кол-во объектов
     * @param int|null $offset смещение
     * @return self
     */
    public function limit($limit, $offset = null);

    /**
     * Возвращает установленный для выборки limit
     * @return int|null
     */
    public function getLimit();

    /**
     * Возвращает установленный для выборки offset
     * @return int|null
     */
    public function getOffset();

    /**
     * Формирует и возвращает билдер Select-запросов по установленным условиям для селектора.
     * Билдер можно дополнить низкоуровнеми условиями для сложных запросов.
     * @return ISelectBuilder
     */
    public function getSelectBuilder();

    /**
     * Возвращает удовлетворяющий условиям набор объектов.
     * @alias
     * @return IObjectSet
     */
    public function result();

    /**
     * Возвращает удовлетворяющий условиям набор объектов.
     * @return IObjectSet
     */
    public function getResult();

    /**
     * Формирует билдер Select-запросов по установленным условиям для селектора и
     * возвращает количество объектов, удовлетворяющих выборке, без учета limit.
     * @return int
     */
    public function getTotal();

    /**
     * Возвращает коллекцию выборки.
     * @return ICollection
     */
    public function getCollection();

    /**
     * Выставляет инициализатор билдера запроса
     * @param callable $builderInitializer
     * @return self
     */
    public function setSelectBuilderInitializer(callable $builderInitializer);
}
