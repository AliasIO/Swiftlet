<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

/**
 * Node
 * @abstract
 */
class Node_Controller extends Controller
{
	public
		$pageTitle = 'Page not found'
		;

	function init()
	{
		header('HTTP/1.0 404 Not Found');

		$this->app->view->load('404.html.php');
	}
}
