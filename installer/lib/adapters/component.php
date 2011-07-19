<?php
/**
 * @package   Installer Bundle Framework - RocketTheme
 * @version   1.9 October 21, 2010
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2010 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Installer uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

if (!class_exists( "JInstallerComponent")){
    jimport('joomla.installer.adapters.component');
}
/**
 * Component installer
 *
 * @package		Joomla.Framework
 * @subpackage	Installer
 * @since		1.5
 */
class RokInstallerComponent extends JInstallerComponent
{

	/**
	 * Custom install method for components
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function install()
	{
		// Get a database connector object
		$db =& $this->parent->getDBO();

		// Get the extension manifest object
		$manifest =& $this->parent->getManifest();
		$this->manifest =& $manifest->document;

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Manifest Document Setup Section
		 * ---------------------------------------------------------------------------------------------
		 */

		// Set the extensions name
		$name =& $this->manifest->getElementByPath('name');
		$name = JFilterInput::clean($name->data(), 'cmd');
		$this->set('name', $name);

		// Get the component description
		$description = & $this->manifest->getElementByPath('description');
		if (is_a($description, 'JSimpleXMLElement')) {
			$this->parent->set('message', $description->data());
		} else {
			$this->parent->set('message', '' );
		}

		// Get some important manifest elements
		$this->adminElement		=& $this->manifest->getElementByPath('administration');
		$this->installElement	=& $this->manifest->getElementByPath('install');
		$this->uninstallElement	=& $this->manifest->getElementByPath('uninstall');

		// Set the installation target paths
		$this->parent->setPath('extension_site', JPath::clean(JPATH_SITE.DS."components".DS.strtolower("com_".str_replace(" ", "", $this->get('name')))));
		$this->parent->setPath('extension_administrator', JPath::clean(JPATH_ADMINISTRATOR.DS."components".DS.strtolower("com_".str_replace(" ", "", $this->get('name')))));

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Basic Checks Section
		 * ---------------------------------------------------------------------------------------------
		 */

		// Make sure that we have an admin element
		if ( ! is_a($this->adminElement, 'JSimpleXMLElement') )
		{
			JError::raiseWarning(1, JText::_('Component').' '.JText::_('Install').': '.JText::_('The XML file did not contain an administration element'));
			return false;
		}

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Filesystem Processing Section
		 * ---------------------------------------------------------------------------------------------
		 */

		/*
		 * If the component site or admin directory already exists, then we will assume that the component is already
		 * installed or another component is using that directory.
		 */
		$exists	= false;
		if ( file_exists($this->parent->getPath('extension_site')) && !$this->parent->getOverwrite()) {
			$exists	= true;
			JError::raiseWarning(1, JText::_('Component').' '.JText::_('Install').': '.JText::_('Another component is already using directory').': "'.$this->parent->getPath('extension_site').'"');
		}
        else if (file_exists($this->parent->getPath('extension_site')) && $this->parent->getOverwrite()){
            $this->parent->upgrade = true;
        }
		if ( file_exists($this->parent->getPath('extension_administrator')) && !$this->parent->getOverwrite()) {
			$exists	= true;
			JError::raiseWarning(1, JText::_('Component').' '.JText::_('Install').': '.JText::_('Another component is already using directory').': "'.$this->parent->getPath('extension_administrator').'"');
		}
        else if (file_exists($this->parent->getPath('extension_administrator')) && $this->parent->getOverwrite()){
            $this->parent->upgrade = true;
        }
		if ( $exists )
		{
			return false;
		}

		// If the component directory does not exist, lets create it
		$created = false;
		if (!file_exists($this->parent->getPath('extension_site'))) {
			if (!$created = JFolder::create($this->parent->getPath('extension_site'))) {
				JError::raiseWarning(1, JText::_('Component').' '.JText::_('Install').': '.JText::_('Failed to create directory').': "'.$this->parent->getPath('extension_site').'"');
				return false;
			}
		}

		/*
		 * Since we created the component directory and will want to remove it if we have to roll back
		 * the installation, lets add it to the installation step stack
		 */
		if ($created) {
			$this->parent->pushStep(array ('type' => 'folder', 'path' => $this->parent->getPath('extension_site')));
		}

