Swiftlet
========

[Swiftlet](http://swiftlet.org/) is quite possibly the smallest 
[MVC](http://en.wikipedia.org/wiki/Model-view-controller) framework you'll ever 
use. And it's swift.

*Licensed under the [MIT license](http://www.opensource.org/licenses/mit-license.php).*


Buzzword compliance
-------------------

✔ Micro-Framework  
✔ Namespaced  
✔ Unit tested  
✔ Pluggable  
✔ [PSR-0](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md)  
✔ PHP5  
✔ MVC  
✔ OOP  

✘ ORM  


Installation in three easy steps
--------------------------------

* Step 1: Clone (or download and extract) Swiftlet into a directory on your PHP
  supported web server.
* Step 2: Congratulations, Swiftlet is now up and running.
* Step 3: There is no step 3.


Getting started: controllers and views
--------------------------------------

Let's create a page. Each page consists of a controller and at least one view.

Controllers house the 
[business logic](http://en.wikipedia.org/wiki/Business_logic) of the page while 
views should be limited to simple UI logic (loops and switches).

**Controller `lib/Swiftlet/Controllers/Foo.php`**

```php
<?php
namespace Swiftlet\Controllers;

class Foo extends \Swiftlet\Controller
{
	protected $title = 'Foo'; // Optional but usually desired 

	public function index()
	{
		// Pass a variable to the view
		$this->view->set('helloWorld', 'Hello world!');
	}
}
```

Important: class names are written in 
[CamelCase](http://en.wikipedia.org/wiki/CamelCase) and match their filename.


**View `views/foo.html.php`**

```php
<?php namespace Swiftlet ?>

<h1><?php echo self::get('pageTitle') ?></h1>

<p>
	<?php echo self::get('helloWorld') ?>
</p>
```

Variables can be passed from controller to view using `$this->view->set()` and 
`self::get()`. Values are automatically made safe for use in HTML, use
`self::htmlDecode()` on values that should be treated as code.

You can now view the page by navigating to `http://<swiftlet>/foo` in your web
browser!


Routing
-------

Notice how we can access the page at `/foo` by simply creating a controller 
named `Foo`. The application (Swiftlet) maps URLs to controllers, actions and
arguments.

Consider this URL: `/foo/bar/baz/qux`

In this case `foo` is the name of the controller and view, `bar` the name of 
the action and `baz` and `qux` are arguments. If the controller or action is 
missing from the URL they will default to `index` (`/` will call `index()` on 
`Swiftlet\Controller\Index`).

Underscores in the controller name are translated to directory separators, so
`/foo_bar` will point to `lib/Swiftlet/Controllers/Foo/Bar.php`.


Actions and arguments
---------------------

Actions are methods of the controller. A common example might be `edit` or
`delete`:

`/blog/edit/1`

This will call the function `edit()` on `Blog` with `1` as the argument (the 
id of the blog post to edit).

If the action doesn't exist `notImplemented()` will be called instead.  This 
will throw an exception by default but can be overridden.

The action name and arguments can be accessed through 
`$this->app->getAction()` and `$this->app->getArgs()` respectively.

Note: if you want to use different HTML files for each action you can change 
the view with `$this->view->setName($viewName)`.


Models
------

Let's throw a model into the mix and update the controller.

**Model `lib/Swiftlet/Models/Foo.php`**

```php
<?php
namespace Swiftlet\Models;

class Foo extends \Swiftlet\Model
{
	public function getHelloWorld()
	{
		return 'Hello world!';
	}
}
```

**Controller `lib/Swiftlet/Controllers/Foo.php`**

```php
<?php
namespace Swiftlet\Controllers;

class Foo extends \Swiftlet\Controller
{
	protected $title = 'Foo';

	public function index()
	{
		// Get an instance of the Example class (lib/Swiftlet/Models/Example.php)
		$exampleModel = $this->app->getModel('example');

		$helloWorld = $exampleModel->getHelloWorld();

		$this->view->set('helloWorld', $helloWorld);
	}
}
```

Controllers get their data from models. Code for querying a database,
reading/writing files and parsing data all belongs in a model. You can create as
many models as you like; they aren't tied to specific controllers.

A model can instantiated using `$this->app->getModel($modelName)`. To allow 
re-use, use `$this->app->getSingleton($modelName)` instead as this will only
create a single instance when called multiple times (useful for database 
connections and session management).


Plugins and hooks
-----------------

Plugins implement [hooks](http://en.wikipedia.org/wiki/Hooking). Hooks are entry
points for code that extends the application. Swiftlet has a few core hooks but 
they can be registered pretty much anywhere using
`App::registerHook($hookName)`.  

**Plugin `lib/Swiftlet/Plugins/Foo.php`**

```php
<?php
namespace Swiftlet\Plugins;

class Foo extends \Swiftlet\Plugin
{
	public function actionAfter()
	{
		// Overwrite our previously set "hello world" variable
		if ( get_class($this->controller) === 'Swiftlet\Controllers\Foo' ) {
			$this->view->set('helloWorld', 'Hi world!');
		}
	}
}
```

This plugin implements the core `actionAfter` hook and changes the view 
variable `hello world` from our previous example to `Hi world!`.

Plugins don't need to be installed or activated, all files in the
`/lib/Swiftlet/Plugins/` directory are automatically included and their classes 
instantiated. They are hooked in alphabetical order.

The core hooks are:

* `actionBefore`  
Called before each action

* `actionAfter` 
Called after each action


Configuration
-------------

No configuration is needed to run Swiftlet. If you're writing a model that
does require configuration, e.g. credentials to establish a database connection,
you may use the Config class:

```php
<?php
Config::set('variable', 'value');

$value = Config::get('variable');
```

Values can be set in `config.php` or a custom file.


--------------------------------------------------------------------------------


Public abstract methods
-----------------------

All application and view methods can be called statically, e.g.
`App::getAction()` and `View::getTitle()`.


**Application `Swiftlet\App`**

* `string getAction()`  
Name of the action

* `array getArgs()`  
List of arguments passed in the URL

* `object getModel(string $modelName)`  
Create a new model instance

* `object getSingleton(string $modelName)`  
Create or return an existing model instance

* `array getPlugins()`  
All plugin instances

* `array getHooks()`  
All registered hooks

* `string getRootPath()`  
Absolute client-side path to website root

* `registerHook(string $hookName, array $params)`  
Register a hook


**View `Swiftlet\View`**

* `string getName()`  
Get the name of the view

* `string setName()`  
Change the view

* `mixed get(string $variable [, bool $htmlEncode = true ])`  
Get a view variable, encoded for safe use in HTML by default

* `set(string $variable [, mixed $value ])`  
Set a view variable

* `htmlEncode(mixed $value)` 
Recursively make a value safe for HTML

* `htmlDecode(mixed $value)` 
Recursively decode a previously encoded value to be rendered as HTML


**Controller `Swiftlet\Controller`**

* `index()`  
Default action

* `notImplemented()`  
Fallback action if action doesn't exist


**Config `Swiftlet\Config`**

* `mixed get(string $variable)`  
Get a config variable, encoded for safe use in HTML by default

* `set(string $variable [, mixed $value ])`  
Set a config variable
