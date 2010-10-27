<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU Public License
 */

if ( !isset($this) ) die('Direct access to this file is not allowed');

/**
 * File
 * @abstract
 */
class File_Controller extends Controller
{
	public
		$pageTitle    = 'File',
		$dependencies = array('db', 'input'),
		$standAlone   = TRUE
		;

	function init()
	{
		if ( !$this->app->input->args )
		{
			$this->app->end();
		}

		$thumb = isset($this->app->input->args[0]) && isset($this->app->input->args[1]) && $this->app->input->args[0] == 'thumb';
		$id    = $thumb ? $this->app->input->args[1] : $this->app->input->args[0];

		$id = basename($id, strstr($id, '.'));

		$this->app->db->sql('
			SELECT
				`title`,
				`extension`,
				`filename`,
				`mime_type`
			FROM `' . $this->app->db->prefix . 'uploads`
			WHERE
				id = ' . ( int ) $id . '
			LIMIT 1
			;');

		if ( $this->app->db->result && $r = $this->app->db->result[0] )
		{
			if ( is_file($file = 'uploads/' . ( $thumb ? 'thumbs/' : 'files/' ) . $r['filename']) )
			{
				if ( substr($r['mime_type'], 0, 5) == 'image' )
				{
					header('Content-type: ' . $r['mime_type'] . '; authoritative=true');
				}
				else
				{
					header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
					header('Content-type: ' . $r['mime_type'] . '; authoritative=true');
					header('Content-Disposition: attachment; filename="' . rawurlencode($r['title'] . $r['extension']) . '"');
				}

				readfile($file);

				$this->app->end();
			}
		}

		header('HTTP/1.0 404 Not Found');

		echo $this->view->t('File not found.');
	}
}
