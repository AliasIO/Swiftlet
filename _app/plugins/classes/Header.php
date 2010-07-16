<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

if ( !isset($app) ) die('Direct access to this file is not allowed');

/**
 * Header
 * @abstract
 */
class Header
{
	public
		$menu = array(),
		$ready
		;

	/**
	 * Initialize
	 * @param object $app
	 */
	function __construct($app)
	{
		$this->ready = TRUE;

		$app->hook('menu', $this->menu);

		foreach ( $this->menu as $title => $path )
		{
			if ( !preg_match('/^[a-z]+:\/\//', $path) )
			{
				$this->menu[$title] = $app->view->route($path);;

			}
		}
	}
}
