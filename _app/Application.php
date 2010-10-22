<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

if ( !isset($swiftlet) ) die('Direct access to this file is not allowed');

/**
 * Application
 * @abstract
 */
class Application
{
	const
		VERSION = '1.3.0'
		;

	public
		$configMissing = FALSE,
		$debugMode     = TRUE,
		$plugins       = array()
		;

	/**
	 * Initialize
	 */
	function __construct()
	{
		set_error_handler(array($this, 'error'), E_ALL);

		$this->timerStart = microtime(TRUE);

		$this->debugOutput['version'] = Application::VERSION;

		/**
		 * Get the user's real IP address (this can be either IPv4 or IPv6)
		 */
		$this->userIp = !empty($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : ( !empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : ( !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '' ) );

		/*
		 * Load configuration
		 */
		if ( is_file('_config.php') )
		{
			require('_config.php');
		}
		else
		{
			if ( is_file('_config.default.php') )
			{
				require('_config.default.php');

				$this->configMissing = TRUE;
			}
			else
			{
				$this->error(FALSE, 'Missing configuration file.');
			}
		}

		chdir(dirname(__FILE__));

		$this->view = new View($this);

		/*
		 * Plugins
		 */
		chdir('plugins');

		if ( $handle = opendir('.') )
		{
			while ( ( $file = readdir($handle) ) !== FALSE )
			{
				if ( is_file($file) && preg_match('/\.php$/', $file) )
				{
					require($file);

					$plugin      = basename($file, '.php');
					$pluginClass = $plugin . '_Plugin';

					$plugin = strtolower($plugin);

					$this->{$plugin} = new $pluginClass($this, $plugin, $file, $pluginClass);

					$this->plugins[$plugin] = $plugin;
				}
			}

			closedir($handle);
		}

		$this->hook('init');

		chdir('../../');

		require('_controllers/' . $this->view->controller . '.php');

		$controllerClass = basename($this->view->controller) . '_Controller';

		$this->controller = new $controllerClass($this);

		$this->controller->init();

		$this->end();
	}

	/**
	 * Hook a plugin
	 * @param string plugin
	 */
	function hook($hook, &$params = array())
	{
		if ( !empty($this->hooksRegistered[$hook]) )
		{
			$pluginsSkipped = array();

			/**
			 * Hook plugins in order
			 */
			usort($this->hooksRegistered[$hook], array($this, 'hook_sort'));

			foreach ( $this->hooksRegistered[$hook] as $plugin )
			{
				$missing = array();

				// Check dependencies
				if ( $this->{$plugin['name']}->dependencies )
				{
					foreach ( $this->{$plugin['name']}->dependencies as $dependency )
					{
						if ( empty($this->{$dependency}->ready) )
						{
							$missing[] = $dependency;
						}
					}
				}

				if ( !$missing )
				{
					$timerStart = microtime(TRUE);

					$this->{$plugin['name']}->{$hook}($params);

					$this->pluginsHooked[$plugin['name']][$hook] = TRUE;

					$this->debugOutput['plugins hooked']['hook: ' . $hook][] = array(
						'order'          => $plugin['order'],
						'plugin'         => $plugin['name'],
						'execution time' => round(microtime(TRUE) - $this->timerStart, 3) . ' sec'
						);
				}
				else
				{
					$pluginsSkipped[$plugin['name']] = $missing;
				}
			}

			if ( $pluginsSkipped )
			{
				foreach ( $pluginsSkipped as $plugin => $dependencies )
				{
					$this->debugOutput['plugins skipped'][] = array(
						'plugin'               => $plugin,
						'missing dependencies' => $dependencies
						);
				}
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
	 * Wrap up and exit
	 */
	function end()
	{
		if ( empty($this->controller->standAlone) )
		{
			$this->hook('footer');
		}

		$this->view->output();

		$this->debugOutput['execution time']['all'] = round(microtime(TRUE) - $this->timerStart,   3) . ' sec';
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

		if ( $this->config['adminEmail'] )
		{
			echo '
						<p>
							<br/>
							Please contact us at <a href="mailto:' . $view->h($this->config['adminEmail']) . '">' . $view->h($this->config['adminEmail']) . '</a>.
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
