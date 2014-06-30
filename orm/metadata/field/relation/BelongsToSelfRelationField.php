<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\orm\metadata\field\relation;

use umi\orm\exception\UnexpectedValueException;

/**
 * Класс поля хранителя связи.
 */
class BelongsToSelfRelationField extends BelongsToRelationField
{
    /**
     * {@inheritdoc}
     */
    protected function applyConfiguration(array $config)
    {
        if (!isset($config['collectionName']) || !is_string($config['collectionName'])) {
            throw new UnexpectedValueException($this->translate(
                'Self relation field configuration should contain collection name and name should be a string.'
            ));
        }
        $this->targetCollectionName = $config['collectionName'];
    }

}
