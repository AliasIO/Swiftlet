<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

if ( !isset($app) ) die('Direct access to this file is not allowed');

/**
 * Language
 * @abstract
 */
class Lang
{
	public
		$language  = 'English US',
		$languages = array('English US' => 'English US'),
		$ready
		;

	private
		$translation = array(),

		$app,
		$view,
		$controller
		;

	/**
	 * Initialize
	 * @param object $app
	 */
	function __construct($app)
	{
		$this->app        = $app;
		$this->view       = $app->view;
		$this->controller = $app->controller;

		$this->check_languages();

		if ( !empty($app->session->ready) )
		{
			if ( $d = $app->session->get('pref_values') )
			{
				if ( !empty($d['Language']) )
				{
					$this->language = $d['Language'];
				}
			}
		}

		$this->load();

		$this->ready = TRUE;
	}

	/**
	 * Scan the /lang/ directory for language files
	 */
	function check_languages()
	{
		if ( !empty($this->app->user->ready) )
		{
			$this->languages = array('English US' => 'English US');

			if ( is_dir($dir = $this->controller->rootPath . 'lang/') )
			{
				if ( $handle = opendir($dir) )
				{
					while ( ( $file = readdir($handle) ) !== FALSE )
					{
						if ( is_dir($dir . $file) && substr($file, 0, 1) != '.' )
						{
							$this->languages[$file] = $file;
						}
					}

					closedir($handle);
				}
			}

			$this->languages = array_unique($this->languages);

			arsort($this->languages);

			if ( !isset($this->app->user->prefs['Language']['options']) || $this->languages != $this->app->user->prefs['Language']['options'] )
			{
				$this->app->user->save_pref(array(
					'pref'    => 'Language',
					'type'    => 'select',
					'match'   => '/.*/',
					'options' => $this->languages
					));
			}
		}
	}

	/**
	 * Load a language file
	 */
	function load()
	{
		$this->translation = array();

		if ( $this->language )
		{
			if ( is_dir($dir = $this->controller->rootPath . 'lang/' . $this->language . '/') )
			{
				if ( $handle = opendir($dir) )
				{
					while ( ( $file = readdir($handle) ) !== FALSE )
					{
						if ( is_file($dir . $file) && substr($file, -4) == '.php' )
						{
							require($dir . $file);

							if ( !empty($translation) && is_array($translation) )
							{
								$this->translation += $translation;
							}

							unset($translation);
						}
					}

					closedir($handle);
				}
			}
		}
	}

	/**
	 * Translate a string
	 * @param string $v
	 * @return string
	 */
	function translate($v)
	{
		return isset($this->translation[$v]) ? $this->translation[$v] : $v;
	}
}
