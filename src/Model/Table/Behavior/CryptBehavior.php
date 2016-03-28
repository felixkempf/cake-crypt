<?php
/**
 * Copyright (c) Felix Kempf 2016
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Felix Kempf 2016
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Crypt\Model\Behavior;

use Cake\ORM\Behavior;
use Cake\ORM\Query;

class CryptBehavior extends Behavior
{

    public function findEncrypted(Query $query, array $options)
    {
        if ($query->count() === 0) {
            return [];
        }
        $Model = $query->repository();
        $all = $query->limit(1000)->all();

        if (!empty($options['where']) ) {
            foreach ($options['where'] as $fieldname => $fieldvalue) {
                if (strpos(substr($fieldname, -6), 'IS') !== false) {
                    $fieldname = str_replace(['IS', ' '], '', $fieldname);
                }
                if (strpos(substr($fieldname, -6), 'NOT') !== false) {
                    $fieldname = str_replace(['NOT'], '!', $fieldname);
                }
                if (strpos(substr($fieldname, -1), '!') !== false) {
                    $fieldname = substr($fieldname, 0, -1);
                    $all = $all->reject(function($value, $key) use ($fieldname, $fieldvalue) {
                        return $value->$fieldname == $fieldvalue;
                    });
                } else {
                    $all = $all->match([$fieldname => $fieldvalue]);
                }
            }
        }
        $ids = $all->extract('id')->toArray();
        if (count($ids) === 0) {
            return [];
        }
        $contain = empty($options['contain_']) ? [] : $options['contain_'];
        $select = (empty($options['select'])) ? [] : $options['select'];
        $findOptions = (empty($options['findOptions'])) ? [] : $options['findOptions'];

        $type = 'all';
        if (isset($options['list']) && $options['list'] === true) {
            $type = 'list';
        }

        $returnQuery = $Model->find($type, $findOptions)
            ->contain($contain)
            ->select($select)
            ->where([
                $Model->alias() . '.' . $Model->primaryKey() . ' IN' => $ids
            ]);

        if (!empty($options['sort'])) {
            $direction = (empty($options['direction'])) ? 'asc' : $options['direction'];
            $returnQuery->order([
                $options['sort'] => $direction
            ]);
        }

        return $returnQuery;
    }
}
