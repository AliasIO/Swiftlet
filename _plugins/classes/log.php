<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

if ( !isset($app) ) die('Direct access to this file is not allowed');

/**
 * Log
 * @abstract
 */
class Log
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
	 * Initialize
	 * @param object $app
	 */
	function __construct($app)
	{
		$this->app        = $app;
		$this->view       = $app->view;
		$this->controller = $app->controller;

		$this->ready = TRUE;
	}

	/**
	 * Write to log
	 */
	function write($filename, $contents)
	{
		if ( !is_dir($this->controller->rootPath . 'log') )
		{
			$this->app->error(FALSE, 'Directory "/log" does not exist.', __FILE__, __LINE__);
		}

		if ( !is_writable($this->controller->rootPath . 'log') )
		{
			$this->app->error(FALSE, 'Directory "/log" is not writable.', __FILE__, __LINE__);
		}

		$contents = date('M d H:i:s') . "\t" . $contents . "\n";

		if ( !$handle = fopen($this->controller->rootPath . 'log/' . $filename, 'a+') )
		{
			$this->app->error(FALSE, 'Could not open file "/log/' . $filename . '".', __FILE__, __LINE__);
		}

		if ( fwrite($handle, $contents) === FALSE )
		{
			$this->app->error(FALSE, 'Could not write to file "/log/' . $filename . '".', __FILE__, __LINE__);
		}

		fclose($handle);
	}
}
