<?php

require_once APP . 'Plugin' . DS . 'Resque' . DS . 'Vendor' . DS . 'php-resque' . DS . 'lib' . DS . 'Resque.php';

class ResqueComponent extends Component {

	public function __construct(ComponentCollection $collection, $settings = array()) {
		Resque::setBackend(Configure::read('Resque.Redis.host') . ':' . Configure::read('Resque.Redis.port'));
	}

}
