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
~~~

Or you can just download the [latest release archive](https://github.com/kamisama/Cake-Resque/zipball/master), and just uncompress it in your <code><i class="icon-folder-open for-code"></i> app/Plugin</code> folder. Make sure that the plugin folder is named <b>CakeResque</b>.

### Install dependencies {#install-dependencies}

This plugin uses some external libraries, that you can install via [Composer](http://getcomposer.org/doc/00-intro.md).

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

* Load the Plugin in your <code><i class="icon-folder-open for-code"></i> app/Config/bootstrap.php</code>

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
	$this->{array_shift($this->args)}();
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
		$this->{array_shift($this->args)}();
	}
}
~~~
</div></div>

### Configure CakeResque {#config-cakeresque}

All configuration are done in the plugin bootstrap file. Documentation <a href="https://github.com/kamisama/Cake-Resque/blob/master/Config/bootstrap.php">inside</a>.

<hr/>

## Update {#update}


1. Backup your <code><i class="icon-file for-code"></i> bootstrap.php</code> file, in <code><i class="icon-folder-open for-code"></i> CakeResque/Config/</code>
2. Download the new version and replace the *CakeResque* folder with the new one
3. Restore your <code><i class="icon-file for-code"></i> bootstrap.php</code>.
		Read the versions releases notes (in the changelog) for new changes in bootstrap.
4. <a href="#install-dependencies">Re-install all Composer dependencies</a>


Occasionally, you should update the dependencies, even if there's no plugin new version.

~~~ .language-bash
cd app/Plugin/CakeResque
php composer.phar update
~~~

<hr/>

## DebugKit {#debugkit}

You can view job queuing log by installing the <a href="https://github.com/kamisama/DebugKitEx">DebugKitEx</a> panels.

<img src="/img/debugkit_jobs.png" width=940 height=336 alt="DebugKit Resque panel" title="DebugKit Resque panel" />

