<?php

namespace Swiftlet;

$dir = dirname(__FILE__) . '/../';

require_once $dir . 'Interfaces/Common.php';
require_once $dir . 'Interfaces/App.php';
require_once $dir . 'Interfaces/Controller.php';
require_once $dir . 'Interfaces/View.php';
require_once $dir . 'Abstracts/Common.php';
require_once $dir . 'Abstracts/App.php';
require_once $dir . 'Abstracts/Controller.php';
require_once $dir . 'Abstracts/View.php';

require_once 'Mocks/App.php';
require_once 'Mocks/Controller.php';
require_once 'Mocks/View.php';

class ViewTest extends \PHPUnit_Framework_TestCase
{
	function testGet()
	{
	}

	function test__get()
	{
	}

	function test__set()
	{
	}

	function testSet()
	{
	}

	function testHtmlEncode()
	{
	}

	function testHtmlDecode()
	{
	}

	function testRender()
	{
	}
}
