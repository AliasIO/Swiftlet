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

The controller does most of the work; views should be limited to simple 
presentation logic (loops and switches).

**Controller `vendor/HelloWorld/Controllers/Foo.php`**

```php
<?php
namespace HelloWorld\Controllers;

class Foo extends \Swiftlet\Abstracts\Controller
{
	protected $title = 'Foo'; // Optional but usually desired 

	public function index()
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

Consider this URL: `/foo/bar/baz/qux`

In this case `foo` is the name of the controller and view, `bar` the name of 
the action and `baz` and `qux` are additional arguments. If the controller or
action is missing from the URL they will default to `index` (`/` will call 
`index()` on `HelloWorld\Controller\Index`).

Underscores in the controller name are translated to directory separators, so
`/foo_bar` will point to `vendor/HelloWorld/Controllers/Foo/Bar.php`.

Dashes in routes are ignored; `/foo-bar/baz-qux` calls `bazqux()` in 
`vendor/HelloWorld/Controllers/Foobar.php`.


Actions and arguments
---------------------

Actions are methods of the controller. A common example might be `edit` or
`delete`:

`/blog/edit/1`

This will call the function `edit()` on `Blog` with `1` as an argument (the 
id of the blog post to edit).

Arguments can be accessed through `$this->app->getArgs()`.

To use a different view for a specific action you may change the value of 
`$this->view->name`. The view name is a filename relative to the `views` 
directory, without the `.php` suffix.


Models
------

Let's throw a model into the mix and update the controller.

**Model `vendor/HelloWorld/Models/Foo.php`**

```php
<?php
namespace HelloWorld\Models;

class Foo extends \Swiftlet\Abstracts\Model
{
	public function getHelloWorld()
	{
		return 'Hello world!';
	}
}
```

**Controller `vendor/HelloWorld/Controllers/Foo.php`**

```php
<?php
namespace HelloWorld\Controllers;

use HelloWorld\Models\Example as ExampleModel;

class Foo extends \Swiftlet\Abstracts\Controller
{
	protected $title = 'Foo';

	public function index()
	{
		// Get an instance of the Example class 
		// See vendor/HelloWorld/Models/Example.php
		$example = new ExampleModel;

		$this->view->helloWorld = $example->getHelloWorld();
	}
}
```

A model typically represents data. This can be an entry in a database or an
object such as a user.

```php
<?php
$user = new \HelloWorld\Models\User;

$user->setEmail('example@example.com');

$user->save();
```

Loading and saving data should almost always happen in a model. You can create 
as many models as you like; they aren't tied to specific controllers or views.


Plugins and hooks
-----------------

Plugins implement [hooks](http://en.wikipedia.org/wiki/Hooking). Hooks are entry
points for code that extends the application. Swiftlet has a few core hooks and 
additiontal ones can be registered pretty much anywhere using
`$this->app->registerHook($hookName, $controller, $view)`.  

**Plugin `vendor/HelloWorld/Plugins/Foo.php`**

```php
<?php
namespace HelloWorld\Plugins;

class Foo extends \Swiftlet\Abstracts\Plugin
{
	public function actionAfter()
	{
		// Overwrite our previously set "helloWorld" variable
		$this->view->helloWorld = 'Hi world!';
	}
}
```

This plugin implements the core `actionAfter` hook and changes the view 
variable `helloWorld` from our previous example to `Hi world!`.

Plugins don't need to be installed or activated, all files in the
`vendor/HelloWorld/Plugins/` directory are automatically included and their classes 
instantiated. Plugins are hooked in alphabetical order.

The core hooks are:

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
$email = new \HelloWorld\Libraries\Email

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

* `mixed getConfig(string $variable)`  
Get a configuration value

* `App setConfig(string $variable, mixed $value)`  
Set a configuration value

* `array getArgs([ integer $index ])`  
List of arguments passed in the URL, or a specific argument if `$index` is specified

* `string getRootPath()`  
Absolute client-side path to the website root

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

* `mixed htmlEncode(mixed $value)`  
Recursively make a value safe for HTML

* `mixed htmlDecode(mixed $value)`  
Recursively decode a previously encoded value to be rendered as HTML

* `View render(string $path)`  
Render the view
