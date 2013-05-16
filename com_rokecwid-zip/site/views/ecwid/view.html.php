<?php
/**
 * Joomla! 1.5 component ecwid
 *
 * @version $Id: view.html.php 2010-01-30 07:51:07 svn $
 * @author Rick Blalock
 * @package Joomla
 * @subpackage ecwid
 * @license GNU/GPL
 *
 * ECWID.com e-commerce wrapper
 *
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the ecwid component
 */
class RokEcwidViewEcwid extends JView {
	function display($tpl = null) {

        $this->_prepareDocument();

        parent::display($tpl);
    }

    function _prepareDocument() 
    {
        $app        = JFactory::getApplication();
        $params     = $app->getParams();

        if ($params->get('menu-meta_description'))
        {
            $this->document->setDescription($params->get('menu-meta_description'));
        }   
        
        if ($params->get('menu-meta_keywords'))
        {
            $this->document->setMetadata('keywords', $params->get('menu-meta_keywords'));
        }   
        
        if ($params->get('robots'))
        {
            $this->document->setMetadata('robots', $params->get('robots'));
        }   
    }
}
?>
