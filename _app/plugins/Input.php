<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

if ( !isset($this) ) die('Direct access to this file is not allowed');

class Input extends Plugin
{
	public
		$version    = '1.0.0',
		$compatible = array('from' => '1.2.0', 'to' => '1.2.*'),
		$hooks      = array('footer' => 1, 'init' => 2)
		;

	private
		$typesRegex = array(
			'bool'   => '/^.*$/',
			'empty'  => '/^$/',
			'int'    => '/^-?[0-9]{1,256}$/',
			'string' => '/^.{1,256}$/',
			'email'  => '/^([\w\!\#$\%\&\'\*\+\-\/\=\?\^\`{\|\}\~]+\.)*[\w\!\#$\%\&\'\*\+\-\/\=\?\^\`{\|\}\~]+@((((([a-z0-9]{1}[a-z0-9\-]{0,62}[a-z0-9]{1})|[a-z])\.)+[a-z]{2,6})|(\d{1,3}\.){3}\d{1,3}(\:\d{1,5})?)$/i'
			)
		;

	function hook_init()
	{
		$this->ready = TRUE;

		/**
		 * Authenticity token to secure forms
		 * @see http://en.wikipedia.org/wiki/Cross-site_request_forgery
		 */
		if ( !session_id() )
		{
			session_start();
		}

		$this->authToken = sha1(session_id() . phpversion() . $this->app->config['sysPassword'] . $this->app->userIp . ( !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '' ));

		if ( ( !empty($_POST) && !isset($_POST['auth-token']) ) || ( isset($_POST['auth-token']) && $_POST['auth-token'] != $this->authToken ) )
		{
			$this->app->error(FALSE, 'The form has expired, please go back and try again (wrong or missing authenticity token).', __FILE__, __LINE__);
		}

		if ( isset($_POST['auth-token']) )
		{
			unset($_POST['auth-token']);
		}

		$this->input_sanitize();

		$this->ready = TRUE;

	}

	function hook_footer()
	{
		if ( !empty($this->errors) )
		{
			$this->app->view->load('input_errors.html.php');
		}
	}

	/**
	 * Redirect to confirmation page
	 * @param string $notice
	 */
	function confirm($notice)
	{
		$this->app->view->notice  = $notice;
		$this->app->view->getData = $this->app->view->h(serialize($this->GET_raw));

		$this->app->view->load('confirm.html.php');

		$this->app->end();
	}

	/**
	 * Undo magic quotes
	 * @param mixed $v
	 * @return mixed $v
	 * @see http://php.net/magic_quotes
	 */
	private function undo_magic_quotes($v)
	{
		return is_array($v) ? array_map(array($this, 'undo_magic_quotes'), $v) : stripslashes($v);
	}

	/**
	 * Sanatize user input
	 */
	private function input_sanitize()
	{
		/**
		 * Recursively remove magic quotes
		 */
		if ( function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc() )
		{
			$_GET    = array_map(array($this, 'undo_magic_quotes'), $_GET);
			$_POST   = array_map(array($this, 'undo_magic_quotes'), $_POST);
			$_COOKIE = array_map(array($this, 'undo_magic_quotes'), $_COOKIE);
		}

		/*
		 * Check integrety of confirmed information (see $this->confirm())
		 */
		if ( isset($_POST['confirm']) && !empty($_POST['get-data']) && !empty($_GET) )
		{
			if ( unserialize($_POST['get-data']) != $_GET )
			{
				unset($_POST['confirm']);
			}
		}

		/*
		 * $_POST and $_GET values can't be trusted
		 * If neccesary, access them through $this->POST_raw and $this->GET_raw
		 */
		$this->POST_raw = isset($_POST) ? $_POST : array();
		$this->GET_raw  = isset($_GET)  ? $_GET  : array();

		unset($_POST, $_GET);

		foreach ( $this->POST_raw as $k => $v )
		{
			$this->POST_html_safe[$k] = $this->app->view->h($v);
		}

		foreach ( $this->GET_raw as $k => $v )
		{
			$this->GET_html_safe[$k] = $this->app->view->h($v);
		}

		$this->app->hook('input_sanitize');
	}

	/**
	 * Validate POST data
	 * @param array $vars
	 */
	function validate($vars)
	{
		$this->errors = array();

		$vars['confirm'] = 'bool';

		foreach ( $vars as $var => $types )
		{
			if ( !isset($this->POST_raw[$var]) )
			{
				$this->POST_raw[$var]       = FALSE;
				$this->POST_html_safe[$var] = FALSE;
				$this->POST_valid[$var]     = FALSE;
			}
			else
			{
				$this->POST_valid[$var] = FALSE;

				$regexes = array();

				foreach ( explode(',', $types) as $type )
				{
					$type = trim($type);

					$regexes[] = isset($this->typesRegex[$type]) ? $this->typesRegex[$type] : $type;
				}

				$this->POST_valid[$var] = $this->check($this->POST_raw[$var], $regexes);

				if ( $this->POST_valid[$var] === FALSE )
				{
					$this->errors[$var] = $this->app->view->t('Invalid value');
				}
			}
		}

		$this->app->hook('input_sanitize');
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
