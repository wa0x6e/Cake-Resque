# Installation {#page-header}

## Requirements {#requirements}

* [CakePHP 2.1+](http://cakephp.org/)
* Php 5.3+
* [Redis 2.2+](http://redis.io/)
* [PhpRedis](https://github.com/nicolasff/phpredis)

<small><i class="icon-bell"></i> **RECOMMENDATION**
Installing PhpRedis is strongly recommended, but if you can't, it will fallback to Redisent, another Redis API for php, shipped with the plugin.</small>

<hr/>

## Installation {#install}

Add *CakeResque* as a dependency in your composer.json

~~~ .language-json
{
	"require": {
		... your other dependency
		"kamisama/cake-resque": ">=4.1.0"
	}
}
~~~

then run `composer install`.

If your application does not contain a composer.json yet, run the following command in your shell

~~~ .language-bash
cd path/to/your/app
curl -s https://getcomposer.org/installer | php
php composer.phar require --no-update kamisama/cake-resque:4.1.0
php composer.phar config vendor-dir Vendor
php composer.phar install
~~~

That will take care of installing composer, generating the composer.json, and installing the plugin.

<hr/>


## Configuration {#config}

### Load the plugin into CakePHP {#config-cakephp}

* Load the Plugin with its default configuration in your <code><i class="icon-folder-open for-code"></i> app/Config/bootstrap.php</code>

~~~ .language-php
CakePlugin::load(array( # or CakePlugin::loadAll(array(
	'CakeResque' => array('bootstrap' => true)
));
~~~

* Create the <code><i class="icon-file for-code"></i> AppShell.php</code> file in <code><i class="icon-folder-open for-code"></i> app/Console/Command</code>, if it doesn't exist

* Add the following method to <code><i class="icon-file for-code"></i> AppShell.php</code>

~~~ .language-php
public function perform() {
	$this->initialize();
	return $this->runCommand($this->args[0], $this->args);
}
~~~

<h6><i class="icon-file"></i> Final AppShell.php</h6>
<div class="example"><div markdown=1>
~~~ .language-php
<?php
App::uses('AppModel', 'Model');
class AppShell extends Shell {
	public function perform() {
		$this->initialize();
		return $this->runCommand($this->args[0], $this->args);
	}
}
~~~
</div></div>

<br>

* If not already done, load composer autoloader into your application, by adding the following line at the end of <code><i class="icon-file for-code"></i> app/Config/core.php</code>

~~~ .language-php
require_once dirname(__DIR__) . '/Vendor/autoload.php';
~~~

### Configure CakeResque {#config-cakeresque}

All settings are well documented inside the plugin <code><i class="icon-file for-code"></i> <a href="https://github.com/kamisama/Cake-Resque/blob/master/Config/config.php">config.php</a></code> file.

It's recommended that you don't edit the default config file, but create another one overriding the default settings.

Example

~~~ .language-php
# app/Config/cakeresque_config.php
Configure::write('CakeResque.Redis.host', 'mylocalhost');
~~~

Load the plugin with

~~~ .language-php
CakePlugin::load(array( # or CakePlugin::loadAll(array(
	'CakeResque' => array('bootstrap' => array(
		'bootstrap_config',
		'../../../Config/cakeresque_config', # Path to your own config file
		'bootstrap')
	)
));
~~~

In this example, the config file is <code><i class="icon-file for-code"></i> app/Config/cakeresque_config.php</code>. You don't have to `include` or `require` the original Config file.<br>
Keeping your own setting in your own file, outside of the plugin directory is a good pratice, as it allows the smoothest plugin update experience.

<hr/>

## Update {#update}

Just run

~~~ .language-bash
cd path/to/your/app

php composer.phar update
# Or
composer update
~~~


<hr/>

## DebugKit {#debugkit}

You can view job queuing log by installing the <a href="https://github.com/kamisama/DebugKitEx">DebugKitEx</a> panels.

<img src="/img/debugkit_jobs.png" width=940 height=336 alt="DebugKit Resque panel" title="DebugKit Resque panel" />

