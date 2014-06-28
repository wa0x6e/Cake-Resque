<div class="alert"><b>ATTENTION</b><br/> Cette page n'est pas encore entierement traduite en francais. <a href="https://github.com/kamisama/Cake-Resque/blob/master/CONTRIBUTING.md">Aidez-nous a la traduire</a>.</div>

# Usage {#usage}

Avant de mettre des jobs en queue, vous devez d'abord savoir comment creer les workers qui surveilleront vos queues, ainsi que les classes contenant chacun de vos jobs.


## Gestion des workers {#workers}

Les workers se gerent uniquement a partir de la [console de Cake](http://book.cakephp.org/2.0/en/console-and-shells.html).  
Referez-vous a [la documentation des commandes](/commands) pour plus d'information sur l'utilisation de la console de CakeResque.

Pour **demarrer** un worker, en utilsant les parametres par defaut definis dans le bootstrap

	./cake CakeResque.CakeResque start
	
Pour **demarrer** un worker, avec vos propres parametres

	# Demarrer un worker qui travaillera toutes les 15 secondes, et surveillant la queue 'mail'
	./cake CakeResque.CakeResque start --interval 15 --queue mail
	
Vous pouvez aussi **arreter** les workers

	./cake CakeResque.CakeResque stop 
	# Rahouter --all pour arreter tous les workers en meme temps
	
*<small>Si plus d'un worker est demarre, une liste de worker a arreter sera proposee.</small>*

Pour **mettre en pause**/**continuer** les workers

	./cake CakeResque.CakeResque pause 
	./cake CakeResque.CakeResque resume
	# Rajouer --all pour mettre en pause/continuer tous les workers en meme temps
	
*<small>Si plus d'un worker est en pause, une liste de worker a continuer sera proposee. De meme pour l'inverse.</small>*

Pour consulter les **statistiques**

	./cake CakeResque.CakeResque stats
	
Pour **suivre** le journal

	./cake CakeResque.CakeResque tail
	
*<small>Si plus d'un journal est disponible, une liste de worker a arreter sera proposee.</small>*

<div class="alert alert-info" markdown="1"><i class="icon-lightbulb"></i> **NOTES**  
Chacune de vos queues doivent etre surveillees par au moins un worker. Un worker peut surveiller plusieurs queues, et plusieurs workers peuvent surveiller la meme queue.
</div>

Une fois que vos workers sont demarres, vous pouvez commencer a y mettre vos jobs.


## Creer les classes jobs {#jobs}

Mais avant d'y mettre nos jobs, nous devons deja avoir un job.

### Qu'est ce qu'un job ?

Un job est represente par une classe que le worker instanciera, avec les arguments que vous lui fournirez lors de la creation du job.

Notre but etant de dire a CakePHP que l'on ne veut pas executer la fonction lamda du Model beta immediatement, mais de le remettre a plus tard.
Un job est une sorte de porte d'entree, qui executera directement la fonction lambda du Model beta directement, sans avoir a repasser par toutes les etapes precedentes.

### Comment creer une classe job

Toutes les classes jobs sont juste des classe de shell banales de CakePHP, se trouvant soit dans
* <code><i class="icon-folder-open for-code"></i> app/Console/Command</code>
* soit dans <code><i class="icon-folder-open for-code"></i> app/Plugin/PluginName/Console/Command</code>

Vous n'avez pas besoin de creer une classe par job, tous les jobs du meme modele peuvent etre regroupes dans la meme classe job.

#### Exemple de classe job

~~~ .language-php
// app/Console/Command/FriendShell.php
// -----------------------------------
<?php
App::uses('AppShell', 'Console/Command');
class FriendShell extends AppShell
{
	public $uses = array('Friend');

	/**
	 * Notre premier job, pour trouver de nouveaux amis pour un utilisateur
	 **/
	public function findNewFriend() {
		// Vous pouvez acceder aux arguments renseignes lors de 
		// la creation du job via $this->args
		$this->Friend->findNewFriends($this->args[0], $this->args[1]);
	}
	
	/**
	 * Un deuxieme job, pour notifier vos amis
	 **/
	public function notifyFriend() {
		$this->Friend->notifyFriends($this->args[0]);
	}
}
~~~

Toutes vos classes shell doivent heriter de  `AppShell`.   
Votre classe `AppShell` doit implement la methode `perform()`, comme decrit dans le guide d'installation, sinon vos job ne pouront etre executes par les workers.

<div class="alert alert-error" markdown=1><i class="icon-exclamation-sign"></i> Redemarrer vos workers **a chaque fois** que vous modifier vos classes jobs</div>

<div class="alert alert-info" markdown=1>**<i class="icon-question-sign"></i>Pourquoi utiliser les classes shell comme classes jobs ?** <br/>
Parce qu'on ne peut directement executer une fonction d'une modele directement. Il faut absolument passer par le dispatcher, afin de charger les Controller, Behavior, etc … Passer par le shell nous fais tout cela automatiquement. De plus, vous pouvez executer vos classes jobbs directement dans la console Cake.</div>


