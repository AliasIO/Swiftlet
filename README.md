Swiftlet
========

Swiftlet is quite possibly the smallest 
[http://en.wikipedia.org/wiki/Model-view-controller](MVC) framework you'll ever 
use. It's also swift.


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


Getting started
---------------

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
		$_title = 'Foo'
		;

	public function indexAction()
	{
		$this->_app->getView()->set('hello world', 'Hello world!');
	}
}
```

**View `views/foo.html.php`**

```php
<h1><?php echo $this->getTitle() ?></h1>

<p>
	<?php echo $this->get('hello world') ?>
</p>
```

You can now view the page by navigating to `/foo` in your web browser!

Routing
-------

Notice how we can access the page at `/foo` by simply creating a controller 
named `FooController`. The application (Swiftlet) automatically maps URLs
to controllers, actions and arguments.

Consider the following URL:

```
	/foo/bar/baz/qux
```

In this case `foo` is the controller, `bar` is the action and `baz` and `qux`
are arguments.

TODO

--------------------------------------------------------------------------------

TODO: Actions
-------------

TODO: Models
------------

TODO: Plugins
-------------

TODO: More examples and documentation 
-------------------------------------
