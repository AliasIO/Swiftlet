<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

/**
 * Header
 * @abstract
 */
class Header extends Plugin
{
	public
		$version    = '1.0.0',
		$compatible = array('from' => '1.2.0', 'to' => '1.2.*'),
		$hooks      = array('init' => 999, 'header' => 999)
		;

	public
		$ready
		;

	function hook_init()
	{
		$this->ready = TRUE;
	}

	function hook_header()
	{
		$this->app->view->load('header.html.php');
	}
}
