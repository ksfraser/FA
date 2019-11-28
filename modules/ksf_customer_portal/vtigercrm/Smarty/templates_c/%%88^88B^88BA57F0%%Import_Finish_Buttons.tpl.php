<?php /* Smarty version 2.6.18, created on 2015-03-17 18:44:57
         compiled from modules/Import/Import_Finish_Buttons.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'getTranslatedString', 'modules/Import/Import_Finish_Buttons.tpl', 13, false),)), $this); ?>

<input type="button" name="next" value="<?php echo getTranslatedString('LBL_IMPORT_MORE', $this->_tpl_vars['MODULE']); ?>
" class="crmButton big create"
	   onclick="location.href='index.php?module=<?php echo $this->_tpl_vars['FOR_MODULE']; ?>
&action=Import&return_module=<?php echo $this->_tpl_vars['FOR_MODULE']; ?>
&return_action=index'" />
&nbsp;&nbsp;
<input type="button" name="next" value="<?php echo getTranslatedString('LBL_VIEW_LAST_IMPORTED_RECORDS', $this->_tpl_vars['MODULE']); ?>
" class="crmButton big cancel"
	   onclick="return window.open('index.php?module=<?php echo $this->_tpl_vars['FOR_MODULE']; ?>
&action=<?php echo $this->_tpl_vars['FOR_MODULE']; ?>
Ajax&file=Import&mode=listview&start=1&foruser=<?php echo $this->_tpl_vars['OWNER_ID']; ?>
','test','width=700,height=650,resizable=1,scrollbars=0,top=150,left=200');" />
&nbsp;&nbsp;
<?php if ($this->_tpl_vars['MERGE_ENABLED'] == '0'): ?>
<input type="button" name="next" value="<?php echo getTranslatedString('LBL_UNDO_LAST_IMPORT', $this->_tpl_vars['MODULE']); ?>
" class="crmButton big delete"
	   onclick="location.href='index.php?module=<?php echo $this->_tpl_vars['FOR_MODULE']; ?>
&action=Import&mode=undo_import&foruser=<?php echo $this->_tpl_vars['OWNER_ID']; ?>
'" />
&nbsp;&nbsp;
<?php endif; ?>
<input type="button" name="cancel" value="<?php echo getTranslatedString('LBL_FINISH_BUTTON_LABEL', $this->_tpl_vars['MODULE']); ?>
" class="crmButton big edit"
	   onclick="location.href='index.php?module=<?php echo $this->_tpl_vars['FOR_MODULE']; ?>
&action=index'" />