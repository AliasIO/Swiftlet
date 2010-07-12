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

require($contrSetup['rootPath'] . 'init.php');

if ( !isset($app->routeParts[1]) )
{
	$app->end();
}

$thumb = isset($app->routeParts[1]) && isset($app->routeParts[2]) && $app->routeParts[1] == 'thumb';
$id    = $thumb && isset($app->routeParts[2]) ? $app->routeParts[2] : $app->routeParts[1];

$id = basename($id, strstr($id, '.'));

$app->db->sql('
	SELECT
		`title`,
		`extension`,
		`filename`,
		`mime_type`
	FROM `' . $app->db->prefix . 'files`
	WHERE
		id = ' . ( int ) $id . '
	LIMIT 1
	;');

if ( $app->db->result && $r = $app->db->result[0] )
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

$app->end();
