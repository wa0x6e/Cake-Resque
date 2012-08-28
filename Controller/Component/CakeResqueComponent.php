<?php
/**
 * CakeResque Component file
 *
 * Make the Resque API available to the entire application
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @author        Wan Qi Chen <kami@kamisama.me>
 * @copyright     Copyright 2012, Wan Qi Chen <kami@kamisama.me>
 * @link          http://cakeresque.kamisama.me
 * @package       CakeResque
 * @subpackage	  CakeResque.Controller.Component
 * @since         0.5
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

require_once App::pluginPath('CakeResque') . 'vendor' . DS . Configure::read('CakeResque.Resque.lib') . DS . 'lib' . DS . 'Resque.php';

class CakeResqueComponent extends Component {

	public function __construct(ComponentCollection $collection, $settings = array()) {
		Resque::setBackend(Configure::read('CakeResque.Redis.host') . ':' . Configure::read('CakeResque.Redis.port'));
	}
}