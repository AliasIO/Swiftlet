<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

if ( !isset($model) ) die('Direct access to this file is not allowed');

/*
 * Form
 * @abstract
 */
class form
{
	public
		$ready
		;
	
	private
		$model,
		$view,
		$contr,

		$typesRegex = array(
			'bool'   => '/^.*$/',
			'empty'  => '/^$/',
			'int'    => '/^-?[0-9]{1,256}$/',
			'string' => '/^.{1,256}$/',
			'email'  => '/^([\w\!\#$\%\&\'\*\+\-\/\=\?\^\`{\|\}\~]+\.)*[\w\!\#$\%\&\'\*\+\-\/\=\?\^\`{\|\}\~]+@((((([a-z0-9]{1}[a-z0-9\-]{0,62}[a-z0-9]{1})|[a-z])\.)+[a-z]{2,6})|(\d{1,3}\.){3}\d{1,3}(\:\d{1,5})?)$/i'
			)
		;

	/**
	 * Initialize form
	 * @param object $model
	 */
	function __construct($model)
	{
		$this->model = $model;
		$this->view  = $model->view;
		$this->contr = $model->contr;
		
		$this->ready = TRUE;
	}

	/**
	 * Validate form data
	 * @param array $vars
	 */
	function validate($vars)
	{
		$this->errors = array();
		
		$vars['confirm'] = 'bool';

		foreach ( $vars as $var => $types )
		{
			if ( !isset($this->model->POST_raw[$var]) )
			{
				$this->model->POST_raw[$var]       = FALSE;
				$this->model->POST_html_safe[$var] = FALSE;
				$this->model->POST_valid[$var]     = FALSE;
			}
			else
			{
				$this->model->POST_valid[$var] = FALSE;

				$regexes = array();

				foreach ( explode(',', $types) as $type )
				{
					$type = trim($type);

					$regexes[] = isset($this->typesRegex[$type]) ? $this->typesRegex[$type] : $type;
				}

				$this->model->POST_valid[$var] = $this->check($this->model->POST_raw[$var], $regexes);

				if ( $this->model->POST_valid[$var] === FALSE )
				{
					$this->errors[$var] = TRUE;
				}
			}
		}
	}
	
	private function check($var, $regexes)
	{
		if ( is_array($var) )
		{
			foreach ( $var as $k => $v )
			{
				$var[$k] = $this->check($v, $regexes);
			}
			
			return $var;
		}
		else
		{
			foreach ( $regexes as $regex )
			{
				if ( preg_match($regex, $var) )
				{
					return $var;
				}
			}

			return FALSE;
		}
	}
}
