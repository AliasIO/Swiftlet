Swiftlet
========

Swiftlet is quite possibly the smallest 
[MVC](http://en.wikipedia.org/wiki/Model-view-controller) framework you'll ever 
use. And it's swift.


Buzzword compliance
-------------------

✔ Micro-Framework  
✔ Pluggable  
✔ MVC  
✔ OOP  
✔ PHP5  

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

**Controller `controllers/FooController.php`**

```php
<?php

class FooController extends SwiftletController
{
	protected
		$_title = 'Foo' // Optional but recommended
		;

	public function indexAction()
	{
		// Pass a variable to the view
		$this->_app->getView()->set('hello world', 'Hello world!');
	}
}
```

Class names are written in [CamelCase](http://en.wikipedia.org/wiki/CamelCase)
and match their filename. This is not just a convention; it's required.

**View `views/foo.html.php`**

```php
<h1><?php echo $this->getTitle() ?></h1>

<p>
	<?php echo $this->get('hello world') ?>
</p>
```

Variables can be passed from controller to view using the view's `set` and `get`
methods. Values are automatically made safe for use in HTML.

You can now view the page by navigating to `http://<swiftlet>/foo` in your web browser!


Routing
-------

Notice how we can access the page at `/foo` by simply creating a controller 
named `FooController`. The application (Swiftlet) automatically maps URLs
to controllers, actions and arguments.

Consider the following URL: `/foo/bar/baz/qux`

In this case `foo` is the controller, `bar` is the action and `baz` and `qux`
are arguments. If the controller or action is missing they will default to 
`index`.


Actions and arguments
---------------------

Actions are methods of the controller. A common example might be `edit` or
`delete`:

`/blog/edit/1`

This will call the function `editAction()` on `BlogController` and pass the
argument `1` (i.e. the id of the blog post we're editing).

If the action doesn't exist `notImplementedAction()` will be called instead.
This will throw an exception by default but can be overridden.

The action name and arguments can be accessed by calling
`$this->_app->getAction()` and `$this->_app->getArgs()` respectively.


Models
------------

Let's throw a model into the mix and update the controller.

**Model `model/FooModel.php`**

```php
<?php

class FooModel extends SwiftletModel
{
	public function getHelloWorld() {
		return 'Hello world!';
	}
}
```

**Controller `controllers/FooController.php`**

```php
<?php

class FooController extends SwiftletController
{
	protected
		$_title = 'Foo'
		;

	public function indexAction()
	{
		$exampleModel = $this->_app->getModel('example');

		$helloWorld = $exampleModel->getHelloWorld();

		$this->_app->getView()->set('hello world', $helloWorld);
	}
}
```

Controllers get their data from models. Code for querying a database,
reading/writing files and parsing data all belongs in a model. You can create as
many models as you like; they aren't tied to specific controllers.

A model can instantiated using `$this->_app->getModel()`.  To allow re-use, use 
`$this->_app->getSingleton()` instead as this will only create a single instance
when called multiple times.


TODO: Plugins and hooks
-----------------------

--------------------------------------------------------------------------------
