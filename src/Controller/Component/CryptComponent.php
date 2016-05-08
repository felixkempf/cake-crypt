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
    public $components = ['App.Auth'];

    /**
     * called after the controller’s beforeFilter method but before the controller
     * executes the current action handler.
     *
     * Writes the hashed user password into the config and handles pagination url queries
     *
     * Also sets pagination limit for the finder to 1000 to make pagination impossible
     * No really, strange behavior from PaginationHelper, feel free fo fixin
     */
    public function startup(Event $event)
    {
        $this->Auth->config('authorize', ['controller']);
        if (!empty($this->Auth->user())) {
            Configure::write('Caches.key', $this->Auth->user('password'));
        }
        $this->__translatePaginationQueries();
        $this->setPaginationSettings([
            'limit' => 1000
        ]);
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
     * check if a configured amount of seconds past since the time stamp in the user
     * session was last set / renewed
     *
     * @return bool
     */
    private function __checkSessionAge()
    {
        $session = $this->request->session()->read();
        if (!empty($session['Auth']['User']['time'])
            && (
            ($session['Config']['time'] - $session['Auth']['User']['time'])
            > Configure::read('Crypt.sessionCheckThreshold')
            )
        ) {
            return true;
        }
        return false;
    }

    /**
     * read yourself
     *
     * @return void
     */
    public function randomlyAskForPassword()
    {
        if (Configure::read('debug') === false // plese not while developing
            && $this->request->is('get') // only get so no post is lost
            && $this->__checkSessionAge() // waits configured time after successfull logins
            && $this->response->statusCode() === 200 // excludes especially 403 Not Authorized
            && strpos($this->request->here(), '/users/login') === false // avoid stacking of redirectUrl
            && mt_rand(0, 9) === 0 // even then  statistically only every 10th case
        ) {
            $this->Flash->info('PASWWORT BITTE!'); // scare
            // destroy authorization
            $this->request->session()->delete('Auth.User.role');
            $this->request->session()->delete('Auth.User.password');
            // redirect to login, not logout, to keep rest of session data intact
            $url = $this->Auth->_config['loginAction'];
            // save the initial request url into redirect parameter
            $url['redirectUrl'] = $this->request->here();
            return $this->redirect($url);
        }
    }

    /**
     * translates url queries from pagination into pagination custom finder options
     *
     * @return void
     */
    private function __translatePaginationQueries()
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
                $customFinderOptions['where'] = $this->request->query('conditions');
            }
        }
        $this->_registry->getController()->paginate += [
            'finder' => [
                'encrypted' => $customFinderOptions
            ],
        ];
    }
}
