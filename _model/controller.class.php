<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

if ( !isset($contrSetup) ) die('Direct access to this file is not allowed');

/**
 * Controller
 * @abstract
 */
class contr
{
	public
		$pageDescription,
		$pageKeywords,
		$pageTitle,
		$standAlone,
		$inAdmin
		;

	/**
	 * Initialize
	 * @param array $contrSetup
	 */
	function __construct($contrSetup)
	{
		$contr = $this;

		foreach ( $contrSetup as $k => $v )
		{
			$contr->{$k} = $v;
		}

		if ( !$contr->rootPath )
		{
			$contr->rootPath = './';
		}

		if ( empty($contr->rootPathView) )
		{
			$contr->rootPathView = $contr->rootPath;
		}

		$this->viewPath   = $contr->rootPath . '_view/';
		$this->pluginPath = $contr->rootPath . '_plugins/';
		$this->classPath  = $contr->rootPath . '_plugins/classes/';

		/**
		 * Find absolute path
		 */
		$this->absPath = str_replace('//', '/', preg_replace('/([^\/]+\/){' . ( substr_count(( $contr->rootPath == './' ? '' : $contr->rootPath ), '/') ) . '}$/', '', dirname(str_replace('\\', '/', $_SERVER['PHP_SELF'])) . '/'));
	}
}