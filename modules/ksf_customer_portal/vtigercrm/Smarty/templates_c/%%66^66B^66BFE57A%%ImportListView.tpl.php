<?php /* Smarty version 2.6.18, created on 2015-03-17 18:50:31
         compiled from modules/Import/ImportListView.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'vtiger_imageurl', 'modules/Import/ImportListView.tpl', 24, false),array('modifier', 'getTranslatedString', 'modules/Import/ImportListView.tpl', 33, false),)), $this); ?>
<link rel="stylesheet" type="text/css" href="themes/<?php echo $this->_tpl_vars['THEME']; ?>
/style.css">
<script language="javascript" type="text/javascript" src="include/scriptaculous/prototype.js"></script>
<script language="JavaScript" type="text/javascript" src="include/js/ListView.js"></script>
<script language="JavaScript" type="text/javascript" src="include/js/general.js"></script>
<script language="JavaScript" type="text/javascript" src="include/js/vtlib.js"></script>
<script language="JavaScript" type="text/javascript" src="modules/MailManager/resources/jquery-1.6.2.min.js"></script>
<script type="text/javascript" charset="utf-8">
	jQuery.noConflict();
</script>
<script language="JavaScript" type="text/javascript" src="modules/Import/resources/Import.js"></script>

<div id="status" style="position:absolute;display:none;left:850px;top:15px;height:27px;white-space:nowrap;">
	<img src="<?php echo vtiger_imageurl('status.gif', $this->_tpl_vars['THEME']); ?>
">
</div>
<form onsubmit="VtigerJS_DialogBox.block();" action="index.php" enctype="multipart/form-data" method="POST" name="importBasic">
	<input type="hidden" name="module" value="<?php echo $this->_tpl_vars['FOR_MODULE']; ?>
" />
	<input type="hidden" name="action" value="Import" />
	<input type="hidden" name="mode" value="upload_and_parse" />
	<table cellpadding="5" cellspacing="12" class="searchUIBasic">
		<tr>
			<td class="heading2" align="left" colspan="2">
				<?php echo getTranslatedString('LBL_IMPORT', $this->_tpl_vars['MODULE']); ?>
 <?php echo getTranslatedString($this->_tpl_vars['FOR_MODULE'], $this->_tpl_vars['FOR_MODULE']); ?>
 - <?php echo getTranslatedString('LBL_LAST_IMPORTED_RECORDS', $this->_tpl_vars['MODULE']); ?>

			</td>
		</tr>
		<?php if ($this->_tpl_vars['ERROR_MESSAGE'] != ''): ?>
		<tr>
			<td class="style1" align="left" colspan="2">
				<?php echo $this->_tpl_vars['ERROR_MESSAGE']; ?>

			</td>
		</tr>
		<?php endif; ?>
		<tr>
			<td class="leftFormBorder1" width="60%" valign="top">
				<div id="import_listview_contents" class="small">
				<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'modules/Import/ListViewEntries.tpl', 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
				</div>
			</td>
		</tr>
	</table>
</form>