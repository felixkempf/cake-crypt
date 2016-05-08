<?php
use Cake\Core\Configure;
use Cake\Utility\Hash;

// Load and merge default with app config
$config = require 'crypt.default.php';
$config = $config['Crypt'];
if ($appMonitorConfig = Configure::read('Crypt')) {
    $config = Hash::merge($config, $appMonitorConfig);
}
Configure::write('Crypt', $config);
