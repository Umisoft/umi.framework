<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\stream;

use umi\toolkit\exception\RequiredDependencyException;

/**
 * Обертка для потока.
 * Обертка передает управление сервису потоков.
 */
class StreamWrapper
{
    /**
     * @var null|resource $content текущий контекст или NULL, если в вызывающую функцию не было передано никакого контекста
     * @see http://www.php.net/manual/ru/class.streamwrapper.php#streamwrapper.props.context
     */
    public $content = null;
    /**
     * @var resource $handle generic resource handle
     */
    public $handle = null;
    /**
     * @var string $data результат работы сервиса
     */
    protected $data = '';
    /**
     * @var int $position текущая позиция потока
     */
    protected $position = 0;
    /**
     * @var int $length общий размер потока
     */
    protected $length;
    /**
     * @var IStreamService $streamServices контейнер для доступа к сервисам
     */
    private static $streamServices;

    /**
     * {@inheritdoc}
     */
    public static function setStreamService(IStreamService $tools)
    {
        self::$streamServices = $tools;
    }

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
    }

    /**
     * Открывает поток
     * @link http://www.php.net/manual/ru/streamwrapper.stream-open.php
     * @param string $path URL, который будет передан в вызывающую функцию
     * @param string $mode режим открытия файла, аналогичен режимам для fopen()
     * @param int $options дополнительные флаги, задаваемые API потоков
     * @param string &$opened_path полный путь к открытому файлу или ресурсу
     * @return bool TRUE в случае успешного завершения или FALSE в случае возникновения ошибки
     */
    public function stream_open($path, $mode, $options, &$opened_path)
    {
        $this->data = self::getStreamService()->executeStream($path);
        $this->position = 0;
        $this->length = mb_strlen($this->data, '8bit');

        return true;
    }

    /**
     * Читает данные из потока
     * @link http://www.php.net/manual/ru/streamwrapper.stream-read.php
     * @param int $count cколько байт данных от текущей позиции требуется вернуть
     * @return string Если в потоке имеется меньше байт, чем count, нужно возвращать столько, сколько доступно.
     * Если доступных данных больше нет, нужно вернуть либо FALSE, либо пустую строку.
     */
    public function stream_read($count)
    {
        $result = mb_substr($this->data, $this->position, $count, '8bit');
        $this->position += $count;

        return $result;
    }

    /**
     * Запись в поток
     * @link http://www.php.net/manual/ru/streamwrapper.stream-write.php
     * @param string $data эти данные должны передаваться потоку уровнем ниже
     * @return int количество успешно записанных байт или 0, если ничего записать не удалось
     */
    public function stream_write($data)
    {
        return 0;
    }

    /**
     * Определение текущей позиции потока
     * @link http://www.php.net/manual/ru/streamwrapper.stream-tell.php
     * @internal
     * @return int
     */
    public function stream_tell()
    {
        return $this->position;
    }

    /**
     * Проверяет достижение конца файла по файловому указателю
     * @link http://www.php.net/manual/ru/streamwrapper.stream-eof.php
     * @return bool должен вернуть TRUE, если позиция чтения/записи находится в конце потока и
     * доступных для чтения данных больше нет. В остальных случаях возвращается FALSE.
     */
    public function stream_eof()
    {
        return $this->position >= $this->length;
    }

    /**
     * Перемещение на заданную позицию в потоке
     * @link http://www.php.net/manual/ru/streamwrapper.stream-seek.php
     * @param int $offset cмещение в потоке, на которое требуется переместиться
     * @param int $whence тип смещения (SEEK_SET, SEEK_CUR, SEEK_END)
     * @return bool успех операции
     */
    public function stream_seek($offset, $whence = SEEK_SET)
    {
        switch ($whence) {
            case SEEK_SET:
            {
                if ($this->isValidOffset($offset)) {
                    $this->position = $offset;

                    return true;
                } else {
                    return false;
                }
            }

            case SEEK_CUR:
            {
                if ($offset >= 0) {
                    $this->position += $offset;

                    return true;
                } else {
                    return false;
                }
            }

            case SEEK_END:
            {
                if ($this->isValidOffset($this->position + $offset)) {
                    $this->position = $this->length + $offset;

                    return true;
                } else {
                    return false;
                }
            }
        }
        return false;
    }

    /**
     * Сохранение данных вывода
     * @link http://www.php.net/manual/ru/streamwrapper.stream-flush.php
     * @return bool возвращать TRUE, если кэшированые данные успешно сохранены (или их вообще нет), либо FALSE, если данные не могут быть сохранены.
     */
    public function stream_flush()
    {
        return true;
    }

    /**
     * Закрывает поток, если в процессе работы стрима возникло исключение, оно будет выброшено повторно здесь
     * @link http://www.php.net/manual/ru/streamwrapper.stream-close.php
     * @return void
     */
    public function stream_close()
    {
    }

    /**
     * Получение информации о файле
     * @link http://www.php.net/manual/ru/streamwrapper.url-stat.php
     * @param string $path путь к файлу или его URL
     * @param int $flags дополнительные флаги
     * @return array
     */
    public function url_stat($path, $flags)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function stream_stat()
    {
        return [];
    }

    /**
     * Проверяет не вышел ли $offset за рамки общего размера потока
     * @param int $offset
     * @return bool
     */
    protected function isValidOffset($offset)
    {
        return ($offset >= 0) && ($offset < $this->length);
    }

    /**
     * Возвращает сервис потоков.
     * @throws RequiredDependencyException если сервис не был внедрен
     * @return IStreamService
     */
    protected function getStreamService()
    {
        if (is_null(self::$streamServices)) {
            throw new RequiredDependencyException('Stream service is not injected.');
        }

        return self::$streamServices;
    }

}
