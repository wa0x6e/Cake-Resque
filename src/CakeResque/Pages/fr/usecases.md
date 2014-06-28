<div class="alert"><b>ATTENTION</b><br/> Cette page n'est pas encore entierement traduite en francais. <a href="https://github.com/kamisama/Cake-Resque/blob/master/CONTRIBUTING.md">Aidez-nous a la traduire</a>.</div>

# Use cases {#page-header}

## Sending emails {#sending-emails}

<a href="http://book.cakephp.org/2.0/en/core-utility-libraries/email.html">Sending emails</a> is probably the most common use case of background jobs. Everyone need to send emails.

### The problem

Sending emails can be slow. Depending on what type of emails you're sending (sign in confirmation, notification, etc ...),
you'll probably need to fetch additional datas from the database for the content. You'll also probably need to parse and
render a template to format the email.


This is what you probably have


~~~ .language-php
# User model class
App::uses('CakeEmail', 'Network/Email');
class User extends AppModel
{
    public function signUp($data) {
        if ($this->save($data)) {
            # Send email
            # See http://book.cakephp.org/2.0/en/core-utility-libraries/email.html for details
            $email = new CakeEmail();
            $email->template('welcome', 'fancy')
                ->viewVars($this->datas)
                ->emailFormat('html')
                ->to($this->data['User']['email'])
                ->from('app@domain.com')
                ->send();
            return true;
        }
        return false;
    }
}
~~~


Firstly, there's something wrong with this application design. <code>User</code> class should not be coupled the
<code>CakeEmail</code> class. <br/>
Secondly, the email sending is slowing down the workflow.

The application design part is easily solved by using <a href="http://book.cakephp.org/2.0/en/core-libraries/events.html">Events</a>.
    But even if the email sending is moved to another part of the application, it's still inside the <em>main workflow</em> of
    signing up a user. It's CakeResque time.

### The solution

This is one solution among others.

#### Step 1 : Move all the non-User class related stuff to an event

~~~ .language-php
# User model class
class User extends AppModel
{
    public function signUp() {
        if ($this->save($this->data)) {
            $this->getEventManager()->dispatch(new CakeEvent('Model.Order.afterSignUp', $this));
            return true;
        }
        return false;
    }
}
~~~



#### Step 2 : Enqueue the job in the event callback

Let's register the callback first. In this particular example, we'll register it in the <code>UsersController</code>.<br/>
You can register it elsewhere, depending on your application design.

~~~ .language-php
class UsersController extends AppController
{
    public function signUp() {
        $this->User->getEventManager()->attach(
            function($event) {
                // In this case, $event->subject() == $this->User
                CakeResque::enqueue('email', 'EmailSender', array('sendSignUpEmail', $event->subject()->id))
            },
            'Model.User.afterSignUp'
        );

        if ($this->User->signUp($this->data)) {
            ...
        }
    }
}
~~~


#### Step 3 : Create the job class

~~~ .language-php
# app/Console/Command/EmailSenderShell.php
App::uses('CakeEmail', 'Network/Email');
class EmailSenderShell extends AppShell
{
    public function sendSignUpEmail() {
        // We don't have access to User object anymore
        // We need to create it, and re-fetch all the pertinent datas
        $user = ClassRegistry::init('User')->find('first', array('conditions' => array('id' => $this->args[0])));

        // $this->args[0] == the User id, passed when queuing the job with CakeResque::enqueue() at step 2

        $email = new CakeEmail();
        $email->template('welcome', 'fancy')
            ->viewVars($user)
            ->emailFormat('html')
            ->to($user['User']['email'])
            ->from('app@domain.com')
            ->send();
    }
}
~~~



#### Bonus 1: Send email via the Cake Console

~~~ .language-php
cake emailSender sendSignUpEmail USER-ID
~~~


#### Bonus 2: Re-enqueue the email if sending failed

Sometimes, sending an email can fail. This can happen when you're using an
<a href="http://book.cakephp.org/2.0/en/core-utility-libraries/email.html#using-transports">external service</a> (like gmail)
that's down, or taking too much time to respond, to send your emails.<br/>
You can try to resend it, by queuing it again.


~~~ .language-php
# app/Console/Command/EmailSenderShell.php
App::uses('CakeEmail', 'Network/Email');
class EmailSenderShell extends AppShell
{
    public function sendSignUpEmail() {
        // We don't have access to User object anymore
        // We need to create it, and re-fecth all the pertinent datas
        $user = ClassRegistry::init('User')->find('first', array('conditions' => array('id' => $this->args[0])));

        // $this->args[0] == the User id, passed when queuing the job with CakeResque::enqueue() at step 2

        $email = new CakeEmail();
        try {
            $email->template('welcome', 'fancy')
                ->viewVars($user)
                ->emailFormat('html')
                ->to($user['User']['email'])
                ->from('app@domain.com')
                ->send();
        } catch (SocketException $e) {
            // Resend the email if fail
            // We're assuming here that the only possible SocketException is the one thrown
            // when the email fail to be sent
            CakeResque::enqueue('email', 'EmailSender', array('sendSignUpEmail', $this->args[0]));
        }
    }
}
~~~


## Resizing image/Creating thumbnails {#resizing-images}

<div class="alert alert-info"><i class="icon-time"></i> Coming soon ...</div>

## Cache warming up {#warming-up-cache}

<div class="alert alert-info"><i class="icon-time"></i> Coming soon ...</div>

