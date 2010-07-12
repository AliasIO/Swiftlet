<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

if ( !isset($app) ) die('Direct access to this file is not allowed');

/**
 * Cache
 * @abstract
 */
class cache
{
	public
		$ready = FALSE
		;

	private
		$app,
		$view,
		$contr,

		$cacheLifeTime = 3600
		;

	/**
	 * Initialize buffer
	 * @param object $app
	 */
	function __construct($app)
	{
		$this->app  = $app;
		$this->view  = $app->view;
		$this->contr = $app->contr;

		$this->ready = TRUE;

		$this->read();
	}

	/**
	 * Read a file from cache
	 * @return bool
	 */
	private function read()
	{
		if ( !empty($this->app->session->ready) && !empty($this->app->user->ready) && $this->app->session->get('user id') != user::guestId )
		{
			return;
		}

		if ( $this->app->caching && empty($this->app->POST_raw) && empty($_POST) )
		{
			if ( $handle = opendir($contr->rootPath . 'cache') )
			{
				while ( $filename = readdir($handle) )
				{
					if ( is_file($this->contr->rootPath . 'cache/' . $filename) )
					{					
						list($time, $hash) = explode('_', $filename);

						if ( $time <= time() )
						{
							@unlink($this->contr->rootPath . 'cache/' . $filename);
						}
						else
						{
							if ( $hash == sha1($_SERVER['REQUEST_URI']) )
							{
								if ( $this->app->debugMode )
								{
									header('X-Swiftlet-Cache: HIT');
								}
								
								echo file_get_contents($this->contr->rootPath . 'cache/' . $filename);

								$this->app->buffer->flush();

								exit;
							}
						}
					}
				}

				closedir($handle);
			}
		}
	}

	/**
	 * Write a file to cache
	 * @param string $v
	 */
	function write(&$contents)
	{
		if ( !empty($this->app->session->ready) && !empty($this->app->user->ready) && $this->app->session->get('user id') != user::guestId )
		{
			return;
		}

		if ( $headers = headers_list() )
		{
			foreach ( $headers as $header )
			{
				if ( preg_match('/^Content\-type:/i', $header) )
				{
					return;
				}
			}
		}

		if ( $this->app->caching && empty($this->app->POST_raw) )
		{
			if ( !is_dir($contr->rootPath . 'cache') )
			{
				$this->app->error(FALSE, 'Directory "/cache" does not exist.', __FILE__, __LINE__);
			}

			if ( !is_writable($contr->rootPath . 'cache') )
			{
				$this->app->error(FALSE, 'Directory "/cache" is not writable.', __FILE__, __LINE__);
			}

			$filename = ( time() + $this->cacheLifeTime ) . '_' . sha1($_SERVER['REQUEST_URI']);

			if ( !$handle = fopen($contr->rootPath . 'cache/' . $filename, 'a+') )
			{
				$this->app->error(FALSE, 'Could not open file "/cache/' . $filename . '".', __FILE__, __LINE__);
			}

			if ( fwrite($handle, $contents) === FALSE )
			{
				$this->app->error(FALSE, 'Could not write to file "/cache/' . $filename . '".', __FILE__, __LINE__);
			}

			fclose($handle);
		}

		unset($contents);
	}
	
	/**
	 * Clear cache
	 */
	function clear()
	{
		if ( $handle = opendir($this->contr->rootPath . 'cache') )
		{
			while ( $filename = readdir($handle) )
			{
				if ( is_file($this->contr->rootPath . 'cache/' . $filename) )
				{
					$r = @unlink($this->contr->rootPath . 'cache/' . $filename);

					if ( !$r )
					{
						$this->app->error(FALSE, 'Could not deleted cached file "/cache/' . $filename . '", please check permissions or the delete the file manually.', __FILE__, __LINE__);
					}
				}
			}

			closedir($handle);
		}
	}
}
