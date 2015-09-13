Swiftlet
========

[Swiftlet](http://swiftlet.org/) is quite possibly the smallest 
[MVC](http://en.wikipedia.org/wiki/Model-view-controller) framework you'll ever 
use. And it's swift.

*Licensed under the [MIT license](http://www.opensource.org/licenses/mit-license.php).*


Buzzword compliance
-------------------

✔ Micro-Framework  
✔ Loosely coupled  
✔ Unit tested  
✔ Namespaced  
✔ Pluggable  
✔ [Composer](https://getcomposer.org)
✔ [PSR-4](http://www.php-fig.org/psr/psr-4)
✔ PHP5  
✔ MVC  
✔ OOP  

✘ ORM  


Installation
------------

* Clone (or download and extract) Swiftlet into a directory on your PHP
  supported web server.
* Having [Composer](https://getcomposer.org) installed, run `composer dump-autoload`.


Getting started: controllers and views
--------------------------------------

Let's create a page. Each page consists of a controller and at least one view.

The controller does most of the work; views should be limited to simple 
presentation logic (loops and switches).

**Controller `src/HelloWorld/Controllers/Foo.php`**

```php
<?php
namespace HelloWorld\Controllers;

use \Swiftlet\Abstracts\Controller as ControllerAbstract;

class Foo extends ControllerAbstract
{
	protected $title = 'Foo'; // Optional but usually desired 

	// Default action
	public function index(array $args = $args)
	{
		// Pass a variable to the view
		$this->view->helloWorld = 'Hello world!';
	}
}
```

Important: class names are written in 
[CamelCase](http://en.wikipedia.org/wiki/CamelCase) and match their filename.


**View `views/foo.php`**

```php
<h1><?= $this->pageTitle ?></h1>

<p>
	<?= $this->helloWorld ?>
</p>
```

The controller can set variables directly on the view. Values are automatically 
made safe for use in HTML, use `$this->get('variable', false)` on values that 
should be treated as code.

You can now view the page by navigating to `http://<swiftlet>/foo` in your web
browser!

If you get a "404 Not Found" you will need to enable rewrites in the web server
configuration. Alternatively you can navigate to `http://<swiftlet>?q=foo`.

*Swiftlet can be invoked from the command line (e.g. to run cron jobs). Simply
run `php public/index.php -q foo`.*


Routing
-------

Notice how you can access the page at `/foo` by simply creating a controller 
named `Foo`. The application maps URLs to controllers, actions and arguments.

Consider this URL: `/foo/bar`

In this case `foo` becomes the name of the controller and view and `bar` the 
name of the action. Actions are public methods on the controller class.

You can specify a different view for an action using `$this->view->setName()`. 
The view name is a filename relative to the `src\<namespace>\views` directory, 
without the `.php` suffix.

If the controller or action is not specified they default to `index` (`/`
will call `index()` on `\HelloWorld\Controller\Index`).

Underscores in the controller name are translated to directory separators, so
`/foo_bar` will point to `src/HelloWorld/Controllers/Foo/Bar.php`.

Dashes in routes are ignored; `/foo-bar/baz-qux` calls `bazqux()` on 
`\HelloWorld\Controllers\Foobar`.


**Custom routes**

Automatic routing is convenient but more granular control is often desirable.
In these cases custom routes can be defined.

A route maps a URL to an action (method).

URL segments can be replaced with a "wildcard" placeholder (a variable name
prefixed with a colon). This value becomes available for use in the 
controller.

Consider this route: `bar/:qux`

Navigating to `<controller>/bar/something` matches this route. The value of 
`$args['qux']` becomes `something`.


```php
<?php
namespace HelloWorld\Controllers;

use \Swiftlet\Abstracts\Controller as ControllerAbstract;

class Foo extends ControllerAbstract
{
	protected $routes = array(
		'hello/world' => 'index',
		'bar/:qux'    => 'bar'
		);

	public function index(array $args = $args)
	{
		// You navigated to foo/hello/world
	}

	public function bar(array $args = $args)
	{
		// You navigated to foo/bar/<something>
		// $args['qux'] contains the second URL argument
	}
}
```


Models
------

Let's throw a model into the mix and update the controller.

**Model `src/HelloWorld/Models/Foo.php`**

```php
<?php
namespace HelloWorld\Models;

use \Swiftlet\Abstracts\Model as ModelAbstract;

class Foo extends ModelAbstract
{
	public function getHelloWorld()
	{
		return 'Hello world!';
	}
}
```

**Controller `src/HelloWorld/Controllers/Foo.php`**

```php
<?php
namespace HelloWorld\Controllers;

use \Swiftlet\Abstracts\Controller as ControllerAbstract;
use \HelloWorld\Models\Example as ExampleModel;

class Foo extends ControllerAbstract;
{
	protected $title = 'Foo';

	public function index()
	{
		// Get an instance of the Example class 
		// See src/HelloWorld/Models/Example.php
		$example = new ExampleModel;

		$this->view->helloWorld = $example->getHelloWorld();
	}
}
```

A model typically represents data. This can be an entry in a database or an
object such as a user.

```php
<?php
use \HelloWorld\Models\User as UserModel;

$user = new UserModel;

$user->setEmail('example@example.com');

$user->save();
```

Loading and saving data should almost always happen in a model. You can create 
as many models as you like; they aren't tied to controllers or views.


Events and listeners
--------------------

Listeners listen for events. When an event is triggered all relevant listeners 
are called and can be used to extend functionality.

Swiftlet has a few core events and additiontal ones can be triggered pretty much 
anywhere using `$this->app->trigger($event)`.  

**Listener `src/HelloWorld/Listeners/Foo.php`**

```php
<?php
namespace HelloWorld\Listeners;

use \Swiftlet\Abstracts\Controller as ControllerAbstract;
use \Swiftlet\Abstracts\Listener as ListenerAbstract;
use \Swiftlet\Abstracts\View as ViewAbstract;

class Foo extends ListernerAbstract
{
	public function actionAfter(ControllerAbstract $controller, ViewAbstract $view)
	{
		// Overwrite our previously set "helloWorld" variable
		$view->helloWorld = 'Hi world!';
	}
}
```

This listener listens for the core `actionAfter` event and changes the view 
variable `helloWorld` from our previous example to `Hi world!`.

Listeners don't need to be installed or activated, all files in the
`src/HelloWorld/Listeners/` directory are automatically included and their 
classes instantiated. Listeners are called in alphabetical order.

The core events are:

* `actionBefore`  
Called before each action

* `actionAfter` 
Called after each action


Libraries
---------

Reusable components such as code to send an email or generate a thumbnail image
should go in a separate library class.

```php
<?php
use \HelloWorld\Libraries\Email as EmailLibrary;

$email = new EmailLibrary;

$email->send($to, $subject, $message);
```


Configuration
-------------

No configuration is needed to run Swiftlet. If you're writing a model that
does require configuration, e.g. credentials to establish a database connection,
you may use the application's `setConfig` and `getConfig` methods:

```php
<?php
$this->app->setConfig('variable', 'value');

$value = $this->app->getConfig('variable');
```

Values can be set in `config/main.php` or a custom file.


Public methods
--------------

**Application `Swiftlet\App`**

* `App dispatchController()`  
Determine which controller to use and run it

* `App serve()`  
Serve the page

* `mixed getConfig(string $variable)`  
Get a configuration value

* `App setConfig(string $variable, mixed $value)`  
Set a configuration value

* `App registerHook(string $hookName, array $params)`  
Register a hook


**View `Swiftlet\View`**

* `mixed get(string $variable [, bool $htmlEncode = true ])`  
Get a view variable, encoded for safe use in HTML by default

* `View set(string $variable [, mixed $value ])`  
Set a view variable

* `mixed get(string $variable [, bool $htmlEncode ])`  
Get a view variable, pass `false` as the second parameter to prevent values from
being HTML encoded.

* `string getRootPath()`  
Absolute client-side path to the website root

* `mixed htmlEncode(mixed $value)`  
Recursively make a value safe for HTML

* `mixed htmlDecode(mixed $value)`  
Recursively decode a previously encoded value to be rendered as HTML

* `View render(string $path)`  
Render the view
