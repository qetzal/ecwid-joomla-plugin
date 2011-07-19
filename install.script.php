<?php
/**
 * @package   gantry
 * @subpackage core
 * @version   1.3 March 1, 2011
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */
class PlgSystemInstallerInstallerScript
{
    protected $packages = array();
    protected $sourcedir;
    protected $installerdir;
    protected $manifest;

    protected function setup($parent){
        $this->sourcedir = $parent->getParent()->getPath('source');
        $this->manifest = $parent->getParent()->getManifest();
        $this->installerdir = $this->sourcedir . DS . 'installer';
    }

    public function install($parent)
    {
        jimport('joomla.filesystem.file');
        jimport('joomla.filesystem.folder');

        $retval = true;
        $buffer = '';
        $install_html_file = dirname(__FILE__).'/install.html';
        $install_css_file = dirname(__FILE__).'/install.css';
        $tmp_path =  JPATH_ROOT . '/tmp';

        // Drop out Style
        if (file_exists($install_css_file)){
            $buffer .= JFile::read($install_html_file);
        }

        if (JFolder::exists($tmp_path)){
            // Copy install.css to tmp dir for inclusion
            JFile::copy($install_css_file, $tmp_path.'/install.css');
        }

        // Opening HTML
        ob_start();
        ?>
            <div id="rokinstall-logo">
		        <ul id="rokinstall-status">
        <?php
        $buffer .= ob_get_clean();

        // Cycle through cogs and install each
        if (count($this->manifest->cogs->children()))
        {
            require_once($this->installerdir . DS . 'RokInstaller.php');

            foreach ($this->manifest->cogs->children() as $cog)
            {
                $folder = $this->sourcedir . DS . trim($cog);

                jimport('joomla.installer.helper');
                if (is_dir($folder))
                {
                    // if its actually a directory then fill it up
                    $package = Array();
                    $package['dir'] = $folder;
                    $package['type'] = JInstallerHelper::detectType($folder);
                    $package['installer'] = new RokInstaller();
                    $package['name'] = (string)$cog->name;
                    $package['state'] = 'Success';
                    $package['description'] = (string)$cog->description;
                    $package['msg'] = '';
                    $package['type'] = ucfirst((string)$cog['type']);

                    $package['installer']->setCogInfo($cog);
                    // add installer to static for possible rollback
                    $this->packages[] = $package;
                    if (!$package['installer']->install($package['dir']))
                    {
                        while ($error = JError::getError(true)){
                            $package['msg'] .= $error;
                        }
                        $buffer .= $this->printerror($package,$package['msg']);
                        //$this->abort();
                        break;
                    }
                    if ($package['installer']->getInstallType() == 'install'){
                        $buffer .= $this->printInstall($package);
                    }
                    else {
                        $buffer .= $this->printUpdate($package);
                    }
                }
                else {
                    $package = Array();
                    $package['dir'] = $folder;
                    $package['name'] = (string)$cog->name;
                    $package['state'] = 'Failed';
                    $package['description'] = (string)$cog->description;
                    $package['msg'] = '';
                    $package['type'] = ucfirst((string)$cog['type']);
                    $buffer .= $this->printerror($package, JText::_('JLIB_INSTALLER_ABORT_NOINSTALLPATH'));
                    //$this->abort();
                    break;
                }
            }
        }
        else
        {
            $parent->getParent()->abort(JText::sprintf('JLIB_INSTALLER_ABORT_PACK_INSTALL_NO_FILES', JText::_('JLIB_INSTALLER_' . strtoupper($this->route))));
        }


        // Closing HTML
        ob_start();
        ?>
                </ul>
	        </div>
        <?php
        $buffer .= ob_get_clean();


        // Return stuff
        echo $buffer;
        return $retval;
    }

    public function uninstall($parent)
    {

    }

    public function update($parent)
    {
        return $this->install($parent);
    }

    public function preflight($type, $parent)
    {
        $this->setup($parent);
        //Load Event Handler
        $event_handler_file = $this->installerdir.'/RokInstallerEvents.php';
        require_once($event_handler_file);
        $dispatcher =& JDispatcher::getInstance();
        new RokInstallerEvents($dispatcher);
    }

    public function postflight($type, $parent)
    {
        $parent->getParent()->abort();
    }

    public function abort($msg=null, $type=null){
        if ($msg) {
			JError::raiseWarning(100, $msg);
		}
        foreach($this->packages as $package){
            $package['installer']->abort(null,$type);
        }
    }

    public function printerror($package, $msg){
        ob_start();
        ?>
       <li class="rokinstall-failure"><span class="rokinstall-row"><span class="rokinstall-icon"></span><?php echo $package['name'];?> installation failed</span>
            <span class="rokinstall-errormsg">
                <?php echo $msg; ?>
            </span>
        </li>
        <?php
        $out = ob_get_clean();
        return $out;
    }

    public function printInstall($package){
        ob_start();
        ?>
        <li class="rokinstall-success"><span class="rokinstall-row"><span class="rokinstall-icon"></span><?php echo $package['name'];?> installation was successful</span></li>
        <?php
        $out = ob_get_clean();
        return $out;
    }

    public function printUpdate($package){
        ob_start();
        ?>
        <li class="rokinstall-update"><span class="rokinstall-row"><span class="rokinstall-icon"></span><?php echo $package['name'];?> update was successful</span></li>
        <?php
        $out = ob_get_clean();
        return $out;
    }
}
