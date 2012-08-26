<?php

require_once App::pluginPath('Resque') . 'vendor' . DS . Configure::read('Resque.Resque.lib') . DS . 'lib' . DS . 'Resque.php';

class ResqueComponent extends Component {

	public function __construct(ComponentCollection $collection, $settings = array()) {
		Resque::setBackend(Configure::read('Resque.Redis.host') . ':' . Configure::read('Resque.Redis.port'));
	}
}