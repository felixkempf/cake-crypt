<?php
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
                if ($all->first()->has($fieldname)) {
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
            ->limit(1000) // just to overwrite any other limit
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
