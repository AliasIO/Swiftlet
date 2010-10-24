<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU Public License
 */

if ( !isset($this) ) die('Direct access to this file is not allowed');

/**
 * Language
 * @abstract
 */
class Language_Plugin extends Plugin
{
	public
		$version      = '1.0.0',
		$compatible   = array('from' => '1.3.0', 'to' => '1.3.*'),
		$hooks        = array('init' => 5, 'translate' => 1),

		$language  = 'English US',
		$languages = array('English US' => 'English US')
		;

	private
		$translation = array()
		;

	/*
	 * Implement init hook
	 */
	function init()
	{
		$this->check_languages();

		if ( !empty($this->app->session->ready) )
		{
			if ( $d = $this->app->session->get('pref_values') )
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

			if ( is_dir($dir = '../../lang/') )
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
			if ( is_dir($dir = '../../lang/' . $this->language . '/') )
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
	 * @param array $params
	 */
	function translate(&$params)
	{
		$params['string'] = isset($this->translation[$params['string']]) ? $this->translation[$params['string']] : $params['string'];
	}
}
