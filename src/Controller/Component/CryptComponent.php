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
namespace Crypt\Controller\Component;

use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Utility\Hash;

class CryptComponent extends Component
{
    // The other component the component uses
    public $components = ['Auth'];

    // Execute any other additional setup for the component.
    public function initialize(array $config)
    {
    }

    /**
     * called after the controller’s beforeFilter method but before the controller
     * executes the current action handler.
     * Writes the hashed user password into the config and handles pagination url queries
     */
    public function startup(Event $event)
    {
        if (!empty($this->Auth->user())) {
            Configure::write('Caches.key', $this->Auth->user('password'));
        }
        $this->__translatePaginationQueries($event->subject()->paginate);
    }

    /**
     * sets and merges given options into the paginate options property or the
     * encrypted finder options therein.
     * Use the keys 'where' and 'contain_' to set conditions and contain options
     * to the custom encrypted finder
     *
     * @param array $options
     * @return void
     */
    public function setPaginationSettings($options)
    {
        if (!empty($options['conditions'])) {
            if (empty($options['where'])) {
                $options['where'] = [];
            }
            $options['where'] += $options['conditions'];
            unset($options['conditions']);
        }

        if (!empty($options['contain'])) {
            if (empty($options['contain_'])) {
                $options['contain_'] = [];
            }
            $options['contain_'] += $options['contain'];
            unset($options['contain']);
        }

        $controller = $this->_registry->getController();
        $controller->paginate = Hash::merge([
            'finder' => [
                'encrypted' => $options
            ],
        ], $controller->paginate);
    }

    /**
     * translates url queries from pagination into pagination custom finder options
     */
    private function __translatePaginationQueries($paginate)
    {
        $customFinderOptions = [];
        if ($this->request->action === 'index') {
            if (!empty($this->request->query('sort'))) {
                $customFinderOptions['sort'] = $this->request->query('sort');
            }
            if (!empty($this->request->query('direction'))) {
                $customFinderOptions['direction'] = $this->request->query('direction');
            }
            if (!empty($this->request->query('conditions'))) {
                $customFinderOptions['conditions'] = $this->request->query('conditions');
            }
        }
        $this->_registry->getController()->paginate += [
            'finder' => [
                'encrypted' => $customFinderOptions
            ],
        ];
    }
}
