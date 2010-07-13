<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

if ( !isset($app) ) die('Direct access to this file is not allowed');

/**
 * Buffer
 * @abstract
 */
class Buffer
{
	public
		$ready = FALSE
		;

	private
		$app,
		$view,
		$controller
		;

	/**
	 * Initialize buffer
	 * @param object $app
	 */
	function __construct($app)
	{
		$this->app        = $app;
		$this->view       = $app->view;
		$this->controller = $app->controller;
	}

	/**
	 * Start buffering
	 */
	function start()
	{
		if ( !$this->ready )
		{
			ob_start();

			$this->ready = TRUE;
		}
	}

	/**
	 * Flush the buffer, send output to the browser
	 */
	function flush()
	{
		if ( $this->ready )
		{
			$contents = ob_get_contents();

			$params['contents'] = &$contents;

			$this->app->hook('cache', $params);

 			if ( ob_get_length() > 0 )
			{
				ob_end_clean();
			}

			$this->ready = FALSE;

			// Output debug messages
			ob_start();

			if ( $this->app->debugMode && !$this->controller->standAlone )
			{
				echo "\n<!--\n\n[ DEBUG OUTPUT ]\n\n";

				print_r($this->app->debugOutput);

				echo "\n-->";
			}

			$contents .= ob_get_contents();

			ob_end_clean();

			// gZIP compression
			if ( !empty($_SERVER['HTTP_ACCEPT_ENCODING']) && strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') )
			{
				$contents = gzencode($contents);

				header('Content-Encoding: gzip');
			}

			header('X-Powered-By: Swiftlet - http://swiftlet.org');

			echo $contents;
		}
	}

	/**
	 * Clean the buffer, cancel output
	 */
	function clean()
	{
		if ( $this->ready )
		{
			if ( ob_get_length() > 0 )
			{
				ob_end_clean();
			}

			$this->active = FALSE;
		}
	}
}
