<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

$contrSetup = array(
	'rootPath'  => '../../',
	'pageTitle' => 'Files',
	'inAdmin'   => TRUE
	);

require($contrSetup['rootPath'] . '_model/init.php');

$model->check_dependencies(array('db', 'form', 'node', 'perm'));

$model->form->validate(array(
	'form-submit' => 'bool',
	'title'       => 'string, empty'
	));

$id       = isset($model->GET_raw['id']) && ( int ) $model->GET_raw['id'] ? ( int ) $model->GET_raw['id'] : FALSE;
$action   = isset($model->GET_raw['action'])   ? $model->GET_raw['action']   : FALSE;
$callback = isset($model->GET_raw['callback']) ? $model->GET_raw['callback'] : FALSE;

if ( !$model->perm->check('admin file access') )
{
	header('Location: ' . $contr->rootPath . 'login?ref=' . rawurlencode($_SERVER['PHP_SELF']));

	$model->end();
}

// Get files root node
$model->db->sql('
	SELECT
		`id`
	FROM `' . $model->db->prefix . 'nodes`
	WHERE
		`permalink` = "files"
	LIMIT 1
	;');

if ( $r = $model->db->result )
{
	$filesNodeId = $r[0]['id'];
}

if ( $model->POST_valid['form-submit'] )
{
	if ( isset($_FILES['file']) )
	{
		$uploads = array();

		if ( !is_writable($contr->rootPath . 'uploads/files/') )
		{
			$model->error(FALSE, 'Directory "/uploads/files/" is not writable.', __FILE__, __LINE__);
		}

		for ( $i = 0; $i < count($_FILES['file']['name']); $i ++ )
		{
			switch ( $_FILES['file']['error'][$i] )
			{
				case UPLOAD_ERR_OK:
					$hash = sha1(file_get_contents($_FILES['file']['tmp_name'][$i]));
					
					if ( is_file($contr->rootPath . 'uploads/files/' . $hash) )
					{
						$model->form->errors['file'][$i] = $model->t('This file has already been uploaded.');
					}
					else
					{
						$r = move_uploaded_file($_FILES['file']['tmp_name'][$i], $file = $contr->rootPath . 'uploads/files/' . $hash);

						if ( $r )
						{
							$width  = '';
							$height = '';

							if ( $image = getimagesize($file) )
							{
								list($width, $height) = $image;

								$model->file->thumb($hash, $_FILES['file']['type'][$i], $width, $height);
							}

							$extension = strtolower(strrchr($_FILES['file']['name'][$i], '.'));

							$title = $model->POST_valid['title'][$i] ? $model->POST_raw['title'][$i] : basename($_FILES['file']['name'][$i], $extension);

							$permalink = $model->node->permalink($title);

							$nodeId = $model->node->create($title, $permalink, $filesNodeId);

							if ( $nodeId )
							{
								$model->db->sql('
									INSERT INTO `' . $model->db->prefix . 'files` (
										`node_id`,
										`title`,
										`extension`,
										`image`,
										`file_hash`,
										`mime_type`,
										`width`,
										`height`,
										`size`,
										`date`,
										`date_edit`
										)
									VALUES (
										' . ( int ) $nodeId . ',
										"' . $model->db->escape($title) . '",
										"' . $model->db->escape($extension) . '",
										' . ( !empty($image) ? 1 : 0 ) . ',
										"' . $model->db->escape($hash) . '",
										"' . $model->db->escape($_FILES['file']['type'][$i]) . '",
										' . ( int ) $width . ',
										' . ( int ) $height . ',
										' . ( int ) $model->db->escape($_FILES['file']['size'][$i]) . ',
										"' . gmdate('Y-m-d H:i:s') . '",
										"' . gmdate('Y-m-d H:i:s') . '"
										)
									;');

								if ( $model->db->result )
								{
									$uploads[] = '<a href="' . $model->route('file/' . $permalink . $extension) . '" onclick="callback(\'' . $model->route('file/' . $permalink . $extension) . '\');">' . $model->h($title) . '</a>';
								}
							}
						}
						else
						{
							$model->form->errors['file'][$i] = $model->t('Could not move file to destined location.');
						}
					}

					break;
				case UPLOAD_ERR_INI_SIZE:
				case UPLOAD_ERR_FORM_SIZE:
					$model->form->errors['file'][$i] = $model->t('The file is to big.');

					break;
				case UPLOAD_ERR_PARTIAL:
					$model->form->errors['file'][$i] = $model->t('Upload failed, try again.');

					break;
				case UPLOAD_ERR_NO_TMP_DIR:
					$model->form->errors['file'][$i] = $model->t('Upload failed, missing a temporary folder.');

					break;
				case UPLOAD_ERR_CANT_WRITE:
					$model->form->errors['file'][$i] = $model->t('Upload failed, could not write to disk.');

					break;
				case UPLOAD_ERR_EXTENSION:
					$model->form->errors['file'][$i] = $model->t('File upload stopped by extension.');

					break;
			}
		}

		if ( $uploads )
		{
			$view->notice = $model->t('The following files have been uploaded:<br/><br/>%1$s', implode('<br/>', $uploads));
		}
	}
}
else if ( isset($model->GET_raw['notice']) )
{
	switch ( $model->GET_raw['notice'] )
	{
		case 'deleted':
			$view->notice = $model->t('The file has been deleted.');

			break;
	}
}

if ( ( int ) $id )
{
	switch ( $action )
	{
		case 'delete':
			if ( !$model->POST_valid['confirm'] )
			{
				$model->confirm($model->t('Are you sure you wish to delete this file?'));
			}
			else
			{
				// Delete file
				if ( $model->node->delete(( int ) $id) )
				{
					$model->db->sql('
						SELECT
							`file_hash`
						FROM `' . $model->db->prefix . 'files`
						WHERE
							`node_id` = ' . ( int ) $id . '
						LIMIT 1
						;');
					
					if ( $r = $model->db->result )
					{
						$hash = $r[0]['file_hash'];

						if ( is_file($file = $contr->rootPath . 'uploads/files/' . $hash) )
						{
							unlink($file);
						}

						if ( is_file($file = $contr->rootPath . 'uploads/files/thumbs/' . $hash) )
						{
							unlink($file);
						}

						$model->db->sql('
							DELETE
							FROM `' . $model->db->prefix . 'files`
							WHERE
								`node_id` = ' . ( int ) $id . '
							LIMIT 1
							;');

						if ( $model->db->result )
						{
							header('Location: ?callback=' . rawurlencode($callback) . '&notice=deleted');

							$model->end();
						}
					}
				}
			}

			break;
	}
}

// Create a list of all files
$files = array();

$nodes = $filesNodeId ? $model->node->get_children($filesNodeId) : array();

if ( $nodes )
{
	$nodeIds = array();
	
	if ( !empty($nodes['children']) )
	{
		foreach ( $nodes['children'] as $d )
		{
			$nodeIds[] = $d['id'];
		}
	}
	
	if ( $nodeIds )
	{
		$model->db->sql('
			SELECT
				n.`permalink`,
				f.*
			FROM      `' . $model->db->prefix . 'nodes` AS n
			LEFT JOIN `' . $model->db->prefix . 'files` AS f ON n.`id` = f.`node_id`
			WHERE
				f.`id` AND
				n.`id` IN ( ' . implode(', ', $nodeIds) . ' )
			ORDER BY f.`date` DESC
			LIMIT ' . count($nodeIds) . '
			;');
		
		if ( $r = $model->db->result )
		{
			foreach ( $r as $d )
			{
				$files[] = $d;
			}
		}
	}
}

$pagination = $model->paginate('files', count($files), 10);

$view->files           = array_splice($files, $pagination['from'], 10);
$view->filesPagination = $pagination;
$view->callback        = $model->h($callback);
$view->id              = $id;
$view->action          = $action;

$view->load('admin/files.html.php');

$model->end();
