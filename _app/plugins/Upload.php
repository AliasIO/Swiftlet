<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

if ( !isset($this) ) die('Direct access to this file is not allowed');

/**
 * Upload
 * @abstract
 */
class Upload_Plugin extends Plugin
{
	public
		$version      = '1.0.0',
		$compatible   = array('from' => '1.3.0', 'to' => '1.3.*'),
		$dependencies = array('db', 'permission'),
		$hooks        = array('dashboard' => 2, 'init' => 5, 'install' => 1, 'remove' => 1, 'unit_tests' => 1)
		;

	/*
	 * Implement install hook
	 */
	function install()
	{
		if ( !in_array($this->app->db->prefix . 'uploads', $this->app->db->tables) )
		{
			$this->app->db->sql('
				CREATE TABLE `' . $this->app->db->prefix . 'uploads` (
					`id`        INT(10)      UNSIGNED NOT NULL AUTO_INCREMENT,
					`title`     VARCHAR(255)          NOT NULL,
					`extension` VARCHAR(255)              NULL,
					`image`     TINYINT(1)   UNSIGNED NOT NULL DEFAULT 0,
					`filename`  VARCHAR(40)           NOT NULL,
					`mime_type` VARCHAR(255)          NOT NULL,
					`width`     INT(10)      UNSIGNED     NULL,
					`height`    INT(10)      UNSIGNED     NULL,
					`size`      INT(10)      UNSIGNED     NULL,
					`date`      DATETIME              NOT NULL,
					`date_edit` DATETIME              NOT NULL,
					INDEX `image` (`image`),
					PRIMARY KEY (`id`)
					) TYPE = INNODB
				;');
		}

		if ( !empty($this->app->permission->ready) )
		{
			$this->app->permission->create('Uploads', 'admin upload access', 'Manage uploads');
			$this->app->permission->create('Uploads', 'admin upload upload', 'Upload uploads');
			$this->app->permission->create('Uploads', 'admin upload delete', 'Delete uploads');
		}
	}

	/*
	 * Implement remove hook
	 */
	function remove()
	{
		if ( in_array($this->app->db->prefix . 'uploads', $this->app->db->tables) )
		{
			$this->app->db->sql('
				DROP TABLE `' . $this->app->db->prefix . 'uploads`
				;');
		}

		if ( !empty($this->app->permission->ready) )
		{
			$this->app->permission->delete('admin upload access');
		}
	}

	/*
	 * Implement init hook
	 */
	function init()
	{
		if ( !empty($this->app->db->ready) )
		{
			/**
			 * Check if the uploads table exists
			 */
			if ( in_array($this->app->db->prefix . 'uploads', $this->app->db->tables) )
			{
				$this->ready = TRUE;
			}
		}
	}

	/*
	 * Implement dashboard hook
	 * @params array $params
	 */
	function dashboard(&$params)
	{
		$params[] = array(
			'name'        => 'Uploads',
			'description' => 'Upload and manage files',
			'group'       => 'Content',
			'path'        => 'admin/upload',
			'permission'  => 'admin upload access',
			);
	}

	/**
	 * Create a thumbnail
	 * @param string $filename
	 * @param string $mimeType
	 * @param string $width
	 * @param string $height
	 * @return string
	 */
	function thumb($filename, $mimeType, $width, $height)
	{
		if ( is_file($file = 'uploads/files/' . $filename) )
		{
			$size = 120;

			$image = FALSE;

			switch ( $mimeType )
			{
				case 'image/png':
				case 'image/x-png':
					$image = imagecreatefrompng($file);

					break;
				case 'image/jpeg':
				case 'image/pjpeg':
					$image = imagecreatefromjpeg($file);

					break;
				case 'image/gif':
					$image = imagecreatefromgif($file);

					break;
			}

			if ( $image )
			{
				$thumb = imagecreatetruecolor($size, $size);

				$bgColor = imagecolorallocate($thumb, 253, 254, 255);

				imagefill($thumb, 0, 0, $bgColor);

				imagecolortransparent($thumb, $bgColor);

				$widthRatio  = 1;
				$heightRatio = 1;

				if ( $width <= $size && $height <= $size )
				{
					$posX = ( $size - $width  ) / 2;
					$posY = ( $size - $height ) / 2;

					$sizeX = $width;
					$sizeY = $height;
				}
				else
				{
					$height > $width  ? $widthRatio  = $height / $width  : NULL;
					$width  > $height ? $heightRatio = $width  / $height : NULL;

					$posX = ceil(( $size - ( $size * ( $width  / ( $width  * $widthRatio  ) ) ) ) / 2);
					$posY = ceil(( $size - ( $size * ( $height / ( $height * $heightRatio ) ) ) ) / 2);

					$sizeX = $size;
					$sizeY = $size;
				}

				imagecopyresized(
					$thumb,
					$image,
					$posX,
					$posY,
					0,
					0,
					$sizeX,
					$sizeY,
					$width  * $widthRatio,
					$height * $heightRatio
					);

				if ( $widthRatio > 1 || $heightRatio > 1 )
				{
					imagefilledrectangle(
						$thumb,
						( $widthRatio  > 1 ? $size - $posX : 0 ),
						( $heightRatio > 1 ? $size - $posY : 0 ),
						$size,
						$size,
						$bgColor
						);
				}

				imagepng($thumb, 'uploads/thumbs/' . $filename);
			}
		}
	}

	/*
	 * Implement unit_tests hook
	 * @params array $params
	 */
	function unit_tests(&$params)
	{
		/**
		 * Uploading a file
		 */
		$post = array(
			'title[0]'    => 'Unit Test File',
			'file[0]'     => '@favicon.ico',
			'form-submit' => 'Submit',
			'auth-token'  => $this->app->input->authToken
			);

		$r = $this->app->test->post_request('http://' . $_SERVER['SERVER_NAME'] . $this->app->view->absPath . 'admin/upload', $post);

		$this->app->db->sql('
			SELECT
				*
			FROM `' . $this->app->db->prefix . 'uploads`
			WHERE
				`title` = "Unit Test File"
			LIMIT 1
			;', FALSE);

		$file = isset($this->app->db->result[0]) ? $this->app->db->result[0] : FALSE;

		$params[] = array(
			'test' => 'Uploading a file in <code>/admin/upload</code>.',
			'pass' => ( bool ) $file['id']
			);

		/**
		 * Deleting a file
		 */
		if ( $file['id'] )
		{
			$post = array(
				'confirm'    => '1',
				'auth-token' => $this->app->input->authToken
				);

			$r = $this->app->test->post_request('http://' . $_SERVER['SERVER_NAME'] . $this->app->view->absPath . 'admin/upload/delete/' . ( int ) $file['id'], $post);
		}

		$this->app->db->sql('
			SELECT
				`id`
			FROM `' . $this->app->db->prefix . 'uploads`
			WHERE
				`id` = ' . ( int ) $file['id'] . '
			LIMIT 1
			;', FALSE);

		$params[] = array(
			'test' => 'Deleting a file in <code>/admin/upload</code>.',
			'pass' => !$this->app->db->result
			);
	}
}
