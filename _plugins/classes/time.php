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
		$timeZone   = 'GMT',
		$timeOffset = 0
		;

	/**
	 * Initialize
	 * @param object $model
	 */
	function __construct($model)
	{
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

		date_default_timezone_set($this->timeZone);

		$this->timeOffset =
			mktime(date('H'),   date('i'),   date('s'),   date('n'),   date('j'),   date('Y')) -
			mktime(gmdate('H'), gmdate('i'), gmdate('s'), gmdate('n'), gmdate('j'), gmdate('Y'))
			;

		$this->ready = TRUE;
	}

	/**
	 * Format a date
	 * @param string $date
	 * @param string $type
	 * @return string
	 */
	function format_date($date, $type = 'datetime')
	{
		if ( $timestamp = strtotime($date) + $this->timeOffset )
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

		return $date . ( $this->timeZone == 'GMT' ? ' GMT' : '' );
	}
}
