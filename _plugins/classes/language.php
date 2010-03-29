<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

if ( !isset($model) ) die('Direct access to this file is not allowed');

/**
 * Language
 * @abstract
 */
class lang
{
	public
		$language  = 'English US',
		$languages = array('English US' => 'English US'),
		$ready
		;

	private
		$model,
		$contr,
		$translation = array()
		;

	/**
	 * Initialize
	 * @param object $model
	 */
	function __construct($model)
	{
		$this->model = $model;
		$this->contr = $model->contr;

		$contr = $this->contr;

		$this->check_languages();
		
		if ( !empty($model->session->ready) )
		{
			if ( $d = $model->session->get('pref_values') )
			{
				if ( isset($d['Language']) )
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
		$model = $this->model;
		$contr = $this->contr;

		if ( !empty($model->user->ready) )
		{
			$this->languages = array('English US' => 'English US');

			if ( is_dir($dir = $contr->rootPath . 'lang/') )
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

			if ( !isset($model->user->prefs['Language']['options']) || $this->languages != $model->user->prefs['Language']['options'] )
			{
				$model->user->save_pref(array(
					'pref'    => 'Language',
					'type'    => 'select',
					'match'   => '/.*/',
					'options' => serialize($this->languages)
					));
			}
		}
	}

	/**
	 * Load a language file
	 */
	function load()
	{
		$contr = $this->contr;

		$this->translation = array();

		if ( $this->language )
		{
			if ( is_dir($dir = $contr->rootPath . 'lang/' . $this->language . '/') )
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
