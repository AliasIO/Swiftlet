<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU Public License
 */

if ( !isset($this) ) die('Direct access to this file is not allowed');

class Test_Plugin extends Plugin
{
	public
		$version    = '1.0.0',
		$compatible = array('from' => '1.3.0', 'to' => '1.3.*'),
		$ready      = TRUE
		;

	/**
	 * Make a POST request
	 * @param string $url
	 * @param array $params
	 * @return array
	 */
	function post_request($url, $params, $guest = FALSE)
	{
		if ( function_exists('curl_init') )
		{
			$cookies = '';

			if ( !$guest )
			{
				// Let's hijack your session for this request
				if ( !empty($_COOKIE) )
				{
					foreach ( $_COOKIE as $k => $v )
					{
						$cookies .= $k . '=' . $v . ';';
					}
				}
			}

			$handle = curl_init();

			$options = array(
				CURLOPT_URL            => $url,
				CURLOPT_COOKIE         => $cookies,
				CURLOPT_USERAGENT      => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
				CURLOPT_HEADER         => FALSE,
				CURLOPT_RETURNTRANSFER => TRUE,
				CURLOPT_FOLLOWLOCATION => TRUE,
				CURLOPT_MAXREDIRS      => 3,
				CURLOPT_POST           => TRUE,
				CURLOPT_POSTFIELDS     => $params
				);

			curl_setopt_array($handle, $options);

			// We can't use the same session twice, close the current one
			$this->app->session->end();

			session_write_close();

			$output = curl_exec($handle);

			// Restore session
			session_start();

			$result = array(
				'output' => $output,
				'info'   => curl_getinfo($handle)
				);

			curl_close($handle);

			unset($output);

			return $result;
		}
		else
		{
			$this->app->error(FALSE, 'Your PHP installation does not support cURL, a requirement for some unit tests.');
		}
	}
}
