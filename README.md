Swiftlet
========

Swiftlet is quite possibly the smallest MVC framework you'll ever use. It's also
swift.


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

**Controller: `controllers/FooController.php`**

```php
class FooController extends SwiftletController
{
	protected
		$_title = 'Foo'
		;

	/**
	 *
	 */
	public function indexAction()
	{
		$this->_app->getView()->set('hello world', 'Hello world!');
	}
}
```

**View: `views/foo.html.php`**

You can now view the page by navigating to `/foo` in your web browser.


```php
<h1><?php echo $this->getTitle() ?></h1>

<p>
	<?php echo $this->get('hello world') ?>
</p>
```

Coming soon: more examples and documentation 
--------------------------------------------
