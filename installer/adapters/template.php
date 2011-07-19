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

if (!class_exists("JInstallerTemplate"))
{
    @include_once(JPATH_LIBRARIES . '/joomla/installer/adapters/template.php');
}
/**
 * Component installer
 *
 * @package        Joomla.Framework
 * @subpackage    Installer
 * @since        1.5
 */
class RokInstallerTemplate extends JInstallerTemplate
{

    protected $master_id = 0;

    const DEFAULT_ACCESS = 1;
    const DEFAULT_ENABLED = 'true';
    const DEFAULT_PROTECTED = 'false';
    const DEFAULT_CLIENT = 'site';
    const DEFAULT_ORDERING = 0;
    const DEFAULT_PARAMS = null;

    protected $access;
    protected $enabled;
    protected $client;
    protected $ordering;
    protected $protected;
    protected $params;


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

        $extension = $this->loadExtension($extensionId);

        // update the extension info
        $this->updateExtension($extension);

        if (count($coginfo->style) > 0){
            $this->removeStyles($extension->element);
        }
        foreach ($coginfo->style as $styleinfo)
        {
            $this->addStyle($extension->element, $styleinfo);
        }
    }


    protected function &loadExtension($eid)
    {
        $row = JTable::getInstance('extension');
        $row->load($eid);
        return $row;
    }

    protected function removeStyles($template_name)
    {
        $db = $this->parent->getDbo();
        $query = 'DELETE FROM #__template_styles' .
                 ' WHERE template = ' . $db->Quote($template_name);
        $db->setQuery($query);
        $db->Query();
    }


    protected function addStyle($templateName, &$styleInfo)
    {
        $params = false;
        $this_is_master = false;
        $db = $this->parent->getDbo();

        if ($styleInfo['paramsfile'])
        {
            $paramfile = $this->parent->getPath('source') . DS . (string)$styleInfo['paramsfile'];
            if (file_exists($paramfile))
            {
                $params = $this->getParamsFromFile($paramfile);
            }
        }
        else if ($styleInfo->params)
        {
            $params = json_decode((string)$styleInfo->params);
        }


        if ($params && $this->master_id != 0)
        {
            $params->master = $this->master_id;
        }
        else
        {
            $params->master = 'true';
            $this_is_master = true;
        }

        if ($styleInfo['default'])
        {
            $default = (strtolower((string)$styleInfo['default']) == 'true') ? 1 : 0;
        }
        else $default = 0;

        if ($default)
        {
            // Reset the home fields for the client_id.
            $db->setQuery(
                'UPDATE #__template_styles' .
                ' SET home = '. $db->Quote('0') .
                ' WHERE client_id = ' . (int)$this->client .
                ' AND home = '. $db->Quote('1')
            );

            $db->query();
        }

        //insert record in #__template_styles
        $query = $db->getQuery(true);
        $query->clear();
        $query->insert('#__template_styles');
        $query->set('template=' . $db->Quote($templateName));
        $query->set('client_id=' . $this->client);
        $query->set('home=' . $db->Quote($default));
        $query->set('title=' . $db->Quote($styleInfo['name']));
        if ($params)
        {
            $query->set('params=' . $db->Quote(json_encode($params)));
        }
        $db->setQuery($query);
        $db->query();


        if ($this_is_master)
        {
            $this->master_id = $db->insertid();
        }

        // Clean the cache.
        $cache = JFactory::getCache();
        $cache->clean('com_templates');
        $cache->clean('_system');
    }


    /**
     * Method to parse the parameters of an extension, build the INI
     * string for it's default parameters, and return the INI string.
     *
     * @access    public
     * @return    string    INI string of parameter values
     * @since    1.5
     */
    public function getParamsFromFile($filepath)
    {

        //   xpath for names //form//field|//form//fields[@default]|//form//fields[@value]
        //   xpath for parents  ancestor::fields[@name][not(@ignore-group)]/@name|ancestor::set[@name]/@name
        $xml = JFactory::getXML($filepath);

        $params = $xml->xpath('//form//field|//form//fields[@default]|//form//fields[@value]');
        $defaults = new stdClass();
        foreach ($params as $param)
        {
            $attrs = $param->xpath('ancestor::fields[@name][not(@ignore-group)]/@name|ancestor::set[@name]/@name');
            $groups = array_map('strval', $attrs ? $attrs : array());
            $groups[] = (string)$param['name'];
            $def_array_eval = '$defaults->' . implode('->', $groups) . ' = (string)$param[\'default\'];';
            if ($param['default']) @eval($def_array_eval);
        }
        return $defaults;
    }

    public function getInstallType()
    {
        return $this->route;
    }

}
