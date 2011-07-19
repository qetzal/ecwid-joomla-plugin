<?php
/**
 * @package   Installer Bundle Framework - RocketTheme
 * @version   1.3 March 1, 2011
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Installer uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

if (!class_exists("JInstallerModule"))
{
    @include_once(JPATH_LIBRARIES . '/joomla/installer/adapters/module.php');
}
/**
 * Component installer
 *
 * @package        Joomla.Framework
 * @subpackage    Installer
 * @since        1.5
 */
class RokInstallerModule extends JInstallerModule
{
    protected $access;
    protected $enabled;
    protected $client;
    protected $ordering = 0;
    protected $protected;
    protected $params;

    const DEFAULT_ACCESS = 1;
    const DEFAULT_ENABLED = 'true';
    const DEFAULT_PROTECTED = 'false';
    const DEFAULT_CLIENT = 'site';
    const DEFAULT_ORDERING = 0;
    const DEFAULT_PARAMS = null;


    public function setAccess($access)
    {
        $this->access = $access;
    }

    public function getAccess()
    {
        return $this->access;
    }

    public function setClient($client)
    {
        switch ($client)
        {
            case 'site':
                $client = 0;
                break;
            case 'adminstrator':
                $client = 1;
                break;
            default:
                $client = (int)$client;
                break;
        }
        $this->client = $client;
    }

    public function getClient()
    {
        return $this->client;
    }

    public function setEnabled($enabled)
    {
        switch (strtolower($enabled))
        {
            case 'true':
                $enabled = 1;
                break;
            case 'false':
                $enabled = 0;
                break;
            default:
                $enabled = (int)$enabled;
                break;
        }
        $this->enabled = $enabled;
    }

    public function getEnabled()
    {
        return $this->enabled;
    }

    public function setOrdering($ordering)
    {
        $this->ordering = $ordering;
    }

    public function getOrdering()
    {
        return $this->ordering;
    }

    public function setProtected($protected)
    {
        switch (strtolower($protected))
        {
            case 'true':
                $protected = 1;
                break;
            case 'false':
                $protected = 0;
                break;
            default:
                $protected = (int)$protected;
                break;
        }
        $this->protected = $protected;
    }

    public function getProtected()
    {
        return $this->protected;
    }

    public function setParams($params)
    {
        $this->params = $params;
    }

    public function getParams()
    {
        return $this->params;
    }

    protected function updateExtension(&$extension)
    {
        if ($extension)
        {
            $extension->access = $this->access;
            $extension->enabled = $this->enabled;
            $extension->protected = $this->protected;
            $extension->client_id = $this->client;
            $extension->ordering = $this->ordering;
            $extension->params = $this->params;
            $extension->store();
        }
    }

    public function postInstall($extensionId)
    {

        $coginfo = $this->parent->getCogInfo();

        $this->setAccess(($coginfo['access']) ? (int)$coginfo['access'] : self::DEFAULT_ACCESS);
        $this->setEnabled(($coginfo['enabled']) ? (string)$coginfo['enabled'] : self::DEFAULT_ENABLED);
        $this->setProtected(($coginfo['protected']) ? (string)$coginfo['protected'] : self::DEFAULT_PROTECTED);
        $this->setClient(($coginfo['client']) ? (string)$coginfo['client'] : self::DEFAULT_CLIENT);
        $this->setParams(($coginfo->params) ? (string)$coginfo->params : self::DEFAULT_PARAMS);
        $this->setOrdering(($coginfo['ordering']) ? (int)$coginfo['ordering'] : self::DEFAULT_ORDERING);

        $extention = $this->loadExtension($extensionId);

        // update the extension info
        $this->updateExtension($extention);

        // remove the auto installed module instance
        $this->removeInstances($extention->element);

        foreach($coginfo->module as $moduleinfo)
        {
            $this->addInstance($extention->element, $moduleinfo);
        }

    }

    protected function &loadExtension($eid)
    {
        $row = JTable::getInstance('extension');
        $row->load($eid);
        return $row;
    }