		// If the component admin directory does not exist, lets create it
		$created = false;
		if (!file_exists($this->parent->getPath('extension_administrator'))) {
			if (!$created = JFolder::create($this->parent->getPath('extension_administrator'))) {
				JError::raiseWarning(1, JText::_('Component').' '.JText::_('Install').': '.JText::_('Failed to create directory').': "'.$this->parent->getPath('extension_administrator').'"');
				// Install failed, rollback any changes
				$this->parent->abort();
				return false;
			}
		}

		/*
		 * Since we created the component admin directory and we will want to remove it if we have to roll
		 * back the installation, lets add it to the installation step stack
		 */
		if ($created) {
			$this->parent->pushStep(array ('type' => 'folder', 'path' => $this->parent->getPath('extension_administrator')));
		}

		// Find files to copy
		foreach ($this->manifest->children() as $child)
		{
			if (is_a($child, 'JSimpleXMLElement') && $child->name() == 'files') {
				if ($this->parent->parseFiles($child) === false) {
					// Install failed, rollback any changes
					$this->parent->abort();
					return false;
				}
			}
		}

		foreach ($this->adminElement->children() as $child)
		{
			if (is_a($child, 'JSimpleXMLElement') && $child->name() == 'files') {
				if ($this->parent->parseFiles($child, 1) === false) {
					// Install failed, rollback any changes
					$this->parent->abort();
					return false;
				}
			}
		}

		// Parse optional tags
		$this->parent->parseMedia($this->manifest->getElementByPath('media'));
		$this->parent->parseLanguages($this->manifest->getElementByPath('languages'));
		$this->parent->parseLanguages($this->manifest->getElementByPath('administration/languages'), 1);

		// Parse deprecated tags
		$this->parent->parseFiles($this->manifest->getElementByPath('images'));
		$this->parent->parseFiles($this->manifest->getElementByPath('administration/images'), 1);

		// If there is an install file, lets copy it.
		$installScriptElement =& $this->manifest->getElementByPath('installfile');
		if (is_a($installScriptElement, 'JSimpleXMLElement')) {
			// check if it actually has a value
			$installScriptFilename = $installScriptElement->data();
			if(empty($installScriptFilename)) {
				if(JDEBUG) JError::raiseWarning(43, JText::sprintf('BLANKSCRIPTELEMENT', JText::_('install')));
			} else {
				// Make sure it hasn't already been copied (this would be an error in the xml install file)
				// Only copy over an existing file when upgrading components
				if (!file_exists($this->parent->getPath('extension_administrator').DS.$installScriptFilename) || $this->parent->getOverwrite())
				{
					$path['src']	= $this->parent->getPath('source').DS.$installScriptFilename;
					$path['dest']	= $this->parent->getPath('extension_administrator').DS.$installScriptFilename;
					if(file_exists($path['src']) && file_exists(dirname($path['dest']))) {
						if (!$this->parent->copyFiles(array ($path))) {
							// Install failed, rollback changes
							$this->parent->abort(JText::_('Component').' '.JText::_('Install').': '.JText::_('Could not copy PHP install file.'));
							return false;
						}
					} else if(JDEBUG) {
						JError::raiseWarning(42, JText::sprintf('INVALIDINSTALLFILE', JText::_('install')));
					}
				}
				$this->set('install.script', $installScriptFilename);
			}
		}

		// If there is an uninstall file, lets copy it.
		$uninstallScriptElement =& $this->manifest->getElementByPath('uninstallfile');
		if (is_a($uninstallScriptElement, 'JSimpleXMLElement')) {
			// check it actually has a value
			$uninstallScriptFilename = $uninstallScriptElement->data();
			if(empty($uninstallScriptFilename)) {
				// display a warning when we're in debug mode
				if(JDEBUG) JError::raiseWarning(43, JText::sprintf('BLANKSCRIPTELEMENT', JText::_('uninstall')));
			} else {
				// Make sure it hasn't already been copied (this would be an error in the xml install file)
				// Only copy over an existing file when upgrading components
				if (!file_exists($this->parent->getPath('extension_administrator').DS.$uninstallScriptFilename) || $this->parent->getOverwrite())
				{
					$path['src']	= $this->parent->getPath('source').DS.$uninstallScriptFilename;
					$path['dest']	= $this->parent->getPath('extension_administrator').DS.$uninstallScriptFilename;
					if(file_exists($path['src']) && file_exists(dirname($path['dest']))) {
						if (!$this->parent->copyFiles(array ($path))) {
							// Install failed, rollback changes
							$this->parent->abort(JText::_('Component').' '.JText::_('Install').': '.JText::_('Could not copy PHP install file.'));
							return false;
						}
					} else if(JDEBUG) {
						JError::raiseWarning(42, JText::sprintf('INVALIDINSTALLFILE', JText::_('uninstall')));
					}
				}
			}
		}

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Database Processing Section
		 * ---------------------------------------------------------------------------------------------
		 */

