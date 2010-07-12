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
class controller
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
		foreach ( $contrSetup as $k => $v )
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

		$this->viewPath   = $this->rootPath . '_view/';
		$this->pluginPath = $this->rootPath . '_plugins/';
		$this->classPath  = $this->rootPath . '_plugins/classes/';

		/**
		 * Find absolute path to the root
		 */
		$this->absPath = str_replace('//', '/', preg_replace('/([^\/]+\/){' . ( substr_count(( $this->rootPath == './' ? '' : $this->rootPath ), '/') ) . '}$/', '', dirname(str_replace('\\', '/', $_SERVER['PHP_SELF'])) . '/'));
	}
}
