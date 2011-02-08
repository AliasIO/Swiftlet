<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU Public License
 */

if ( !isset($swiftlet) ) die('Direct access to this file is not allowed');

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

	protected
		$action,
		$app,
		$dependencies = array(),
		$id,
		$path,
		$request,
		$view
		;

	/**
	 * Initialize
	 * @param object $app
	 */
	function __construct($app)
	{
		$this->app  = $app;
		$this->view = $app->view;

		$this->id      = ( int ) $this->view->id;
		$this->action  = $this->view->action;
		$this->path    = $this->view->path;
		$this->request = $this->view->request;

		if ( !$this->standAlone )
		{
			$app->hook('header');
		}
	}
}
