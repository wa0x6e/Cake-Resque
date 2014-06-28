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


### Téléchargement du plugin {#install-plugin}

A partir de votre console :
	
~~~ .language-bash
cd chemin-de-votre-application/app/Plugin
git clone git://github.com/kamisama/Cake-Resque.git CakeResque
~~~

Ou vous pouvez simplement télécharger la [dernière version](https://github.com/kamisama/Cake-Resque/zipball/master), et la décompresser dans le dossier <code><i class="icon-folder-open for-code"></i> app/Plugin</code>. Assurez-vous que le dossier se nomme bien <b>CakeResque</b>.

### Installation des dépendances  {#install-dependencies}

Ce plugin utilise certaines librairies externes, installable via [Composer](http://getcomposer.org/doc/00-intro.md).

1. 	A partir de votre console, naviguez vers le dossier CakeResque
	~~~ .language-bash
	cd chemin-de-votre-application/app/Plugin/CakeResque
	~~~

2. 	Télécharger Composer, si vous ne l'avez pas déjà installé globalement

	~~~ .language-bash
	curl -s https://getcomposer.org/installer | php
	~~~

3. 	Installer les dépendances

	~~~ .language-bash
	php composer.phar install
	~~~



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
	$this->{array_shift($this->args)}();
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
		$this->{array_shift($this->args)}();
	}
}
~~~
</div></div>

### Configurer CakeResque {#config-cakeresque}

Tous les parametres peuvent être définis dans le fichier bootstrap du plugin. Referez-vous a la <a href="https://github.com/kamisama/Cake-Resque/blob/master/Config/bootstrap.php">documentation</a> a l'intérieur du fichier.

<hr/>

## Mise a jour {#update}


1. Sauvegarder le fichier <code><i class="icon-file for-code"></i> bootstrap.php</code>, se trouvant dans <code><i class="icon-folder-open for-code"></i> CakeResque/Config/</code>
2. Télecharger la nouvelle version du plugin et remplacer le dossier *CakeResque* par le nouveau
3. Restaurer votre <code><i class="icon-file for-code"></i> bootstrap.php</code>.
		Veuillez lire attentivement les notes de mise a jour dans le changelog pour des changement éventuelles apportes au fichier bootstrap lui-même.
4. <a href="#install-dependencies">Re-installer toutes les dépendances</a>

Vous pouvez de temps a autre mettre a jour les dépendances, ce, même si aucune nouvelle version du plugin ne sort.

~~~ .language-bash
cd app/Plugin/CakeResque
php composer.phar update
~~~

<hr/>

## DebugKit {#debugkit}

Le log des activités de CakeResque peut être visualisable grâce au panneau "Resque", disponible en installant <a href="https://github.com/kamisama/DebugKitEx">DebugKitEx</a>.

<img src="/img/debugkit_jobs.png" width=940 height=336 alt="DebugKit Resque panel" title="DebugKit Resque panel" />

