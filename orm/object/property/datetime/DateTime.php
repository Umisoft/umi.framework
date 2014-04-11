<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\orm\object\property\datetime;

/**
 * Значение свойства типа DateTime.
 */
class DateTime extends \DateTime
{
    /**
     * @var bool $timeIsSet признак того, что время было задано
     */
    private $timeIsSet;
    /**
     * @var int $oldValue выставленное время
     */
    private $oldValue;
    /**
     * @var IDateTimeProperty $property свойство, значением которого является DateTime
     */
    private $property;

    /**
     * {@inheritdoc}
     * @param IDateTimeProperty $dateTimeProperty
     */
    public function __construct($time = 'now', \DateTimeZone $timezone = null, IDateTimeProperty $property)
    {
        parent::__construct($time, $timezone);

        $this->timeIsSet = (strlen($time) != 0);
        $this->oldValue = $this->getTimestamp();
        $this->property = $property;

    }

    /**
     * Возвращает признак того было ли установлено время.
     * @return bool
     */
    public function getIsTimeSet()
    {
        return $this->timeIsSet;
    }

    /**
     * Сбрасывает значение.
     * @return $this
     */
    public function clear()
    {
        $this->timeIsSet = false;
        $this->oldValue = 0;
        $this->property->update();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function modify($modify)
    {
        $result = parent::modify($modify);
        $this->update();

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function add($interval)
    {
        $result = parent::add($interval);
        $this->update();

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function setTimezone($timezone)
    {
        // php bugfix https://bugs.php.net/bug.php?id=45543
        $timeZone = new \DateTimeZone($timezone->getName());

        $result = parent::setTimezone($timeZone);
        $this->update();

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function setTime($hour, $minute, $second = 0)
    {
        $result = parent::setTime($hour, $minute, $second);
        $this->update();

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function setDate($year, $month, $day)
    {
        $result = parent::setDate($year, $month, $day);
        $this->update();

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function setISODate($year, $week, $day = 1)
    {
        $result = parent::setISODate($year, $week, $day);
        $this->update();

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function setTimestamp($unixtimestamp)
    {
        $result = parent::setTimestamp($unixtimestamp);
        $this->update();

        return $result;
    }


    /**
     * Установить текущие дату и время в качестве значения свойства.
     */
    public function setCurrent()
    {
        $this->setTimestamp(time());
    }

    /**
     * Оповещает свойство о смене статуса измененности.
     */
    private function update()
    {
        if (!$this->timeIsSet || $this->oldValue != $this->getTimestamp()) {
            $this->oldValue = $this->getTimestamp();
            $this->timeIsSet = true;
            $this->property->update();
        }
    }

}
 