		/*
		 * Let's run the install queries for the component
		 *	If backward compatibility is required - run queries in xml file
		 *	If Joomla 1.5 compatible, with discreet sql files - execute appropriate
		 *	file for utf-8 support or non-utf-8 support
		 */
		$result = $this->parent->parseQueries($this->manifest->getElementByPath('install/queries'));
		if ($result === false) {
			// Install failed, rollback changes
			$this->parent->abort(JText::_('Component').' '.JText::_('Install').': '.JText::_('SQL Error')." ".$db->stderr(true));
			return false;
		} elseif ($result === 0) {
			// no backward compatibility queries found - try for Joomla 1.5 type queries
			// second argument is the utf compatible version attribute
			$utfresult = $this->parent->parseSQLFiles($this->manifest->getElementByPath('install/sql'));
			if ($utfresult === false) {
				// Install failed, rollback changes
				$this->parent->abort(JText::_('Component').' '.JText::_('Install').': '.JText::_('SQLERRORORFILE')." ".$db->stderr(true));
				return false;
			}
		}

		// Time to build the admin menus
		$this->_buildAdminMenus();

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Custom Installation Script Section
		 * ---------------------------------------------------------------------------------------------
		 */

		/*
		 * If we have an install script, lets include it, execute the custom
		 * install method, and append the return value from the custom install
		 * method to the installation message.
		 */
		if ($this->get('install.script')) {
			if (is_file($this->parent->getPath('extension_administrator').DS.$this->get('install.script'))) {
				ob_start();
				ob_implicit_flush(false);
				require_once ($this->parent->getPath('extension_administrator').DS.$this->get('install.script'));
				if (function_exists('rok_com_install')) {
					if (rok_com_install() === false) {
						$this->parent->abort(JText::_('Component').' '.JText::_('Install').': '.JText::_('Custom install routine failure'));
						return false;
					}
				}
				$msg = ob_get_contents();
				ob_end_clean();
				if ($msg != '') {
					$this->parent->set('extension.message', $msg);
				}
			}
		}

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Finalization and Cleanup Section
		 * ---------------------------------------------------------------------------------------------
		 */

		// Lastly, we will copy the manifest file to its appropriate place.
		if (!$this->parent->copyManifest()) {
			// Install failed, rollback changes
			$this->parent->abort(JText::_('Component').' '.JText::_('Install').': '.JText::_('Could not copy setup file'));
			return false;
		}

		// Load component lang file
		$lang =& JFactory::getLanguage();
		$lang->load(strtolower("com_".str_replace(" ", "", $this->get('name'))));

