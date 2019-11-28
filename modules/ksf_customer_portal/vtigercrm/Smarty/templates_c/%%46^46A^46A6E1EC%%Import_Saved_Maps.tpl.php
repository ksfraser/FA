<?php /* Smarty version 2.6.18, created on 2015-03-17 06:11:52
         compiled from modules/Import/Import_Saved_Maps.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'getTranslatedString', 'modules/Import/Import_Saved_Maps.tpl', 13, false),array('modifier', 'vtiger_imageurl', 'modules/Import/Import_Saved_Maps.tpl', 21, false),)), $this); ?>

<span class="small"><?php echo getTranslatedString('LBL_USE_SAVED_MAPPING', $this->_tpl_vars['MODULE']); ?>
</span>&nbsp;&nbsp;
<select name="saved_maps" id="saved_maps" class="small" onchange="ImportJs.loadSavedMap();">
	<option id="-1" value="" selected>--<?php echo getTranslatedString('LBL_SELECT', $this->_tpl_vars['MODULE']); ?>
--</option>
	<?php $_from = $this->_tpl_vars['SAVED_MAPS']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['_MAP_ID'] => $this->_tpl_vars['_MAP']):
?>
	<option id="<?php echo $this->_tpl_vars['_MAP_ID']; ?>
" value="<?php echo $this->_tpl_vars['_MAP']->getStringifiedContent(); ?>
"><?php echo $this->_tpl_vars['_MAP']->getValue('name'); ?>
</option>
	<?php endforeach; endif; unset($_from); ?>
</select>
<span id="delete_map_container" style="display:none;">
	<img valign="absmiddle" src="<?php echo vtiger_imageurl('delete.gif', $this->_tpl_vars['THEME']); ?>
" style="cursor:pointer;"
		 onclick="ImportJs.deleteMap('<?php echo $this->_tpl_vars['FOR_MODULE']; ?>
');" alt="<?php echo getTranslatedString('LBL_DELETE', $this->_tpl_vars['FOR_MODULE']); ?>
" />
</span>