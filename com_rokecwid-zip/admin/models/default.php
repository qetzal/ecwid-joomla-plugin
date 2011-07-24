<?php
/**
 * @version   1.3 July 15, 2011
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Based on work by
 * @author Rick Blalock
 * @package Joomla
 * @subpackage ecwid
 * @license GNU/GPL
 *
 * ECWID.com e-commerce wrapper
 */
defined('_JEXEC') or die();

jimport( 'joomla.application.component.modelform' );

/**
 * @package		Joomla
 * @subpackage	Config
 */
class RokEcwidModelDefault extends JModelForm
{
	
	/**
	 * Get the params for the configuration variables
	 */
	function getParams($component="com_rokecwid")
	{
		static $instance;

        $params = new JRegistry();
        $table =& JTable::getInstance('extension');
        $result = $table->find(array('element'=>$component ));
        $table->load($result);
        $params->loadString($table->params);
		return $params;
	}
	

    /**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_rokecwid.default.default.data', array());

		if (empty($data)) {
			$data = $this->getParams();
		}

		return $data;
	}

    /**
	 * Method to get the record form.
	 *
	 * @param	array	$data		Data for the form.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	mixed	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_rokcwid.default', 'default', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}

		return $form;
	}

    /**
	 * Method to save the form data.
	 *
	 * @param	array	The form data.
	 * @return	boolean	True on success.
	 */
	public function save($data)
	{
		// Initialise variables;
		$dispatcher = JDispatcher::getInstance();


        $params = new JRegistry();
        $params->loadArray($data);


        $table =& JTable::getInstance('extension');
        $result = $table->find(array('element'=>'com_rokecwid' ));
        $table->load($result);

        $table->params = $params->toString();

        $table->store();

		// Clean the cache.
		$cache = JFactory::getCache('_system');
		$cache->clean();

		return true;
	}
	
}
?>
