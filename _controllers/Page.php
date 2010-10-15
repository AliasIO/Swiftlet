<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

/**
 * Page
 * @abstract
 */
class Page_Controller extends Controller
{
	public
		$pageTitle    = 'Page',
		$dependencies = array('db', 'node', 'page')
		;

	function init()
	{
		if ( isset($this->app->view->args[0]) )
		{
			$language = !empty($this->app->lang->ready) ? $this->app->lang->language : 'English US';

			$this->app->db->sql('
				SELECT
					p.`id`,
					p.`node_id`,
					p.`title`,
					p.`body`,
					n.`home`
				FROM      `' . $this->app->db->prefix . 'pages` AS p
				LEFT JOIN `' . $this->app->db->prefix . 'nodes` AS n ON p.`node_id` = n.`id`
				WHERE
					n.`id`        =  ' . ( int ) $this->app->view->args[0] . '  AND
					n.`type`      = "page"                                      AND
					p.`published` = 1                                           AND
					p.`lang`      = "' . $this->app->db->escape($language) . '"
				LIMIT 1
				;');

			if ( isset($this->app->db->result[0]) && $d = $this->app->db->result[0] )
			{
				$this->app->view->pageTitle = $d['title'];
				$this->app->view->nodeId    = $d['node_id'];
				$this->app->view->body      = $this->app->view->allow_html($d['body']);
				$this->app->view->home      = $d['home'];

				/*
				 * Prefix relative links with the path to the root
				 * This way internal links won't break when the site
				 * is moved to another directory
				 */
				$this->app->page->parse_urls($this->app->view->body);

				/*
				 * Create a breadcrumb trail
				 */
				$this->app->view->parents = array();

				if ( !$d['home'] )
				{
					$nodes = $this->app->node->get_parents($d['node_id']);

					foreach ( $nodes['parents'] as $d )
					{
						if ( $d['id'] != Node_Plugin::ROOT_ID )
						{
							$this->app->view->parents[$d['path'] ? $d['path'] : 'node/' . $d['id']] = $d['title'];
						}
					}
				}
			}
		}

		if ( !isset($this->app->view->nodeId) )
		{
			header('HTTP/1.0 404 Not Found');

			$this->app->view->pageTitle = $this->app->view->t('Page not found');
			$this->app->view->error     = $this->app->view->t('The page you are looking for does not exist.');
		}

		$this->app->view->load('page.html.php');
	}
}
