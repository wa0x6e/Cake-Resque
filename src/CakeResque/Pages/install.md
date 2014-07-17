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


### Download the plugin {#install-plugin}

In the command line :

~~~ .language-bash
cd your-application/app/Plugin
git clone git://github.com/kamisama/Cake-Resque.git CakeResque

# OR install as a submodule
git submodule add git://github.com/kamisama/Cake-Resque.git CakeResque
~~~

Or you can just download the [latest release archive](https://github.com/kamisama/Cake-Resque/zipball/master), and extract it in your <code><i class="icon-folder-open for-code"></i> app/Plugin</code> folder. Make sure that the plugin folder is named <b>CakeResque</b>.

### Install dependencies {#install-dependencies}

This plugin uses some external libraries, that you have to install via [Composer](http://getcomposer.org/doc/00-intro.md).

1. 	With the command line, go inside the CakeResque folder
	~~~ .language-bash
	cd your/application/Plugin/CakeResque
	~~~

2. 	Download Composer, if not already done

	~~~ .language-bash
	curl -s https://getcomposer.org/installer | php
	~~~

3. 	Install dependencies

	~~~ .language-bash
	php composer.phar install
	~~~



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
	$this->loadTasks();
	return $this->runCommand($this->args[0], $this->args);
}
~~~

<h6><i class="icon-file"></i> Final AppShell.php</h6>
<div class="example"><div markdown=1>
~~~ .language-php
<?php
App::uses('AppModel', 'Model');
class AppShell extends Shell
{
	public function perform()
	{
		$this->initialize();
		$this->loadTasks();
		$this->{array_shift($this->args)}();
	}
}
~~~
</div></div>

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

Keeping your own setting in your own file, outside of the plugin directory is a good pratice, as it allows the smoothest plugin upgrade experience.

<hr/>

## Update {#update}

### Updating CakeResque

#### The classic way

1. Backup any files in <code><i class="icon-folder-open for-code"></i> CakeResque/Config/</code> that you may have edited
2. Download the new version of CakeResque and replace the *CakeResque* folder with the new one
3. Restore your backup files.
4. <a href="install#install-dependencies">Re-install all Composer dependencies</a>

#### The pro way

**Only if you have installed CakeResque with git, and placed your config file outside of the plugin directory**

1. Update CakeResque

~~~ .language-bash
cd Plugin/CakeResque
git pull

# OR, if installed as a submodule
cd Plugin
git submodule update --init
~~~

2. <a href="install#update-dependencies">Update all Composer dependencies</a>

~~~ .language-bash
cd Plugin/CakeResque
php composer.phar update
# Or composer update
~~~


<hr/>

## DebugKit {#debugkit}

You can view job queuing log by installing the <a href="https://github.com/kamisama/DebugKitEx">DebugKitEx</a> panels.

<img src="/img/debugkit_jobs.png" width=940 height=336 alt="DebugKit Resque panel" title="DebugKit Resque panel" />

