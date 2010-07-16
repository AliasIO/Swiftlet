<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

if ( !isset($controllerSetup) ) die('Direct access to this file is not allowed');

/**
 * Application
 * @abstract
 */
class Application
{
	const
		VERSION = '1.2.0'
		;

	public
		$configMissing = FALSE,
		$debugMode     = TRUE
		;

	/**
	 * Initialize
	 * @param object $controller
	 */
	function __construct($controller)
	{
		$app = $this;

		$this->controller = $controller;

		set_error_handler(array($this, 'error'), E_ALL);

		$this->timerStart = $this->timer_start();

		$this->debugOutput['version'] = Application::VERSION;

		/**
		 * Get the user's real IP address (this can be either IPv4 or IPv6)
		 */
		$this->userIp = !empty($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : ( !empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : ( !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '' ) );

		/*
		 * Load configuration
		 */
		if ( is_file($this->controller->rootPath . '_config.php') )
		{
			require($this->controller->rootPath . '_config.php');
		}
		else
		{
			if ( is_file($this->controller->rootPath . '_config.default.php') )
			{
				require($this->controller->rootPath . '_config.default.php');

				$this->configMissing = TRUE;
			}
			else
			{
				$this->error(FALSE, 'Missing configuration file.');
			}
		}

		if ( isset($config) )
		{
			foreach ( $config as $k => $v )
			{
				$this->{$k} = $v;
			}

			unset($config);
		}

		/*
		 * View
		 */
		if ( !class_exists('View') )
		{
			require($this->controller->rootPath . '_app/View.php');
		}

		$this->view = new View($this);

		/*
		 * Plugins
		 */
		if ( !class_exists('Plugin') )
		{
			require($this->controller->rootPath . '_app/Plugin.php');
		}

		if ( $handle = opendir($dir = $this->controller->pluginPath) )
		{
			while ( ( $file = readdir($handle) ) !== FALSE )
			{
				if ( is_file($dir . $file) && preg_match('/\.php$/', $file) )
				{
					$this->plugin_load($file);
				}
			}

			closedir($handle);
		}

		$this->hook('init');
	}

	/**
	 * Initialize plugin
	 * @param string plugin
	 */
	private function plugin_load($file)
	{
		$plugin = new Plugin($this, $file);

		$this->pluginsLoaded[$plugin->info['name']] = $plugin;
	}

	/**
	 * Hook a plugin
	 * @param string plugin
	 */
	function hook($hook, &$params = array())
	{
		if ( !empty($this->hooksRegistered[$hook]) )
		{
			/**
			 * Hook plugins in order
			 */
			usort($this->hooksRegistered[$hook], array($this, 'hook_sort'));

			foreach ( $this->hooksRegistered[$hook] as $plugin )
			{
				$this->pluginsLoaded[$plugin['name']]->hook($hook, $plugin['order'], $params);
			}
		}
	}

	/**
	 * Sort plugins by order of inclusion
	 * @param array $a
	 * @param array $b
	 * @return int
	 */
	private function hook_sort($a, $b)
	{
		return ( $a['order'] == $b['order'] ) ? 0 : $a['order'] > $b['order'] ? 1 : -1;
	}

	/**
	 * Register a hook
	 * @param string $name
	 * @param array $hooks
	 * @param array $params
	 */
	function hook_register($pluginName, $hooks)
	{
		foreach ( $hooks as $hook => $order )
		{
			$this->hooksRegistered[$hook][] = array('name' => $pluginName, 'order' => $order);
		}
	}

	/**
	 * Check to see if required plugins are ready
	 * @param array $dependencies
	 */
	function check_dependencies($dependencies)
	{
		$missing = array();

		foreach ( $dependencies as $dependency )
		{
			if ( empty($this->{$dependency}->ready ) )
			{
				$missing[] = $dependency;
			}
		}

		if ( $missing )
		{
			$this->error(FALSE, 'Plugins required for this page: `' . implode('`, `', $missing) . '`.', __FILE__, __LINE__);
		}
	}

	/**
	 * Send e-mail
	 * @param string $to
	 * @param string $subject
	 * @param string $body
	 * @param string $headers
	 * @return bool
	 */
	function email($to, $subject, $body, $headers = array())
	{
		$params = array(
			'to'      => $to,
			'subject' => $subject,
			'body'    => $body,
			'headers' => $headers
			);

		$this->hook('email', $params);

		return $params['success'];
	}

	/**
	 * Clear cache
	 */
	function clear_cache()
	{
		$this->hook('clear_cache');
	}

	/**
	 * Start a timer
	 * @return int
	 */
	function timer_start()
	{
		return array_sum(explode(' ', microtime()));
	}

	/**
	 * End a timer
	 * @param int $timerStart
	 * @return int
	 */
	function timer_end($timerStart)
	{
		return round($this->timer_start() - $timerStart, 3) . ' sec';
	}

	/**
	 * Wrap up and exit
	 */
	function end()
	{
		if ( empty($this->controller->standAlone) )
		{
			$this->hook('footer');
		}

		$this->view->output();

		$this->debugOutput['execution time']['all'] = $this->timer_end($this->timerStart);
		$this->debugOutput['peak memory usage']     = round(memory_get_peak_usage() / 1024 / 1024, 3) . ' MB';

		$this->hook('end');

		exit;
	}

	/**
	 * Display an error message
	 * @param string $errNo
	 * @param string $errStr
	 * @param string $errFile
	 * @param string $errLine
	 * @param array $errCont
	 */
	function error($errNo, $errStr, $errFile = '', $errLine = '', $errCont = array())
	{
		$this->hook('error');

		// If error has been supressed with @
		if ( !error_reporting() )
		{
			return;
		}

		if ( !headers_sent() )
		{
			header('HTTP/1.1 503 Service Temporarily Unavailable');
		}

		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
			<html id="swiftlet_error">
				<head>
					<title>-</title>

					<style>
						#swiftlet_error body {
							color: #000;
							font-family: monospace;
							font-size: 12px;
						}

						#swiftlet_error div {
							background: #FFC;
							border: 1px solid #DD7;
							border-radius: 3px;
							-moz-border-radius: 3px;
							-webkit-border-radius: 3px;
							color: #300;
							padding: 1em 2em;
						}

