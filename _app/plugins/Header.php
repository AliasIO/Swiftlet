<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

if ( !isset($this) ) die('Direct access to this file is not allowed');

/**
 * Header
 * @abstract
 */
class Header_Plugin extends Plugin
{
	public
		$version    = '1.0.0',
		$compatible = array('from' => '1.3.0', 'to' => '1.3.*'),
		$hooks      = array('init' => 999, 'header' => 999),

		$menu = array()
		;

	/*
	 * Implement init hook
	 */
	function init()
	{
		$this->ready = TRUE;

		$this->app->hook('menu', $this->menu);

		foreach ( $this->menu as $title => $path )
		{
			if ( !preg_match('/^[a-z]+:\/\//', $path) )
			{
				$this->menu[$title] = $this->app->view->route($path);;
			}
		}
	}

	/*
	 * Implement header hook
	 */
	function header()
	{
		$this->app->view->load('header.html.php');
	}
}
