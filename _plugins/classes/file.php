<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

if ( !isset($model) ) die('Direct access to this file is not allowed');

/**
 * File
 * @abstract
 */
class file
{
	public
		$ready
		;

	private
		$model,
		$contr
		;

	/**
	 * Initialize
	 * @param object $model
	 */
	function __construct($model)
	{
		$this->model = $model;
		$this->contr = $model->contr;

		if ( !empty($model->db->ready) )
		{
			/**
			 * Check if the pages table exists
			 */
			if ( in_array($model->db->prefix . 'files', $model->db->tables) )
			{
				$this->ready = TRUE;
			}
		}
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
		$contr = $this->contr;

		if ( is_file($file = $contr->rootPath . 'uploads/files/' . $filename) )
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

				imagepng($thumb, $contr->rootPath . 'uploads/files/thumbs/' . $filename);
			}
		}
	}
}
