<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

if ( !isset($app) ) die('Direct access to this file is not allowed');

/**
 * Dashboard
 * @abstract
 */
class dashboard
{
	public
		$pages = array(),
		$ready
		;

	/**
	 * Initialize
	 * @param object $app
	 */
	function __construct($app)
	{
		$pages = array();

		$app->hook('dashboard', $pages);

		foreach ( $pages as $page )
		{
			if ( !isset($page['perm']) || $app->perm->check($page['perm']) )
			{
				if ( !isset($this->pages[$page['group']]) )
				{
					$this->pages[$page['group']] = array();
				}

				$this->pages[$page['group']][] = $page;
			}
		}

		$this->ready = TRUE;
	}
}
