<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU Public License
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
		$config          = array(),
		$configMissing   = FALSE,
		$consoleMessages = array(),
		$controller,
		$debugOutput     = array(),
		$plugins         = array(),
		$userIp          = '',
		$view
		;

	/**
	 * Initialize
	 */
	function __construct()
	{
		ini_set('display_errors', 1);

		set_error_handler(array($this, 'error'), E_ALL | E_STRICT);

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

		$this->view = new View($this);

		/*
		 * Plugins
		 */
		if ( $handle = opendir('_app/plugins') )
		{
			while ( ( $filename = readdir($handle) ) !== FALSE )
			{
				if ( is_file('_app/plugins/' . $filename) && preg_match('/^[A-Z][A-Za-z0-9_]*\.php$/', $filename) )
				{
					require('_app/plugins/' . $filename);

					$plugin = strtolower(basename($filename, '.php'));

					$className = basename($filename, '.php') . '_Plugin';

					if ( class_exists($className) )
					{
						$this->{$plugin} = new $className($this, $plugin, $filename, $className);
					}
					else
					{
						$this->error(FALSE, 'Class `' . $className . '` missing from file `/_app/plugins/' . $this->view->h($filename) . '`.');
					}

					$this->plugins[$plugin] = $plugin;
				}
			}

			closedir($handle);
		}

		$this->hook('init_force');

		$this->hook('init');

		$this->hook('init_after');

		if ( isset($this->db) && in_array($this->db->prefix . 'versions', $this->db->tables) )
		{
			$this->db->sql('
				SELECT
					`plugin`,
					`version`
				FROM `' . $this->db->prefix . 'versions`
				;');

			if ( $r = $this->db->result )
			{
				foreach ( $r as $d )
				{
					if ( isset($this->{$d['plugin']}) )
					{
						$this->{$d['plugin']}->installed = $d['version'];
					}
				}
			}
		}

		/*
		 * Controller
		 */
		require('_controllers/' . $this->view->controller . '.php');

		$className = basename($this->view->controller) . '_Controller';

		if ( class_exists($className) )
		{
			$this->controller = new $className($this);
		}
		else
		{
			$this->error(FALSE, 'Class `' . $className . '` missing from file `/_controllers/' . $this->view->h($this->view->controller) . '.php`.');
		}

		$this->controller->init();

		$this->end();
	}

	/**
	 * Hook a plugin
	 * @param string $plugin
	 * @param array $params
	 */
	function hook($hook, &$params = array())
	{
		if ( !empty($this->hooksRegistered[$hook]) )
		{
			$pluginsSkipped = array();

			/**
			 * Hook plugins in order
			 */
			usort($this->hooksRegistered[$hook], function($a, $b) { return $a['order'] == $b['order'] ? 0 : $a['order'] > $b['order'] ? 1 : -1; });

			foreach ( $this->hooksRegistered[$hook] as $plugin )
			{
				$missing = array();

				// Check dependencies
				if ( $this->{$plugin['name']}->dependencies )
				{
					foreach ( $this->{$plugin['name']}->dependencies as $dependency )
					{
						if ( !isset($this->{$dependency}) )
						{
							$missing[] = $dependency;
						}
					}
				}

				if ( !$missing )
				{
					if ( $hook == 'install' || $hook == 'init_force' || $this->{$plugin['name']}->installed )
					{
						$timerStart = microtime(TRUE);

						if ( !method_exists(get_class($this->{$plugin['name']}), $hook) )
						{
							$this->error(FALSE, 'The plugin `' . $plugin['name'] . '` has no hook `' . $hook . '`.');
						}

						$this->{$plugin['name']}->{$hook}($params);

						$this->pluginsHooked[$plugin['name']][$hook] = TRUE;

						$this->debugOutput['plugins hooked']['hook: ' . $hook][] = array(
							'order'          => $plugin['order'],
							'plugin'         => $plugin['name'],
							'execution time' => round(microtime(TRUE) - $this->timerStart, 3) . ' sec'
							);
					}
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

		ob_start();

		$this->view->output();

		$this->debugOutput['execution time']['all'] = round(microtime(TRUE) - $this->timerStart,   3) . ' sec';
		$this->debugOutput['peak memory usage']     = round(memory_get_peak_usage() / 1024 / 1024, 3) . ' MB';

		$this->console(array('DEBUG OUTPUT' => $this->debugOutput), 'info');

		// Write debug messages to console
		if ( $this->config['debugMode'] && !$this->controller->standAlone && $this->consoleMessages )
		{
			$messages = array();

			foreach ( $this->consoleMessages as $message )
			{
				$messages[] = 'console.' . $message['type'] . '(unescape(\'' . rawurlencode('SWIFTLET ' . addslashes($message['file']) . ' on line ' . ( int ) $message['line']) . '\n\n' . rawurlencode($message['message']) . '\'));';
			}

			echo '
				<script type="text/javascript">
					/* <![CDATA[ */
					window.onload = function() {
						if ( typeof(console) != \'undefined\' ) {
							' . implode("\n", $messages) . '
						}
					};
					/* ]]> */
				</script>
				';
		}

		$this->hook('end');

		ob_end_flush();

		exit;
	}

	/**
	 * Write debug messages to browser's JavaScript console
	 */
	function console($message, $type = 'debug')
	{
		if ( $this->config['debugMode'] )
		{
			$backtrace = debug_backtrace();

			ob_start();

			print_r($message);

			$message = ob_get_contents();

			ob_end_clean();

			$this->consoleMessages[] = array(
				'message' => $message,
				'type'    => $type,
				'file'    => $backtrace[0]['file'],
				'line'    => $backtrace[0]['line'],
				);
		}
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

						' . ( $this->config['debugMode'] ? '
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

		if ( $this->config['debugMode'] )
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

				print_r(debug_backtrace(FALSE));

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
							Please contact us at <a href="mailto:' . $this->view->h($this->config['adminEmail']) . '">' . $this->view->h($this->config['adminEmail']) . '</a>.
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