						#swiftlet_error a:link, #swiftlet_error a:hover, #swiftlet_error a:active, #swiftlet_error a:visited {
							color: #AA4;
						}
					</style>
				</head>
				<body>
					<div>
						<p>
							Oops! An error occured.
						</p>

						' . ( $this->debugMode ? '
						<p>[
							<a href="javascript: void(0);" onclick="
								e = document.getElementById(\'error_debug_mode\');
								e.style.display = e.style.display == \'none\' ? \'block\' : \'none\';
								">
								DEBUG MODE</a> ]
						</p>

						<p id="error_debug_mode" style="display: block; margin-left: 2em;">
							' . ( $errStr   ? 'MESSAGE: <strong><br/><br/>' . $errStr  . '</strong><br/><br/>' : '' ) . '
							' . ( $errFile  ? 'FILE:    <strong>'           . $errFile . '</strong><br/>'      : '' ) . '
							' . ( $errLine  ? 'LINE:    <strong>'           . $errLine . '</strong><br/>'      : '' ) . '
							' . ( $errNo    ? 'NO:      <strong>'           . $errNo   . '</strong><br/>'      : '' ) . '
							' . ( $errNo    ? 'CWD:     <strong>'           . getcwd() . '</strong><br/>'      : '' ) . '
						</p>' : '' ) . '
			';

		if ( $this->debugMode )
		{
			if ( count(debug_backtrace(FALSE)) > 1 )
			{
				echo '
					<p>[
						<a href="javascript: void(0);" onclick="
							e = document.getElementById(\'error_backtrace\');
							e.style.display = e.style.display == \'none\' ? \'block\' : \'none\';
							">BACKTRACE</a> ]
					</p>

					<pre id="error_backtrace" style="display: none; margin-left: 2em;">';

				print_r(array_pop(debug_backtrace(FALSE)));

				echo '</pre>';
			}

			if ( empty($this->debugOutput) )
			{
				echo '
					<p>[
						<a href="javascript: void(0);" onclick="
							e = document.getElementById(\'error_debug_output\');
							e.style.display = e.style.display == \'none\' ? \'block\' : \'none\';
							">DEBUG OUTPUT</a> ]
					</p>

					<pre id="error_debug_output" style="display: none; margin-left: 2em;">';

				print_r($this->debugOutput);

				echo '</pre>';
			}
		}

		if ( $this->adminEmail )
		{
			echo '
						<p>
							<br/>
							Please contact us at <a href="mailto:' . $view->h($this->adminEmail) . '">' . $view->h($this->adminEmail) . '</a>.
						</p>
				';
		}

		echo '
					</div>
				</body>
			</html>
			';

		exit;
	}
}