    protected function removeInstances($module_name)
    {
        $db = $this->parent->getDbo();
        // Lets delete all the module copies for the type we are uninstalling
        $query = 'SELECT `id`' .
                 ' FROM `#__modules`' .
                 ' WHERE module = ' . $db->Quote($module_name);
        $db->setQuery($query);

        try
        {
            $modules = $db->loadResultArray();
        }
        catch (JException $e)
        {
            $modules = array();
        }

        // Do we have any module copies?
        if (count($modules))
        {
            // Ensure the list is sane
            JArrayHelper::toInteger($modules);
            $modID = implode(',', $modules);

            // Wipe out any items assigned to menus
            $query = 'DELETE' .
                     ' FROM #__modules_menu' .
                     ' WHERE moduleid IN (' . $modID . ')';
            $db->setQuery($query);
            try
            {
                $db->query();
            }
            catch (JException $e)
            {
                JError::raiseWarning(100, JText::sprintf('JLIB_INSTALLER_ERROR_MOD_UNINSTALL_EXCEPTION', $db->stderr(true)));
                $retval = false;
            }

            // Wipe out any instances in the modules table
            $query = 'DELETE' .
                     ' FROM #__modules' .
                     ' WHERE id IN (' . $modID . ')';
            $db->setQuery($query);

            try
            {
                $db->query();
            }
            catch (JException $e)
            {
                JError::raiseWarning(100, JText::sprintf('JLIB_INSTALLER_ERROR_MOD_UNINSTALL_EXCEPTION', $db->stderr(true)));
                $retval = false;
            }
        }
    }

    protected function addInstance($module_name, &$moduleInfo){

        $db = $this->parent->getDbo();

        $module = JTable::getInstance('module');

        $module->set('module', $module_name);
        if ($moduleInfo['title']) $module->set('title', (string)$moduleInfo['title']);
        if ($moduleInfo['position']) $module->set('position', (string)$moduleInfo['position']);
        if ($moduleInfo['access']) $module->set('access', (int)$moduleInfo['access']);
        if ($moduleInfo['ordering']) $module->set('ordering', (int)$moduleInfo['ordering']);
        $module->set('language', ($moduleInfo['language'])?(string)$moduleInfo['language']:'*');
        if ($moduleInfo['published']){
            $published = (string)$moduleInfo['published'];
            switch (strtolower($published))
            {
                case 'true':
                    $published = 1;
                    break;
                case 'false':
                    $published = 0;
                    break;
                default:
                    $published = (int)$published;
                    break;
            }
            $module->set('published', $published);
        }
        if ($moduleInfo['showtitle']){
            $showtitle = (string)$moduleInfo['showtitle'];
            switch (strtolower($showtitle))
            {
                case 'true':
                    $showtitle = 1;
                    break;
                case 'false':
                    $showtitle = 0;
                    break;
                default:
                    $showtitle = (int)$showtitle;
                    break;
            }
            $module->set('showtitle', $showtitle);
        }
        if($moduleInfo['client']){
            $client = (string)$moduleInfo['client'];
            switch ($client)
            {
                case 'site':
                    $client_id = 0;
                    break;
                case 'adminstrator':
                    $client_id = 1;
                    break;
                default:
                    $client_id = (int)$client;
                    break;
            }
            $module->set('client_id', $client_id);
        }
        if($moduleInfo->params){
            $module->set('params', (string)$moduleInfo->params);
        }
        if($moduleInfo->content){
            $module->set('content', (string)$moduleInfo->content);
        }
        if($moduleInfo->note){
            $module->set('note', (string)$moduleInfo->note);
        }
        $module->store();

        $module_id = $db->insertid();

        $query	= $db->getQuery(true);
        if ($moduleInfo['assigned'] && strtolower((string)$moduleInfo['assigned']) == 'all')
        {
            $query->clear();
            $query->insert('#__modules_menu');
            $query->set('moduleid='.(int)$module_id);
            $query->set('menuid=0');
            $db->setQuery((string)$query);
            $db->query();
        }
    }


    public function getInstallType()
    {
        return strtolower($this->route);
    }
}
