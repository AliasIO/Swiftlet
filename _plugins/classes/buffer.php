<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

if ( !isset($model) ) die('Direct access to this file is not allowed');

/**
 * Buffer
 * @abstract
 */
class buffer
{
	public
		$ready = FALSE
		;

	private
		$model,
		$contr
		;

	/**
	 * Initialize buffer
	 * @param object $model
	 */
	function __construct($model)
	{
		$this->model = $model;
		$this->contr = $model->contr;
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

 			if ( ob_get_length() > 0 )
			{
				ob_end_clean();
			}

			$this->ready = FALSE;

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
