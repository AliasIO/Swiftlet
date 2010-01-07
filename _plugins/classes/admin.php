<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

if ( !isset($model) ) die('Direct access to this file is not allowed');

/**
 * Administration
 * @abstract
 */
class admin
{
	public
		$pages = array(),
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
		
		$pages = array();

		$model->hook('admin', $pages);

		usort($pages, array($this, 'page_sort'));

		foreach ( $pages as $page )
		{
			if ( $page['auth'] <= $model->session->get('user auth') )
			{
				if ( !isset($this->pages[$page['group']]) )
				{
					$this->pages[$page['group']] = array();
				}
				
				$this->pages[$page['group']][] = $page;
			}
		}

		$this->ready = TRUE;
	}

	/**
	 * Sort pages by order
	 * @param array $a
	 * @param array $b
	 * @return int
	 */
	private function page_sort($a, $b)
	{
		return ( $a['order'] == $b['order'] ) ? 0 : $a['order'] > $b['order'] ? - 1 : 1;
	}
}