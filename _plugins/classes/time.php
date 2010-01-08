<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

if ( !isset($model) ) die('Direct access to this file is not allowed');

/**
 * Time
 * @abstract
 */
class time
{
	public
		$ready,
		$dateFormat = 'M j, Y',
		$timeFormat = 'h:i',
		$timeZone   = ''
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

		if ( !empty($model->session->ready) )
		{
			if ( $d = $model->session->get('pref_values') )
			{
				if ( isset($d['Time zone']) )
				{
					$this->timeZone = $d['Time zone'];
				}
			}
		}

		$this->ready = TRUE;
	}

	/**
	 * Format a date
	 * @param string $v
	 * @return string
	 */
	function format_date($date, $type = 'datetime')
	{
		if ( $timestamp = strtotime($date) )
		{
			switch ( $type )
			{
				case 'date':
					$date = date($this->dateFormat, $timestamp);

					break;
				case 'time':
					$date = date($this->timeFormat, $timestamp);

					break;
				default:
					$date = date($this->dateFormat . ' ' . $this->timeFormat, $timestamp);
			}
		}

		return $date . ' - ' . $this->timeZone;
	}
}