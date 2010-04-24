<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

if ( !isset($model) ) die('Direct access to this file is not allowed');

/**
 * Buffer
 * @abstract
 */
class buffer
{
	public
		$ready = FALSE
		;

	private
		$model,
		$contr,

		$cacheLifeTime = 3600
		;

	/**
	 * Initialize buffer
	 * @param object $model
	 */
	function __construct($model)
	{
		$this->model = $model;
		$this->contr = $model->contr;
	}

	/**
	 * Start buffering
	 */
	function start()
	{
		if ( !$this->ready )
		{
			ob_start();

			$this->ready = TRUE;

			$this->read_cache();
		}
	}

	/**
	 * Flush the buffer, send output to the browser
	 */
	function flush()
	{
		if ( $this->ready )
		{
			$contents = ob_get_contents();

 			if ( ob_get_length() > 0 )
			{
				ob_end_clean();
			}

			$this->ready = FALSE;

			echo $contents;
		}
	}

	/**
	 * Clean the buffer, cancel output
	 */
	function clean()
	{
		if ( $this->ready )
		{
			if ( ob_get_length() > 0 )
			{
				ob_end_clean();
			}
		
			$this->active = FALSE;
		}
	}

	/**
	 * Read a file from cache
	 * @return bool
	 */
	function read_cache()
	{
		$model = $this->model;
		$contr = $model->contr;
		
		if ( $this->ready && $model->caching && empty($model->POST_raw) && empty($_POST) )
		{
			if ( $handle = opendir($contr->rootPath . 'cache') )
			{
				while ( $filename = readdir($handle) )
				{
					if ( is_file($contr->rootPath . 'cache/' . $filename) )
					{					
						list($time, $hash) = explode('_', $filename);

						if ( $time <= time() )
						{
							@unlink($contr->rootPath . 'cache/' . $filename);
						}
						else
						{
							if ( $hash == sha1($_SERVER['REQUEST_URI']) )
							{
								if ( $model->debugMode )
								{
									echo '<!-- Served from cache -->' . "\n";
								}
								
								echo file_get_contents($contr->rootPath . 'cache/' . $filename);

								$this->flush();

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
	function write_cache()
	{
		$model = $this->model;
		$contr = $model->contr;

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

		if ( $this->ready && $model->caching && empty($model->POST_raw) )
		{
			$contents = ob_get_contents();

			if ( !is_dir($contr->rootPath . 'cache') )
			{
				$this->model->error(FALSE, 'Directory "/cache" does not exist.', __FILE__, __LINE__);
			}

			if ( !is_writable($contr->rootPath . 'cache') )
			{
				$this->model->error(FALSE, 'Directory "/cache" is not writable.', __FILE__, __LINE__);
			}

			$filename = ( time() + $this->cacheLifeTime ) . '_' . sha1($_SERVER['REQUEST_URI']);

			if ( !$handle = fopen($contr->rootPath . 'cache/' . $filename, 'a+') )
			{
				$this->model->error(FALSE, 'Could not open file "/cache/' . $filename . '".', __FILE__, __LINE__);
			}

			if ( fwrite($handle, $contents) === FALSE )
			{
				$this->model->error(FALSE, 'Could not write to file "/cache/' . $filename . '".', __FILE__, __LINE__);
			}

			fclose($handle);
		}
	}
}
