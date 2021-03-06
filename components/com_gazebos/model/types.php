<?php
/**
 * @version     1.0.0
 * @package     com_gazebos
 * @copyright   Copyright (C) 2012. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Don Gilbert <don@electriceasel.com> - http://www.electriceasel.com
 */
defined('_JEXEC') or die;

class GazebosModelTypes extends EEModelList
{
	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since 1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		// List state information
		$limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'));
		$this->setState('list.limit', $limit);

		$limitstart = JFactory::getApplication()->input->getInt('limitstart', 0);
		$this->setState('list.start', $limitstart);

		if (empty($ordering))
		{
			$ordering = 'a.ordering';
		}

		// List state information.
		parent::populateState($ordering, $direction);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return JDatabaseQuery
	 * @since 1.6
	 */
	protected function buildListQuery()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$q  = $db->getQuery(true);

		// Select the required fields from the table.
		$q
			->select('a.*')
			->from('`#__gazebos_types` AS a')
			->select('uc.name AS editor')
			->leftJoin('#__users AS uc ON uc.id=a.checked_out')
			->select('created_by.name AS created_by')
			->leftJoin('#__users AS created_by ON created_by.id = a.created_by');

		// Filter by published state
		$published = $this->getState('filter.state');

		if (is_numeric($published))
		{
			$q->where('a.state = '.(int) $published);
		}
		else
		{
			$q->where('(a.state = 1)');
		}

		// Filter by search in title
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$q->where('a.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->quote('%' . $db->escape($search, true) . '%');
				$q->where('( a.title LIKE ' . $search . ' )');
			}
		}

		return $q;
	}

}
