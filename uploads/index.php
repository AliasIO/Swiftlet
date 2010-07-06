<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

$contrSetup = array(
	'rootPath'   => '../',
	'pageTitle'  => 'File',
	'standAlone' => TRUE
	);

require($contrSetup['rootPath'] . '_model/init.php');

if ( !isset($model->routeParts[1]) )
{
	$model->end();
}

$thumb = isset($model->routeParts[1]) && isset($model->routeParts[2]) && $model->routeParts[1] == 'thumb';
$id    = $thumb && isset($model->routeParts[2]) ? $model->routeParts[2] : $model->routeParts[1];

$id = basename($id, strstr($id, '.'));

$model->db->sql('
	SELECT
		`title`,
		`extension`,
		`filename`,
		`mime_type`
	FROM `' . $model->db->prefix . 'files`
	WHERE
		id = ' . ( int ) $id . '
	LIMIT 1
	;');

if ( $model->db->result && $r = $model->db->result[0] )
{
	if ( is_file($file = $contr->rootPath . 'uploads/' . ( $thumb ? 'thumbs/' : 'files/' ) . $r['filename']) )
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

$model->end();
