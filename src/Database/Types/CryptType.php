<?php
namespace App\Database\Types;

use Cake\Core\Configure;
use Cake\Database\Driver;
use Cake\Database\Type;
use Cake\Network\Request;
use Cake\Utility\Security;

/**
 * Usage:
 * In config/bootstrap.php, add
 *      Type::map('crypt', '\CkTools\Database\Type\CryptType');
 *
 * In your Table::initialize(), use
 *      $this->schema()->columnType('your_field', 'crypt');
 *
 * to map the field to a CryptType
 */
class CryptType extends Type
{

    /**
     * check if the key for en-/decryption is present
     * @return void
     * @throws \Exception if the key for en-/decryption is missing
     */
    private function _checkKey()
    {
        if (empty(Configure::read('Caches.key'))) {
            throw new \Exception("Missing key");
        }
    }

    /**
     * from database to PHP conversion
     *
     * @param string $value     the value
     * @param Driver $driver    the driver
     * @return array
     */
    public function toPHP($value, Driver $driver)
    {
        $this->_checkKey();
        if (empty($value)) {
            return null;
        }
        return Security::decrypt(utf8_decode($value), Configure::read('Caches.key'));
    }

    /**
     * Convert request data into an array
     *
     * @param mixed $value Request Data
     * @return mixed
     */
    public function marshal($value)
    {
        if (is_array($value) || $value === null) {
            return null;
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
        $this->_checkKey();
        if (empty($value)) {
            return null;
        }
        return utf8_encode(Security::encrypt($value, Configure::read('Caches.key')));
    }
}
