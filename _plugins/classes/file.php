<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

if ( !isset($model) ) die('Direct access to this file is not allowed');

/**
 * File
 * @abstract
 */
class file
{
	public
		$ready
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

		if ( !empty($model->db->ready) )
		{
			/**
			 * Check if the pages table exists
			 */
			if ( in_array($model->db->prefix . 'files', $model->db->tables) )
			{
				$this->ready = TRUE;
			}
		}
	}
}