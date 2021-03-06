<?php
/**
 * @version     1.0.0
 * @package     com_gazebos
 * @copyright   Copyright (C) 2012. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Don Gilbert <don@electriceasel.com> - http://www.electriceasel.com
 */
defined('_JEXEC') or die;

class GazebosModelOptions extends EEModelList
{
    /**
     * Constructor.
     *
     * @param    array    An optional associative array of configuration settings.
     * @see        JController
     * @since    1.6
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                                'id', 'a.id',
                'ordering', 'a.ordering',
                'state', 'a.state',
                'created_by', 'a.created_by',
                'title', 'a.title',
                'option_category_id', 'a.option_category_id',

            );
        }

        parent::__construct($config);
    }


	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $app->getUserStateFromRequest($this->context.'.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);
        
        
		//Filtering option_category_id
		$this->setState('filter.option_category_id', $app->getUserStateFromRequest($this->context.'.filter.option_category_id', 'filter_option_category_id', '', 'string'));
        
		// Load the parameters.
		$params = JComponentHelper::getParams('com_gazebos');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('a.title', 'asc');
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return	JDatabaseQuery
	 * @since	1.6
	 */
	protected function buildListQuery()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$q  = $db->getQuery(true);

		// Select the required fields from the table.
		$q
			->select('a.*')
			->from('`#__gazebos_options` AS a')
			->select('b.title AS option_category_title')
			->leftJoin('#__gazebos_option_categories AS b ON b.id = a.option_category_id');

	    // Filter by published state
	    $published = $this->getState('filter.state');
	    if (is_numeric($published))
	    {
	        $q->where('a.state = '.(int) $published);
	    }
	    elseif ($published === '')
	    {
	        $q->where('(a.state IN (0, 1))');
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
				$search = $db->Quote('%'.$db->escape($search, true).'%');
                $q->where('( a.title LIKE '.$search.' )');
			}
		}

		//Filtering option_category_id
		$filter_option_category_id =  (int) $this->state->get("filter.option_category_id");
		if ($filter_option_category_id)
		{
			$q->where("a.option_category_id = {$filter_option_category_id}");
		}        

		// Add the list ordering clause.
        $orderCol	= $this->state->get('list.ordering');
        $orderDirn	= $this->state->get('list.direction');

        if ($orderCol && $orderDirn)
        {
            $q->order($db->escape($orderCol . ' ' . $orderDirn));
        }

		return $q;
	}
}
