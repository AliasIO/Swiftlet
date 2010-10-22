<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

if ( !isset($this) ) die('Direct access to this file is not allowed');

class Log_Plugin extends Plugin
{
	public
		$version    = '1.0.0',
		$compatible = array('from' => '1.3.0', 'to' => '1.3.*'),
		$hooks      = array('unit_tests' => 1)
		;

	/*
	 * Implement unit_test hook
	 * @param array $params
	 */
	function unit_tests(&$params)
	{
		/*
		$this->write('unit_test', 'Test');

		$params[] = array(
			'test' => 'Writing a log file to <code>/log/</code>.',
			'pass' => is_file($this->app->controller->rootPath . 'log/unit_test')
			);

		if ( is_file($this->app->controller->rootPath . 'log/unit_test') )
		{
			unlink($this->app->controller->rootPath . 'log/unit_test');
		}
		*/
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
