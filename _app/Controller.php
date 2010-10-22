<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
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
		$args         = array(),
		$app,
		$dependencies = array(),
		$id,
		$method,
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
		$this->method  = $this->view->method;
		$this->path    = $this->view->path;
		$this->request = $this->view->request;

		if ( $this->dependencies )
		{
			$missing = array();

			foreach ( $this->dependencies as $dependency )
			{
				if ( empty($app->{$dependency}->ready) )
				{
					$missing[] = $dependency;
				}
			}

			if ( $missing )
			{
				$app->error(FALSE, 'Plugins required for this page: `' . implode('`, `', $missing) . '`.', __FILE__, __LINE__);
			}
		}

		if ( !$this->standAlone )
		{
			$app->hook('header');
		}
	}
}