## Mettre des Jobs en queue {#queueing}

Il existe 3 manieres de mettre les jobs en queue :

* Mettre le job **immediatement** en queue
* Mettre le job en queue **apres** un certain temps
* Mettre le job en queue **a** a un heure precise

Les 2 dernieres methodes servent a programmer un job pour une date future.


### Mettre un job en queue immediatement

~~~ .language-php
CakeResque::enqueue($queue, $jobClassName, $args, $track);
~~~

<table class="table">
	<tr>
		<th>Type</th>
		<th>Argument</th>
		<th>Description</th>
	</tr>
	<tr>
		<td markdown="1">*String*</td>
		<td markdown="1">`$queue`</td>
		<td markdown="1">Nom de la queue dans laquelle vous ajoutez le job</td>
	</tr>
	<tr>
		<td markdown="1">*String*</td>
		<td markdown="1">`$jobClassName`</td>
		<td markdown="1">Nom de la classe job.<br/>
La syntaxe plugin (`PluginName.ClassName`) est aussi acceptee.</td>
	</tr>
	<tr>
		<td markdown="1">*Array*</td>
		<td markdown="1">`$args`</td>
		<td markdown="1">Liste d'argument a passer au job<br>
Le premier element est le nom de la fonction de la classe job a executer. 
Les elements suivants seront passer comme arguments a ladite fonction, et seront disponibles
a l'interieur de celle-ci via la variable `$this->args`.</td>
	</tr>
	<tr>
		<td markdown="1">*Boolean*</td>
		<td markdown="1">`$track`</td>
		<td markdown="1">*Optionelle*, defaut : `false`<br/>
 Si le job doit etre suivi.<br/>
 Le suivi de job permet de connaitre le status du job, pour savoir si il est encore en queue, en cours d'execution, echoue, ou complete avec succes. Les status sont enregistres pendant 24 heures, puis supprimes. Si omise, la valeur definie dans le bootstrap sera utilisee.</td>
	</tr>
</table>

#### Exemple

<div class="example"><div markdown=1>
~~~ .language-php
CakeResque::enqueue(
	'default', 
	'FriendShell', 
	array('findNewFriends', 'John Doe', 'Ghana')
);
~~~

Cela va creer le job `FriendShell` avec les arguments `array('findNewFriends', 'John Doe', 'Ghana')` et l'ajoutera dans la queue `default`.

Une fois que le worker trouvera le job, il va instancier la classe **FriendShell**, puis executera la fonction **findNewFriends()** avec les arguments definis dans `$args` array accessible svia `$this->args`.  

Veuillez noter que a l'interieur de la fonction `findNewFriends()`, `$this->args` sera :  
`array('John Doe', 'Ghana')`  
Le premier indice (*findNewFriends*) qui correspond au nom de la fonction a executer a l'interieur de la classe, a ete supprimee.
</div></div>

## Planifier un Job {#scheduling}

### Mettre un job en queue a une date future

Vous pouvez specifier *quand* mettre un job en queue.

~~~ .language-php
CakeResque::enqueueAt($time, $queue, $class, $args, $track);
~~~

<table class="table">
	<tr>
		<th>Type</th>
		<th>Argument</th>
		<th>Description</th>
	</tr>
	<tr>
		<td markdown="1">*DateTime|int*</td>
		<td markdown="1">`$time`</td>
		<td markdown="1">Date a laquelle vous voulez mettre le job en queue. Peut etre un objet Datetime ou un timestamp.</td>
	</tr>
</table>

Les 4 arguments suivants sont les memes que ceux de `CakeResque::enqueue()`, decrit plus haut.

#### Exemple

<div class="example"><div markdown=1>
~~~ .language-php
CakeResque::enqueueAt(
	new DateTime('2012-01-26 15:56:23'),
	'default', 		// Nom de la queue
	'FriendShell', // Nom de la classe job
	array('findNewFriends', 'John Doe', 'Ghana') // Divers arguments
);
~~~
</div></div>

### Mettre un job en queue apres un certain temps

Vous pouvez aussi mettre un job en queue apres un certain temps, par exemple apres 5 minutes,
dans le case ou vous n'avez pas l'heure absolue, avec `CakeResque::enqueueIn()`. Cette fonction prends aussi 5 arguments :

~~~ .language-php
CakeResque::enqueueIn($wait, $queue, $class, $args, $track);
~~~

<table class="table">
	<tr>
		<th>Type</th>
		<th>Argument</th>
		<th>Description</th>
	</tr>
	<tr>
		<td markdown="1">*int*</td>
		<td markdown="1">`$wait`</td>
		<td markdown="1">Nombre de secondes a attendre avant de mettre le job en queue.</td>
	</tr>
</table>

