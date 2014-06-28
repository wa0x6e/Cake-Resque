<div class="hero-unit headline">
<dfn>CakeResque</dfn> is a CakePHP plugin for creating background jobs that can be processed offline later.
</div>

<div class="pull-right">
<iframe src="http://ghbtns.com/github-btn.html?user=kamisama&amp;repo=cake-resque&amp;type=watch&amp;count=true"
allowtransparency="true" frameborder="0" scrolling="0" width="110" height="20"></iframe>


<iframe src="http://ghbtns.com/github-btn.html?user=kamisama&amp;repo=cake-resque&amp;type=fork&amp;count=true"
allowtransparency="true" frameborder="0" scrolling="0" width="95" height="20"></iframe>
</div>



## Introduction {#introduction}

The main goal is to delay some nonessential tasks to later, reducing the waiting time for the user.

## The reality

<img src="/img/workflow1.jpg" class="pull-right" alt="Classic worflow" title="Classic worflow" width=361 height=615 />
Let's say a lambda user wants to update his location, and your website has some pretty neat social features centered around the user. 

Main workflow for the *update location* action :

1. 	*Update the user's location* in the users table (or whatever, depending on your structure)  
	-> took 0.2s
2.	*Refresh various cache*  
	-> took 0.1s
3. 	*Send mails*  
	-> took 0.7s	
4.	*Send some notifications* to all your friends  
	-> took 3.7s
5. 	*Recommend some new friends* around your new location  
	-> took 2s
	
**Total time** : 3.7 seconds

Your mileage may vary, but what to remember here is that the important stuff takes just a fraction of the total processing time.

## The problem

But the user doesn't sadly care about all of these stuffs, and just want to update his location.

Why should the user wait for the cache refresh ? It does not matter at all if it refreshed or not in the final response return to the user.

And what will happen if there's an error while refreshing the cache ?  

* Answer A : It crashes the application <small>(you should review your code …)</small>  
* Answer B : Ignore it, and continue <small>(who will do it then ?)</small>

The problem here is that there're some actions in the main workflow that :

* are not required to be executed immediately 
* are prone to errors, and could take down your entire application
* can take time to process, thus slowing the total processing time
* can cost system resources

## The solution

**User should just wait 0.2s instead of 3.7s**

A response should be returned immediately after the first point, and *delay all the other tasks for later, out of the main workflow*. That way, "unimportant" tasks doesn't affect the main workflow.

The trick is to tell someone else to do it.

<img src="/img/workflow2.jpg" alt="Worflow with background jobs" title="Worflow with background jobs" width=676 height=567/>

Here come the background jobs. 

Instead of executing the task, we convert it into a *job*, then put it in a *queue*. A *worker*, which is running on another php process, will poll that queue and execute the job.

That queue system is based on Resque. CakeResque is just wrapper of Resque for CakePHP.

> Resque (pronounced like "rescue") is a Redis-backed library for creating background jobs,
> placing those jobs on multiple queues, and processing them later.

<div class="alert alert-info"><i class="icon-book"></i> Read the <a href="https://github.com/defunkt/resque">Resque</a> official page for more details about background jobs, workers and queues.</div>




<div class="hero-unit">
Now head to the <a href="/install" class="btn btn-info">installation guide</a><br/> then proceed to the <a href="/usage" class="btn btn-success">usage documentation</a>
</div>


## Resources {#resources}

* 	[Resque official page](https://github.com/defunkt/resque)
* 	[Php-Resque : PHP port of Resque](https://github.com/chrisboulton/php-resque)
* 	[Serie of 9 tutorials explaining the mechanic behind backgound jobs](http://www.kamisama.me/2012/10/09/background-jobs-with-php-and-resque-part-1-introduction/)


## Support {#support}

All support, bugs submissions and feature requests are tracked on <a href="https://github.com/kamisama/Cake-Resque/issues">Github</a>.


## Author {#author}

Wan Qi Chen

* Email : [kami@kamisama.me](mailto:kami@kamisama.me)
* Twitter : [@Wa0x6e_k](https://twitter.com/Wa0x6e_k)
* Github : [https://github.com/kamisama](https://github.com/kamisama)
* Google+ : [Google+](https://plus.google.com/116246394244628198627?rel=author)


## Changelog {#changelog}

See [here](https://github.com/kamisama/Cake-Resque/blob/master/CHANGELOG.md)

## Licence {#licence}

CakeResque is licensed under the <a href="http://www.opensource.org/licenses/mit-license.php">MIT License</a>.<br />

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

Redistributions of files must retain the above copyright notice