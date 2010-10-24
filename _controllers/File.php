<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU Public License
 */

/**
 * File
 * @abstract
 */
class File_Controller extends Controller
{
	public
		$pageTitle  = 'File',
		$standAlone = TRUE
		;

	function init()
	{
		if ( !$this->args )
		{
			$this->app->end();
		}

		$thumb = isset($this->args[0]) && isset($this->args[1]) && $this->args[0] == 'thumb';
		$id    = $thumb ? $this->args[1] : $this->args[0];

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
			}
		}
	}
}