De meme que pour `CakeResque:enqueueAt()`, les 4 derniers arguments sont les meme qu'avec `CakeResque::enqueue()`.


##### Exemple
<div class="example"><div markdown=1>

~~~ .language-php
CakeResque::enqueueIn(
	3600, 			// Mettre le job en queue apres 1 heure
	'default', 		// Nom de la queue
	'FriendShell', 	// Nom de la classe job
	array('findNewFriends', 'John Doe', 'Ghana') // Divers arguements
);
~~~

</div></div>

### Notes

La programmation des jobs est **desactivee** par defaut. Pour l'activer :

* Mettez la cle `CakeResque.Scheduler.enabled` a `true` dans le bootstrap
* Demarrer le *Scheduler Worker*

### Le Scheduler Worker

Planifier un job revient a mettre le job en queue dans une queue temporaire speciale.

Un worker special, le Scheduler Worker, surveillera cette queue specialle regulierement, et deplacera les jobs dans la bonne queue, quand leur temps sera venu.
Vous devez donc avoir le Scheduler Worker en marche. 

* Le Scheduler Worker peut etre demarre avec la commande [`startschedule`](commands#command-startscheduler). 

	~~~ .language-bash
	./cake CakeResque.CakeResque startscheduler
	~~~
  
	~~~ .language-bash
	# Vous pouvez aussi specifier le temps de pause, qui est par defaut 3 secondes
	./cake CakeResque.CakeResque startscheduler -i 5
	~~~
	
	Contrairement a la commande [`start`](commands#command-start), `-i` ets la seule option acceptee lors du demarrage du Scheduler Worker.
 
* Le Scheduler Worker est aussi automatiquement demarrer avec la commande [`load`](commands#command-load), aussi longtemps que la programmation de job sera activee.

<i class="icon-lightbulb"></i> **NOTES** : Seulement un Scheduler Worker peut tourner. Demarrer d'autres instances echouera. A part le demarrage, ce worker peut etre manipulee comme un worker regulier, avec les commandes [`stop`](commands#command-stop), [`pause`](commands#command-pause), [`resume`](commands#command-resume) et [`restart`](commands#command-restart).
	
### Limitation de la plannification de jobs

#### Scheduler Worker

Si le Scheduler Worker ne tourne pas pour quelle que raison que ce soit, tous les jobs programees s'accumuleront dans la queue de planification, jusqu'a ce que le Scheduler Worker tourne.  
Tous les jobs "expires" seront immediatement executes, aucun job ne sera perdu.

#### Precision

Planifier un job en queue a un temps X ne signifie pas qu'il sera execute a ce temps la. Il sera juste mis en queue a ce temps X. Le moment auquel il sera execute dependra du temps de pause du worker surveillant la queue (nombre de jobs deja dans la queue, temps de pause, etc …)

**Nous ne planifions pas quand un job sera execute, mais quand il sera mis en queue.**

## Journalisation {#logging}

Par defaut, les workers enregistrent toutes leurs activites, a des fins de deboggages ou d'informations. Ces enregistrements sont important, dans la mesure ou ils sont l'unique moyen de savoir ce que les workers, puisqu'ils tournent en arriere plan.

Les enregistrements se divisent en 2 categories :

* **worker stream** : messages bien structures, provenant des workers
* **process stream** : php warning, fatal error, et autres messages provenant du systeme

Chaque stream est dirige par defaut vers un journal different.

* worker stream est redirigee vers Monolog
* process stream est redirigee vers un fichier texte

Si Monolog n'est pas disponible, le stream sera redirigee vers le process stream.

Log stream settings are defined in the bootstrap. You can also override these settings using the `--log`, `--log-handler` and `--log-handler-target` flag when starting a worker.

### Worker Stream

<table class="table">
	<tr>
		<th>Key</th>
		<th>Description</th>
	</tr>
	<tr>
		<td markdown="1">`CakeResque.Log.handler`<br/>*String*</td>
		<td markdown="1">*Default Value : RotatingFile*<br/>
Name of the [Monolog](https://github.com/Seldaek/monolog) Handler, without the 'Handler' part.<br/>
List of supported handler [here](https://github.com/kamisama/Monolog-Init)
		</td>
	</tr>
	<tr>
		<td markdown="1">`CakeResque.Log.target`<br/>*String*</td>
		<td markdown="1">*Default Value : TMP . 'logs' . DS . 'resque-error.log'*
Argument passed to the Monolog handler.<br/>
Each handler takes its own type of argument.<br/><br/>
E.g.: **RotatingFile** takes a *pathname*, **Cube** takes an *url*.
		</td>
	</tr>
</table>

### Process Stream

<table class="table">
	<tr>
		<th>Key</th>
		<th>Description</th>
	</tr>
	<tr>
		<td markdown="1">`CakeResque.Worker.Log`<br/>*String*</td>
		<td markdown="1">*Default Value : TMP . 'logs' . DS . 'resque-worker-error.log'*</td>
	</tr>
</table>