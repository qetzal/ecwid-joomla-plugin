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
defined('_JEXEC') or die('Restricted access');
JHTML::_('behavior.tooltip');
$model = $this->getModel();

?>
<form action="<?php echo JRoute::_('index.php?option=com_rokecwid&layout=default'); ?>" method="post" id="adminForm" class="form-validate ecwid-form">
	<table class="noshow">
		<tr valign="top">
			<td width="60%">
				<div id="rokecwid-guide">
					<h2>Guide</h2>
					<div id="rokecwid-wrapper">
						<div id="rokecwid-content">
							<?php echo $this->loadTemplate('guide'); ?>
						</div>
					</div>
				</div>
			</td>
			<td width="40%">
				<div id="rokecwid-config">
					<h2>Configuration</h2>
					<?php echo $this->render(); ;?>
					
					<div class="clear"></div>
					<div class="copyright">
	  					Based on RokEcwid module by <a target="_blank" href="http://www.rockettheme.com/"><span>RocketTheme</span></a>
          </div>
				</div>
			</td>
		</tr>
	</table>
    <input type="hidden" name="task" value="" />
    <?php echo JHtml::_('form.token'); ?>
</form>

<?php echo JHTML::_('behavior.keepalive'); ?>
