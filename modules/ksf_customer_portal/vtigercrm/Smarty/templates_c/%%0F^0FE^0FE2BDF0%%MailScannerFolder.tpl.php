<?php /* Smarty version 2.6.18, created on 2015-03-13 22:53:45
         compiled from MailScanner/MailScannerFolder.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'vtiger_imageurl', 'MailScanner/MailScannerFolder.tpl', 33, false),)), $this); ?>

<script language="JAVASCRIPT" type="text/javascript" src="include/js/smoothscroll.js"></script>
<script type="text/javascript">
<?php echo '
function vtmailscanner_folders_resetAll_To(checktype) {
	var form = $(\'form\');
	var inputs = form.getElementsByTagName(\'input\');
	for(var index = 0; index < inputs.length; ++index) {
		var input = inputs[index];
		if(input.type == \'checkbox\' && input.name.indexOf(\'folder_\') == 0) {
			input.checked = checktype;
		}
	}
}
'; ?>

</script>

<br>
<table align="center" border="0" cellpadding="0" cellspacing="0" width="98%">
<tbody>
<tr>
	<td valign="top"><img src="<?php echo vtiger_imageurl('showPanelTopLeft.gif', $this->_tpl_vars['THEME']); ?>
"></td>
    <td class="showPanelBg" style="padding: 10px;" valign="top" width="100%">

	<form action="index.php" method="post" id="form" onsubmit="VtigerJS_DialogBox.block();">
		<input type='hidden' name='module' value='Settings'>
		<input type='hidden' name='action' value='MailScanner'>
		<input type='hidden' name='scannername' value="<?php echo $this->_tpl_vars['SCANNERINFO']['scannername']; ?>
">
		<input type='hidden' name='mode' value='foldersave'>
		<input type='hidden' name='return_action' value='MailScanner'>
		<input type='hidden' name='return_module' value='Settings'>
		<input type='hidden' name='parenttab' value='Settings'>

        <br>

		<div align=center>
			<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'SetMenu.tpl', 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
				<!-- DISPLAY -->
				<table border=0 cellspacing=0 cellpadding=5 width=100% class="settingsSelUITopLine">
				<tr>
					<td width=50 rowspan=2 valign=top><img src="<?php echo vtiger_imageurl('mailScanner.gif', $this->_tpl_vars['THEME']); ?>
" alt="<?php echo $this->_tpl_vars['MOD']['LBL_MAIL_SCANNER']; ?>
" width="48" height="48" border=0 title="<?php echo $this->_tpl_vars['MOD']['LBL_MAIL_SCANNER']; ?>
"></td>
					<td class=heading2 valign=bottom><b><a href="index.php?module=Settings&action=index&parenttab=Settings"><?php echo $this->_tpl_vars['MOD']['LBL_SETTINGS']; ?>
</a> > <?php echo $this->_tpl_vars['MOD']['LBL_MAIL_SCANNER']; ?>
</b></td>
				</tr>
				<tr>
					<td valign=top class="small"><?php echo $this->_tpl_vars['MOD']['LBL_MAIL_SCANNER_DESCRIPTION']; ?>
</td>
				</tr>
				</table>
				
				<br>
				<table border=0 cellspacing=0 cellpadding=10 width=100% >
				<tr>
				<td>
				
				<table border=0 cellspacing=0 cellpadding=5 width=100% class="tableHeading">
				<tr>
					<td class="big" width="70%"><strong><?php echo $this->_tpl_vars['MOD']['LBL_MAILBOX']; ?>
 <?php echo $this->_tpl_vars['MOD']['LBL_FOLDER']; ?>
</strong></td>
					<td align="right">
						<input type="submit" class="crmbutton small create" onclick="this.form.mode.value='folderupdate'" value="<?php echo $this->_tpl_vars['MOD']['LBL_UPDATE']; ?>
"> 
						<a href='javascript:void(0);' onclick="vtmailscanner_folders_resetAll_To(true);"><?php echo $this->_tpl_vars['MOD']['LBL_SELECT']; ?>
 <?php echo $this->_tpl_vars['MOD']['LBL_ALL']; ?>
</a> |
						<a href='javascript:void(0);' onclick="vtmailscanner_folders_resetAll_To(false);"><?php echo $this->_tpl_vars['MOD']['LBL_UNSELECT']; ?>
 <?php echo $this->_tpl_vars['MOD']['LBL_ALL']; ?>
</a>
					</td>
				</tr>
				</table>

				<?php $this->assign('FOLDER_COL_LIMIT', '4'); ?>				
				<?php $this->assign('FOLDER_COL_INDEX', '0'); ?>				
				<?php $this->assign('FOLDER_ROW_OPEN', 'false'); ?>

				<table border=0 cellspacing=0 cellpadding=0 width=100% class="listRow">
				<tr valign=top>
	         	    <td class="small" valign=top ><table width="100%"  border="0" cellspacing="0" cellpadding="5">
						<?php $_from = $this->_tpl_vars['FOLDERINFO']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['FOLDERNAME'] => $this->_tpl_vars['FOLDER']):
?>
						<?php if (( $this->_tpl_vars['FOLDER_COL_INDEX'] % $this->_tpl_vars['FOLDER_COL_LIMIT'] ) == 0): ?>
						<tr>
						<?php $this->assign('FOLDER_ROW_OPEN', 'true'); ?>
						<?php endif; ?>
							<td>
								<input type="checkbox" name="folder_<?php echo $this->_tpl_vars['FOLDER']['folderid']; ?>
" value="<?php echo $this->_tpl_vars['FOLDERNAME']; ?>
" <?php if ($this->_tpl_vars['FOLDER']['enabled']): ?>checked="true"<?php endif; ?>>
								<a href='javascript:void(0)' title='Lastscan: <?php echo $this->_tpl_vars['FOLDER']['lastscan']; ?>
'><?php echo $this->_tpl_vars['FOLDERNAME']; ?>
</a></td>
						<?php if (( $this->_tpl_vars['FOLDER_COL_INDEX'] % $this->_tpl_vars['FOLDER_COL_LIMIT'] ) == ( $this->_tpl_vars['FOLDER_COL_LIMIT']-1 )): ?>
						</tr>
						<?php $this->assign('FOLDER_ROW_OPEN', 'false'); ?>
						<?php endif; ?>
						<?php $this->assign('FOLDER_COL_INDEX', $this->_tpl_vars['FOLDER_COL_INDEX']+1); ?>
						<?php endforeach; endif; unset($_from); ?>
						<?php if ($this->_tpl_vars['FOLDER_ROW_OPEN']): ?></tr><?php endif; ?>
					</td>
				</tr>
				<tr>
					<td colspan="<?php echo $this->_tpl_vars['FOLDER_COL_LIMIT']; ?>
" nowrap align="center">
						<input type="submit" class="crmbutton small save" value="<?php echo $this->_tpl_vars['APP']['LBL_SAVE_LABEL']; ?>
" />
						<input type="button" class="crmbutton small cancel" value="<?php echo $this->_tpl_vars['APP']['LBL_CANCEL_BUTTON_LABEL']; ?>
" 
							onclick="location.href='index.php?module=Settings&action=MailScanner&parenttab=Settings&scannername=<?php echo $this->_tpl_vars['SCANNERINFO']['scannername']; ?>
'"/>
					</td>
				</tr>
				</table>	
				
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
        <td valign="top"><img src="<?php echo vtiger_imageurl('showPanelTopRight.gif', $this->_tpl_vars['THEME']); ?>
"></td>
   </tr>
</tbody>
</form>
</table>

</tr>
</table>

</tr>
</table>