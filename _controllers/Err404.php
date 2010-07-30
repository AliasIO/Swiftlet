<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

class Err404 extends Controller
{
	public
		$pageTitle = 'Page not found'
		;

	function init()
	{
		$this->view->load('404.html.php');
	}
}
