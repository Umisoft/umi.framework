<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\orm\metadata\field;

use umi\dbal\builder\IQueryBuilder;
use umi\orm\exception\InvalidArgumentException;
use umi\orm\exception\NonexistentEntityException;
use umi\orm\object\IObject;
use umi\orm\object\property\IProperty;

/**
 * Поле типа данных.
 */
interface IField
{
    // relation
    const TYPE_BELONGS_TO = 'belongsToRelation';
    const TYPE_MANY_TO_MANY = 'manyToManyRelation';
    const TYPE_HAS_MANY = 'hasManyRelation';
    const TYPE_HAS_ONE = 'hasOneRelation';
    const TYPE_OBJECT_RELATION = 'objectRelation';

    // integer's
    const TYPE_INTEGER = 'integer';
    const TYPE_DECIMAL = 'decimal';
    const TYPE_REAL = 'real';
    const TYPE_BOOL = 'bool';

    // string's & blobs
    const TYPE_STRING = 'string';
    const TYPE_CHAR = 'char';
    const TYPE_TEXT = 'text';
    const TYPE_BLOB = 'blob';

    // date & time
    const TYPE_TIMESTAMP = 'timestamp';
    const TYPE_DATE = 'date';
    const TYPE_TIME = 'time';
    const TYPE_DATE_TIME = 'dateTime';

    // special fields
    const TYPE_IDENTIFY = 'identify';
    const TYPE_GUID = 'guid';
    const TYPE_VERSION = 'version';

    const TYPE_MPATH = 'mpath';
    const TYPE_SLUG = 'slug';
    const TYPE_URI = 'uri';
    const TYPE_LEVEL = 'level';
    const TYPE_ORDER = 'order';

    const TYPE_COUNTER = 'counter';

    //TODO сделать реализацию полей данного типа
    const TYPE_PASSWORD = 'password';
    const TYPE_MONEY = 'money';
    const TYPE_FILE = 'file';
    const TYPE_IMAGE = 'image';

    /**
     * Возвращает имя поля
     * @return string
     */
    public function getName();

    /**
     * Возвращает тип поля
     * @return string
     */
    public function getType();

    /**
     * Проверяет, доступно ли поле на запись
     * @return boolean
     */
    public function getIsReadOnly();

    /**
     * Возвращает php-тип данных поля. Используется для PDO.<br />
     * http://ru2.php.net/manual/en/function.gettype.php
     * @return string
     */
    public function getDataType();

    /**
     * Возвращает имя столбца таблицы для поля
     * @param string|null $localeId идентификатор локали
     * @throws NonexistentEntityException если не найдено имя столбца для указанной локали
     * @return string
     */
    public function getColumnName($localeId = null);

    /**
     * Возвращает имя getter'а для доступа к значению поля
     * @return string
     */
    public function getAccessor();

    /**
     * Возвращает имя setter'а для установки значения поля
     * @return string
     */
    public function getMutator();

    /**
     * Возвращает значение поля по умолчанию (которое будет сохраняться в БД при создании объекта).
     * @param string|null $localeId идентификатор локали
     * @throws NonexistentEntityException если не найдено значения для указанной локали
     * @return mixed
     */
    public function getDefaultValue($localeId = null);

    /**
     * Возвращает конфигурацию валидаторов.
     * @return array в формате [$validatorType => [$optionName => $value, ...], ...]
     */
    public function getValidatorsConfig();

    /**
     * Возвращает конфигурацию фильтров.
     * @return array в формате [$filterType => [$optionName => $value, ...], ...]
     */
    public function getFiltersConfig();

    /**
     * Проверяет, локазизовано ли поле
     * @return bool
     */
    public function getIsLocalized();

    /**
     * Возвращает список локализаций для поля
     * @return array в виде array($localeId => array('column' => $columnName, 'default' => $defaultValue), ...)
     */
    public function getLocalizations();

    /**
     * Проверяет, есть ли указанная локаль у поля
     * @param string $localeId идентификатор локали
     * @return bool
     */
    public function hasLocale($localeId);

    /**
     * Проверяет, соответствует ли указанное значение типу поля.
     * @internal
     * @param mixed $propertyValue значение свойства
     * @throws InvalidArgumentException метод может выкинуть исключение,
     * для предоставления более детальной информации о проблеме
     * @return boolean
     */
    public function validateInputPropertyValue($propertyValue);

    /**
     * Подготавливает и возвращает значение свойства по внутреннему значению из БД.
     * @internal
     * @param IObject $object объект, для которого подготавливается свойство
     * @param mixed $internalDbValue внутреннее значение свойства в БД
     * @return mixed
     */
    public function preparePropertyValue(IObject $object, $internalDbValue);

    /**
     * Подготавливает и возвращает значение для записи в БД.
     * @internal
     * @param IObject $object объект, для которого будет установлено свойство
     * @param mixed $propertyValue значение свойства
     * @throws InvalidArgumentException если значение не соответствует ожидаемому
     * @return mixed
     */
    public function prepareDbValue(IObject $object, $propertyValue);

    /**
     * Дополняет запрос условием на изменение значения свойства в БД.
     * @internal
     * @param IObject $object объект, для которого выставляется значение
     * @param IProperty $property свойство, для которого выставляется значение
     * @param IQueryBuilder $builder построитель запросов, с помощью которого изменяется значние
     * @return self
     */
    public function persistProperty(IObject $object, IProperty $property, IQueryBuilder $builder);
}
