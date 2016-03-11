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
namespace Crypt\Database\Types;

use Crypt\Database\Types\CryptType;
use Cake\Database\Driver;
use Cake\Database\Type;

/**
 * Usage:
 * In config/bootstrap.php, add
 *      Type::map('cryptJson', '\App\Database\Type\CryptJsonType');
 *
 * In your Table::initialize(), use
 *      $this->schema()->columnType('your_field', 'cryptJson');
 *
 * to map the field to a CryptJsonType
 *
 *
 * If you want to display a cryptJson Field as string, remember to json_encode
 * with the JSON_UNESCAPED_UNICODE flag
 *
 */
class CryptJsonType extends CryptType
{

    /**
     * from database to PHP conversion
     *
     * @param string $value     the value
     * @param Driver $driver    the driver
     * @return array
     */
    public function toPHP($value, Driver $driver)
    {
        if ($value === null) {
            return null;
        }
        $value = parent::toPhp($value, $driver);
        return json_decode($value, true);
    }

    /**
     * Convert request data into an array
     *
     * @param mixed $value Request Data
     * @return mixed
     */
    public function marshal($value)
    {
        if (is_array($value)) {
            $array = [];
            foreach ($value as $key => $valueArray) {
                if (!empty($valueArray['key']) && !empty($valueArray['value'])) {
                    $array[ $valueArray['key'] ] = $valueArray['value'];
                }
            }
            $value = $array;
        }
        return $value;
    }

    /**
     * from PHP to database conversion
     *
     * @param array|string $value   the value
     * @param Driver $driver        the driver
     * @return array
     */
    public function toDatabase($value, Driver $driver)
    {
        $value = json_encode($value, JSON_UNESCAPED_UNICODE);
        return parent::toDatabase($value, $driver);
    }
}
