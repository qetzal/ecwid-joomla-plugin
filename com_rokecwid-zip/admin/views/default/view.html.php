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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');
jimport('joomla.application.component.helper');

/**
 * HTML View class for the Rokdownloads component
 *
 * @static
 * @package        Joomla
 * @subpackage    RokDownloads
 * @since 1.0
 */

class RokEcwidViewDefault extends JView
{
    /**
     * Display the view
     */
    public function display($tpl = null)
    {

        $option = JRequest::getWord('option', 'com_rokecwid');
        $document =& JFactory::getDocument();
        $document->addStyleSheet('components/' . $option . '/assets/rokecwid.css');
        $document->addScript('components/' . $option . '/assets/rokecwid.js');

        // Initialiase variables.
        $this->form = $this->get('form');
        $this->params = $this->get('params');

        $document = & JFactory::getDocument();
        // Set toolbar items for the page
        $this->addToolbar();

        $uri =& JFactory::getURI();

        $document = & JFactory::getDocument();
        $document->setTitle(JText::_('Ecwid Edit Configuration'));
        parent::display($tpl);
    }

    /**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{


		JToolBarHelper::title(JText::_('COM_ROKECWID_CONFIGURATION'));
		JToolBarHelper::save('default.save','JTOOLBAR_SAVE');
		JToolBarHelper::cancel('default.cancel','COM_ROKECWID_RESET');

		JToolBarHelper::divider();
	}

    protected function render()
    {
        if (!isset($this->form))
        {
            return false;
        }

        $html = array();
        $html[] = '<div id="rokecwid-paramslist">';

        //		if ($description = $self->_xml[$group]->attributes('description')) {
        //			// add the params description to the display
        //			$desc	= JText::_($description);
        //			$html[]	= '<div id="rokecwid-params-description>'.$desc.'</div>';
        //		}

        $fields = $this->form->getFieldset('params');

        $i = 0;
        foreach ($fields as $field)
        {
            $class = '';

            if (!$i)
            {
                $html[] = '<div class="left-column column">';
                $class = ' first';
            }
            if ($i == round(count($fields) / 2) - 1)
            {
                $class = ' last';
            }
            if ($i == round(count($fields) / 2))
            {
                $html[] = '</div><div class="right-column column">';
                $class = ' first';
            }
            if ($i >= count($fields) - 1)
            {
                $class = ' last';
            }

            $html[] = '<div class="rokecwid-row' . $class . '">';

            if ($field->label != '')
            {
                $html[] = '<div class="label"><span>' . $field->label . '</span></div>';
                $html[] = '<div class="value">' . $field->input . '</div>';
            } else
            {
                $html[] = '<div class="value">' . $field->input . '</div>';
            }

            $html[] = '</div>';

            if ($i >= count($fields) - 1) $html[] = '</div>';
            $i++;
        }

        if (count($fields) < 1)
        {
            $html[] = "<div class='notice'>" . JText::_('There are no Parameters for this item') . "</div>";
        }

        $html[] = '</div>';

        return implode("\n", $html);
    }

}

?>
