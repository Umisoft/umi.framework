<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\orm\object\property\calculable;

use umi\orm\object\property\Property;

/**
 * Класс свойства с вычисляемым значением
 */
class CalculableProperty extends Property implements ICalculableProperty
{

    /**
     * {@inheritdoc}
     */
    public function recalculate()
    {
        $this->isModified = true;
        $this->object->setIsModified();

        return $this;
    }
}
