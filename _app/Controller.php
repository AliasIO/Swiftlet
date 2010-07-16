<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

if ( !isset($controllerSetup) ) die('Direct access to this file is not allowed');

/**
 * Controller
 * @abstract
 */
class Controller
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
	 * @param array $controllerSetup
	 */
	function __construct($controllerSetup)
	{
		foreach ( $controllerSetup as $k => $v )
		{
			$this->{$k} = $v;
		}

		if ( !$this->rootPath )
		{
			$this->rootPath = './';
		}

		if ( empty($this->rootPathView) )
		{
			$this->rootPathView = $this->rootPath;
		}

		$this->viewPath       = $this->rootPath . '_views/';
		$this->controllerPath = $this->rootPath . '_controllers/';
		$this->pluginPath     = $this->rootPath . '_app/plugins/';
		$this->classPath      = $this->rootPath . '_app/plugins/classes/';

		/**
		 * Find absolute path to the root
		 */
		$this->absPath = str_replace('//', '/', preg_replace('/([^\/]+\/){' . ( substr_count(( $this->rootPath == './' ? '' : $this->rootPath ), '/') ) . '}$/', '', dirname(str_replace('\\', '/', $_SERVER['PHP_SELF'])) . '/'));
	}
}
