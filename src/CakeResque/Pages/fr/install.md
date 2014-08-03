# Installation {#page-header}

## Préalable {#requirements}

* [CakePHP 2.1+](http://cakephp.org/)
* Php 5.3+
* [Redis 2.2+](http://redis.io/)
* [PhpRedis](https://github.com/nicolasff/phpredis)

<small><i class="icon-bell"></i> **RECOMMANDATION**  
Bien que l'installation de phpredis est fortement conseillé, le plugin peut très bien fonctionner sans, et utilisera Redisent, déjà livré avec le plugin.</small>

<hr/>

## Installation {#install}

Rajouter *CakeResque* comme une dépendence dans votre fichier composer.json

~~~ .language-json
{
	"require": {
		... vos autres dépendences
		"kamisama/cake-resque": ">=4.1.0"
	}
}
~~~

puis executer `composer install`.

Si votre application ne contient pas encore de fichier composer.json, vous pouvez le génerer avec les commandes suivante, à executer dans votre console.

~~~ .language-bash
cd chemin/vers/votre/app
curl -s https://getcomposer.org/installer | php
php composer.phar require --no-update kamisama/cake-resque:4.1.0
php composer.phar config vendor-dir Vendor
php composer.phar install
~~~

Ces commandes vont
* Installer Composer
* Générer le fichier composer.json
* Installer le plugin

<hr/>


## Configuration {#config}

### Charger le plugin dans votre application CakePHP {#config-cakephp}

* Charger votre plugin dans <code><i class="icon-folder-open for-code"></i> app/Config/bootstrap.php</code>

~~~ .language-php
CakePlugin::load(array( # ou CakePlugin::loadAll(array(
	'CakeResque' => array('bootstrap' => true)
));
~~~

* Créer le fichier <code><i class="icon-file for-code"></i> AppShell.php</code> dans le dossier <code><i class="icon-folder-open for-code"></i> app/Console/Command</code>, si celui n'existe pas encore.

* Ajouter la fonction suivante dans <code><i class="icon-file for-code"></i> AppShell.php</code>

~~~ .language-php
public function perform() {
	$this->initialize();
	$this->loadTasks();
	return $this->runCommand($this->args[0], $this->args);
}
~~~

<h6><i class="icon-file"></i> Fichier AppShell.php final</h6>
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

<br>

* Si ce n'est deja fait, charger le autoloader de composer dans votre application, en rajoutant la ligne suivante à la fin de <code><i class="icon-file for-code"></i> app/Config/core.php</code>

~~~ .language-php
require_once dirname(__DIR__) . '/Vendor/autoload.php';
~~~

### Configurer CakeResque {#config-cakeresque}

Tous les paramètres peuvent être définis dans le fichier bootstrap du plugin. Referez-vous a la <a href="https://github.com/kamisama/Cake-Resque/blob/master/Config/bootstrap.php">documentation</a> a l'intérieur du fichier.

Il est recommandé de ne pas editer directement les fichiers dans le dossier Config du plugin, mais de créer vos propres fichier configs.

Example

~~~ .language-php
# app/Config/cakeresque_config.php
Configure::write('CakeResque.Redis.host', 'mylocalhost');
~~~

Vous devez alors activer le plugin avec cette commande

~~~ .language-php
CakePlugin::load(array( # or CakePlugin::loadAll(array(
	'CakeResque' => array('bootstrap' => array(
		'bootstrap_config',
		'../../../Config/cakeresque_config', # Chemin vers votre propre fichier config
		'bootstrap')
	)
));
~~~

Dans l'exemple ci-dessus, votre configuration se trouve dans <code><i class="icon-file for-code"></i> app/Config/cakeresque_config.php</code>. Vous n'avez pas besoin de `require` ou d'`include` le fichier config original.<br>
Séparer votre propre config dans un fichier séparé, en dehors du plugin, facilitera les futures mise à jour du plugin.

<hr/>

## Mise a jour {#update}

Dans votre console, simplement executer

~~~ .language-bash
cd chemin/vers/votre/app

php composer.phar update
# Ou bien
composer update
~~~

<hr/>

## DebugKit {#debugkit}

Le log des activités de CakeResque peut être visualisable grâce au panneau "Resque", disponible en installant <a href="https://github.com/kamisama/DebugKitEx">DebugKitEx</a>.

<img src="/img/debugkit_jobs.png" width=940 height=336 alt="DebugKit Resque panel" title="DebugKit Resque panel" />

