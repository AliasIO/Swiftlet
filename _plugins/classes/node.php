<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

if ( !isset($model) ) die('Direct access to this file is not allowed');

/**
 * Nodes
 * @abstract
 */
class node
{
	public
		$ready
		;

	const
		rootId = 1
		;

	private
		$model,
		$contr
		;

	/**
	 * Initialize nodes
	 * @param object $model
	 */
	function __construct($model)
	{
		$this->model = $model;

		if ( !empty($model->db->ready) )
		{
			/**
			 * Check if the nodes table exists
			 */
			if ( in_array($model->db->prefix . 'nodes', $model->db->tables) )
			{
				$this->ready = TRUE;
			}
		}
	}

	/**
	 * Create a URL friendly title
	 * @param string $title
	 * @return string
	 */
	function permalink($title, $id = FALSE)
	{
		$model = $this->model;

		$permalink = trim(preg_replace('/__+/', '_', preg_replace('/[^a-z0-9_-]/', '_', strtolower($title))), '_');

		$i = 0;

		while ( TRUE )
		{
			$model->db->sql('
				SELECT
					`permalink`
				FROM `' . $model->db->prefix . 'nodes`
				WHERE
					`permalink` = "' . $permalink . ( $i ? '_' . $i : '' ) . '"' . ( $id ? ' AND `id` != ' . $id : '' ) . '
				LIMIT 1
				;');

			if ( !$model->db->result ) return $permalink . ( $i ? '_' . $i : '' );

			$i ++;
		}
	}

	/**
	 * Create a node
	 * @param string $title
	 * @param int $parentId
	 * @param bool $move
	 * @return int
	 */
	function create($title, $permalink, $parentId)
	{
		$model = $this->model;
		
		$model->db->sql('
			SELECT
				`left_id`,
				`right_id`
			FROM `' . $model->db->prefix . 'nodes`
			WHERE
				`id` = ' . ( int ) $parentId . '
			LIMIT 1
			;');
		
		if ( $model->db->result )
		{
			$parentNode = $model->db->result[0];
			
			$model->db->sql('
				UPDATE `' . $model->db->prefix . 'nodes` SET
					`left_id`   = `left_id` + 2,
					`date_edit` = NOW()
				WHERE
					`left_id` > ' . ( int ) $parentNode['left_id'] . '
				;');

			$model->db->sql('
				UPDATE `' . $model->db->prefix . 'nodes` SET
					`right_id`  = `right_id` + 2,
					`date_edit` = NOW()
				WHERE
					`right_id` > ' . ( int ) $parentNode['left_id'] . '
				;');

			$model->db->sql('
				INSERT INTO `' . $model->db->prefix . 'nodes` (
					`left_id`,
					`right_id`,
					`title`,
					`permalink`,
					`date`,
					`date_edit`
					)
				VALUES (
					' . ( ( int ) $parentNode['left_id'] + 1 ) . ',
					' . ( ( int ) $parentNode['left_id'] + 2 ) . ',
					"' . $title . '",
					"' . $permalink . '",
					NOW(),
					NOW()
					)
				;');

			return $model->db->result;
		}

		return FALSE;
	}

	/**
	 * Move a branch
	 * @param int $id
	 * @param int $parentId
	 * @return bool
	 */
	function move($id, $parentId)
	{
		$model = $this->model;

		// Root node can not be moved
		if ( $id == node::rootId )
		{
			return;
		}

		$node       = $this->get_children($id);
		$parentNode = $this->get($parentId);

		// Node can not be moved to a decendant of its own
		if ( $node['left_id'] <= $parentNode['left_id'] && $node['right_id'] >= $parentNode['right_id'] )
		{
			return;
		}

		$diff = count($node['all']) * 2;

		// Sync parents
		$model->db->sql('
			UPDATE `' . $model->db->prefix . 'nodes` SET
				`right_id` = `right_id` - ' . $diff . '
			WHERE
				`left_id`  < ' . ( int ) $node['right_id'] . ' AND
				`right_id` > ' . ( int ) $node['right_id'] . '
			;');

		// Sync righthand side of tree
		$model->db->sql('
			UPDATE `' . $model->db->prefix . 'nodes` SET
				`left_id`  = `left_id`  - ' . $diff . ',
				`right_id` = `right_id` - ' . $diff . '
			WHERE
				`left_id` > ' . $node['right_id'] . '
			;');

		$parentNode = $this->get($parentId);

		// Sync new parents
		$model->db->sql('
			UPDATE `' . $model->db->prefix . 'nodes` SET
				`right_id` = `right_id` + ' . $diff . '
			WHERE
				' . $parentNode['right_id'] . ' BETWEEN `left_id` AND `right_id` AND
				`id` NOT IN ( ' . implode(', ', $node['all']) . ' )
			;');

		// Sync righthand side of tree
		$model->db->sql('
			UPDATE `' . $model->db->prefix . 'nodes` SET
				`left_id`  = `left_id`  + ' . $diff . ',
				`right_id` = `right_id` + ' . $diff . '
			WHERE
				`left_id` > ' . $parentNode['right_id'] . ' AND
				`id` NOT IN ( ' . implode(', ', $node['all']) . ' )
			;');

		// Sync moved branch
		$parentNode['right_id'] += $diff;
		
		if ( $parentNode['right_id'] > $node['right_id'] )
		{
			$diff = '+ ' . ( $parentNode['right_id'] - $node['right_id'] - 1 );
		}
		else
		{
			$diff = '- ' . abs($parentNode['right_id'] - $node['right_id'] - 1);
		}

		$model->db->sql('
			UPDATE `' . $model->db->prefix . 'nodes` SET
				`left_id`  = `left_id`  ' . $diff . ',
				`right_id` = `right_id` ' . $diff . '
			WHERE
				`id` IN ( ' . implode(', ', $node['all']) . ' )
			;');
	}

	/**
	 * Delete a node
	 * @param int $parentId
	 * @param bool $soft
	 * @return int
	 */
	function delete($id)
	{
		$model = $this->model;

		$model->db->sql('
			SELECT
				`left_id`,
				`right_id`
			FROM `' . $model->db->prefix . 'nodes`
			WHERE
				`id` = ' . ( int ) $id . '
			LIMIT 1
			;');

		if ( $model->db->result )
		{
			$leftId  = $model->db->result[0]['left_id'];
			$rightId = $model->db->result[0]['right_id'];

			$model->db->sql('
				UPDATE `' . $model->db->prefix . 'nodes` SET
					`right_id`  = `right_id` - 2,
					`date_edit` = NOW()
				WHERE
					`right_id` > ' . $rightId . '
				;');

			$model->db->sql('
				UPDATE `' . $model->db->prefix . 'nodes` SET
					`left_id`   = `left_id`  - 1,
					`right_id`  = `right_id` - 1,
					`date_edit` = NOW()
				WHERE
					`left_id`  > ' . $leftId  . ' AND
					`right_id` < ' . $rightId . '
				;');

			$model->db->sql('
				UPDATE `' . $model->db->prefix . 'nodes` SET
					`left_id`  = `left_id` - 2,
					`date_edit` = NOW()
				WHERE
					`left_id`  > ' . $leftId  . ' AND
					`right_id` > ' . $rightId . '
				;');

			$model->db->sql('
				DELETE
				FROM `' . $model->db->prefix . 'nodes`
				WHERE
					`id` = ' . ( int ) $id . '
				LIMIT 1
				;');

			return $model->db->result;
		}
	}

	/**
	 * Get a node
	 * @param int $id
	 * @return array
	 */
	function get($id)
	{
		$model = $this->model;

		$node = array();	

		$model->db->sql('
			SELECT
				`left_id`,
				`right_id`,
				`title`,
				`permalink`
			FROM `' . $model->db->prefix . 'nodes`
			WHERE
				`id` = ' . ( int ) $id . '
			LIMIT 1
			;');

		if ( $model->db->result )
		{
			$node = array(
				'id'        => $id,
				'left_id'   => $model->db->result[0]['left_id'],
				'right_id'  => $model->db->result[0]['right_id'],
				'title'     => $model->db->result[0]['title'],
				'permalink' => $model->db->result[0]['permalink']
				);
		}

		return $node;
	}

	/**
	 * Get a node and its parents
	 * @param int $id
	 * @return array
	 */
	function get_parents($id)
	{
		$model = $this->model;

		$node = array();	

		if ( $node = $this->get($id) )
		{
			$node['parents'] = array();
			
			$model->db->sql('
				SELECT
					`id`,
					`left_id`,
					`right_id`,
					`title`,
					`permalink`
				FROM `' . $model->db->prefix . 'nodes`
				WHERE
					`left_id`  < ' . ( int ) $node['left_id']  . ' AND
					`right_id` > ' . ( int ) $node['right_id'] . '
				ORDER BY `left_id` ASC
				;');

			if ( $model->db->result )
			{
				foreach ( $model->db->result as $d ) $node['parents'][] = array(
					'id'        => $d['id'],
					'left_id'   => $d['left_id'],
					'right_id'  => $d['right_id'],
					'title'     => $d['title'],
					'permalink' => $d['permalink']
					);
			}
		}

		return $node;
	}
	
	/**
	 * Get a node and its children
	 * @param int $id
	 * @return array
	 */
	function get_children($id)
	{
		$model = $this->model;
		
		if ( $node = $this->get($id) )
		{
			$node['children'] = array();

			$model->db->sql('
				SELECT
					`id`,
					`left_id`,
					`right_id`,
					`title`,
					`permalink`
				FROM `' . $model->db->prefix . 'nodes`
				WHERE
					`left_id` BETWEEN ' . ( int ) $node['left_id']  . ' AND ' . ( int ) $node['right_id'] . '
				ORDER BY `left_id` ASC
				;');

			if ( $model->db->result )
			{
				$nodes    = array();
				$children = array();

				foreach ( $model->db->result as $d )
				{
					$children[] = $d['id'];
				}

				foreach ( $model->db->result as $d )
				{
					$nodes[] = array(
						'id'        => $d['id'],
						'left_id'   => $d['left_id'],
						'right_id'  => $d['right_id'],
						'title'     => $d['title'],
						'permalink' => $d['permalink'],
						'level'     => 0,
						'children'  => array()
						);
				}
				
				$nodes = $this->tree($nodes);

				$nodes['all'] = $children;

				return $nodes;
			}
		}
	}

	/**
	 * Create a structured array of nodes
	 * @param array $nodes
	 * @return array
	 */
	function tree($nodes)
	{
		$stack = array();

		for ( $i = 0; $i < count($nodes); $i ++ )
		{
			$node = &$nodes[$i];
			
			while ( count($stack) > 0 && $stack[count($stack) - 1]['right_id'] < $node['right_id'] )
			{
				array_pop($stack);
			}

			if ( count($stack) > 0 )
			{
				$node['level'] = count($stack);
				
				$stack[count($stack) - 1]['children'][] = &$node;
			}

			$stack[] = &$node;
		}

		return $stack[0];
	}

	/**
	 * Turn an array of nodes into a flat list
	 * @param array $nodes
	 * @param array $list
	 */
	function nodes_to_array($nodes, &$list)
	{
		if ( $nodes )
		{
			foreach ( $nodes as $node )
			{
				$list[] = array(
					'id'        => $node['id'],
					'left_id'   => $node['left_id'],
					'right_id'  => $node['right_id'],
					'title'     => $node['title'],
					'permalink' => $node['permalink'],
					'level'     => $node['level']
					);

				if ( !empty($node['children']) )
				{
					$this->nodes_to_array($node['children'], $list);
				}
			}
		}
	}
}