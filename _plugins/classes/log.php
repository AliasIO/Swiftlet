<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

if ( !isset($model) ) die('Direct access to this file is not allowed');

/**
 * Log
 * @abstract
 */
class log
{
	public
		$ready = FALSE
		;

	private
		$model,
		$contr
		;

	/**
	 * Initialize
	 * @param object $model
	 */
	function __construct($model)
	{
		$this->model = $model;
		$this->contr = $model->contr;

		$this->ready = TRUE;
	}

	/**
	 * Write to log
	 */
	function write($filename, $contents)
	{
		$contr = $this->contr;
		
		$contents = date('M d H:i:s') . "\t" . $contents . "\n";

		if ( !$handle = fopen($contr->rootPath . 'log/' . $filename, 'a+') )
		{
			$this->model->error(FALSE, 'Could not open file "/log/' . $filename . '".', __FILE__, __LINE__);
		}

		if ( fwrite($handle, $contents) === FALSE )
		{
			$this->model->error(FALSE, 'Could not write to file "/log/' . $filename . '".', __FILE__, __LINE__);
		}

		fclose($handle);
	}
}