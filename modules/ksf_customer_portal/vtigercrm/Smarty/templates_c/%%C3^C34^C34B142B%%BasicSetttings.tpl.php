<?php /* Smarty version 2.6.18, created on 2015-02-18 04:16:53
         compiled from modules/CustomerPortal/BasicSetttings.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'vtiger_imageurl', 'modules/CustomerPortal/BasicSetttings.tpl', 30, false),array('modifier', 'getTranslatedString', 'modules/CustomerPortal/BasicSetttings.tpl', 39, false),)), $this); ?>
<script language="JavaScript" type="text/javascript" src="modules/<?php echo $this->_tpl_vars['MODULE']; ?>
/<?php echo $this->_tpl_vars['MODULE']; ?>
.js"></script>
<style type="text/css">
	<?php echo '
td.showPanelBg div.small table.lvtBg tbody tr td table.small {
	border-bottom: 1px solid #ccc;
}
'; ?>

</style>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'Buttons_List.tpl', 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<form  method="post" name="new" id="form">
	<input type="hidden" name="module" value="CustomerPortal">
	<input type="hidden" name="action" value="ListView">
	<input type="hidden" name="return_action" value="ListView">
	<input type="hidden" name="mode" value="save">
	<table border=0 cellspacing=0 cellpadding=0 width="100%">
    <tr>
        <td valign=top><img src="<?php echo vtiger_imageurl('showPanelTopLeft.gif', $this->_tpl_vars['THEME']); ?>
"></td>
			<td class="showPanelBg" valign="top" width="100%" style="padding:05px;">
				<div class="small" style="padding:30px;width:100%;position:relative;">
					<table border=0 cellspacing=1 cellpadding=0 width="100%" class="lvtBg" >
			<tr>
				<td>
								<table border=0 cellspacing=0 cellpadding=0 width="95%" class="small" >
					<!-- Tab Links -->
						<tr>
										<td class="dvtSelectedCell" nowrap><?php echo getTranslatedString('LBL_CUSTOMERPORTAL_SETTINGS', $this->_tpl_vars['MODULE']); ?>
</td>
							<td class="dvtTabCache" width="100%">&nbsp;</td>
						</tr>
					<!-- Acutal Contents -->				
						<tr>
										<td colspan="2">
											<table border=0 cellspacing=0 cellpadding=10 width="100%" align="center" class="dvtContentSpace" style='border-bottom: 0'>
												<tr>
													<td align="left">
								<div id='portallist'>
								<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "modules/CustomerPortal/BasicSetttingsContents.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
								</div>
							</td>
						</tr>
						</table>
										</td>
						</tr>
						</table>
				</td>
			</tr>
			</table>
		</div>
		</td>
	</tr>
	</table>
</form>