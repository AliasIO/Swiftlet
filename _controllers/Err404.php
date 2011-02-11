<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU Public License
 */

if ( !isset($this) ) die('Direct access to this file is not allowed');

/**
 * Error 404
 * @abstract
 */
class Err404_Controller extends Controller
{
	public
		$pageTitle = 'Page not found'
		;

	function init()
	{
		header('HTTP/1.0 404 Not Found');

		$this->view->load('err404.html.php');
	}
}