		return true;
	}
		/**
	 * Custom uninstall method for components
	 *
	 * @access	public
	 * @param	int		$cid	The id of the component to uninstall
	 * @param	int		$clientId	The id of the client (unused)
	 * @return	mixed	Return value for uninstall method in component uninstall file
	 * @since	1.0
	 */
	function uninstall($id, $clientId)
	{
		// Initialize variables
		$db =& $this->parent->getDBO();
		$row	= null;
		$retval	= true;

		// First order of business will be to load the component object table from the database.
		// This should give us the necessary information to proceed.
		$row = & JTable::getInstance('component');
		if ( !$row->load((int) $id) || !trim($row->option) ) {
			JError::raiseWarning(100, JText::_('ERRORUNKOWNEXTENSION'));
			return false;
		}

		// Is the component we are trying to uninstall a core one?
		// Because that is not a good idea...
		if ($row->iscore) {
			JError::raiseWarning(100, JText::_('Component').' '.JText::_('Uninstall').': '.JText::sprintf('WARNCORECOMPONENT', $row->name)."<br />".JText::_('WARNCORECOMPONENT2'));
			return false;
		}

		// Get the admin and site paths for the component
		$this->parent->setPath('extension_administrator', JPath::clean(JPATH_ADMINISTRATOR.DS.'components'.DS.$row->option));
		$this->parent->setPath('extension_site', JPath::clean(JPATH_SITE.DS.'components'.DS.$row->option));

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Manifest Document Setup Section
		 * ---------------------------------------------------------------------------------------------
		 */

		// Find and load the XML install file for the component
		$this->parent->setPath('source', $this->parent->getPath('extension_administrator'));

		// Get the package manifest objecct
		$manifest =& $this->parent->getManifest();
		if (!is_a($manifest, 'JSimpleXML')) {
			// Make sure we delete the folders if no manifest exists
			JFolder::delete($this->parent->getPath('extension_administrator'));
			JFolder::delete($this->parent->getPath('extension_site'));

			// Remove the menu
			$this->_removeAdminMenus($row);

			// Raise a warning
			JError::raiseWarning(100, JText::_('ERRORREMOVEMANUALLY'));

			// Return
			return false;
		}

		// Get the root node of the manifest document
		$this->manifest =& $manifest->document;

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Custom Uninstallation Script Section
		 * ---------------------------------------------------------------------------------------------
		 */

		// Now lets load the uninstall file if there is one and execute the uninstall function if it exists.
		$uninstallfileElement =& $this->manifest->getElementByPath('uninstallfile');
		if (is_a($uninstallfileElement, 'JSimpleXMLElement')) {
			// Element exists, does the file exist?
			if (is_file($this->parent->getPath('extension_administrator').DS.$uninstallfileElement->data())) {
				ob_start();
				ob_implicit_flush(false);
				require_once ($this->parent->getPath('extension_administrator').DS.$uninstallfileElement->data());
				if (function_exists('rok_com_uninstall')) {
					if (rok_com_uninstall() === false) {
						JError::raiseWarning(100, JText::_('Component').' '.JText::_('Uninstall').': '.JText::_('Custom Uninstall script unsuccessful'));
						$retval = false;
					}
				}
				$msg = ob_get_contents();
				ob_end_clean();
				if ($msg != '') {
					$this->parent->set('extension.message', $msg);
				}
			}
		}

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Database Processing Section
		 * ---------------------------------------------------------------------------------------------
		 */

		/*
		 * Let's run the uninstall queries for the component
		 *	If backward compatibility is required - run queries in xml file
		 *	If Joomla 1.5 compatible, with discreet sql files - execute appropriate
		 *	file for utf-8 support or non-utf support
		 */
		$result = $this->parent->parseQueries($this->manifest->getElementByPath('uninstall/queries'));
		if ($result === false) {
			// Install failed, rollback changes
			JError::raiseWarning(100, JText::_('Component').' '.JText::_('Uninstall').': '.JText::_('SQL Error')." ".$db->stderr(true));
			$retval = false;
		} elseif ($result === 0) {
			// no backward compatibility queries found - try for Joomla 1.5 type queries
			// second argument is the utf compatible version attribute
			$utfresult = $this->parent->parseSQLFiles($this->manifest->getElementByPath('uninstall/sql'));
			if ($utfresult === false) {
				// Install failed, rollback changes
				JError::raiseWarning(100, JText::_('Component').' '.JText::_('Uninstall').': '.JText::_('SQLERRORORFILE')." ".$db->stderr(true));
				$retval = false;
			}
		}

		$this->_removeAdminMenus($row);

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Filesystem Processing Section
		 * ---------------------------------------------------------------------------------------------
		 */

		// Let's remove language files and media in the JROOT/images/ folder that are
		// associated with the component we are uninstalling
		$this->parent->removeFiles($this->manifest->getElementByPath('media'));
		$this->parent->removeFiles($this->manifest->getElementByPath('languages'));
		$this->parent->removeFiles($this->manifest->getElementByPath('administration/languages'), 1);

		// Now we need to delete the installation directories.  This is the final step in uninstalling the component.
		if (trim($row->option)) {
			// Delete the component site directory
			if (is_dir($this->parent->getPath('extension_site'))) {
				if (!JFolder::delete($this->parent->getPath('extension_site'))) {
					JError::raiseWarning(100, JText::_('Component').' '.JText::_('Uninstall').': '.JText::_('Unable to remove the component site directory'));
					$retval = false;
				}
			}

			// Delete the component admin directory
			if (is_dir($this->parent->getPath('extension_administrator'))) {
				if (!JFolder::delete($this->parent->getPath('extension_administrator'))) {
					JError::raiseWarning(100, JText::_('Component').' '.JText::_('Uninstall').': '.JText::_('Unable to remove the component admin directory'));
					$retval = false;
				}
			}
			return $retval;
		} else {
			// No component option defined... cannot delete what we don't know about
			JError::raiseWarning(100, 'Component Uninstall: Option field empty, cannot remove files');
			return false;
		}
	}
	
}
