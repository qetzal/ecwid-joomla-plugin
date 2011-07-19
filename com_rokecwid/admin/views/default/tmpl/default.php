<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php

JHTML::_('behavior.tooltip');
$model = $this->getModel();

?>
<form action="index.php" method="post" name="adminForm" class="rokecwid-form" autocomplete="off">

	<table class="noshow">
		<tr valign="top">
			<td width="60%">
				<div id="rokecwid-guide">
					<h2>Guide</h2>
					<div id="rokecwid-wrapper">
						<div id="rokecwid-content">
							<?php echo $model->loadTemplate('guide'); ?>
						</div>
					</div>
				</div>
			</td>
			<td width="40%">
				<div id="rokecwid-config">
					<h2>Configuration</h2>
					<?php echo $model->render($this->params); ;?>
					
					<div class="clear"></div>
					<div id="rockettheme">
						<a target="_blank" href="http://www.rockettheme.com/"><span>Brought to you by RocketTheme</span></a>
					</div>
				</div>
			</td>
		</tr>
	</table>

	<input type="hidden" name="component" value="<?php echo $this->component;?>" />
	<input type="hidden" name="controller" value="default" />
	<input type="hidden" name="option" value="com_rokecwid" />
	<input type="hidden" name="task" value="" />
</form>

<?php echo JHTML::_('behavior.keepalive'); ?>
