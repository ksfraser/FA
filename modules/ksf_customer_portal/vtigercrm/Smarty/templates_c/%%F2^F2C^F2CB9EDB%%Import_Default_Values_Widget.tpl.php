<?php /* Smarty version 2.6.18, created on 2015-03-17 06:11:56
         compiled from modules/Import/Import_Default_Values_Widget.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'getTranslatedString', 'modules/Import/Import_Default_Values_Widget.tpl', 20, false),array('modifier', 'vtiger_imageurl', 'modules/Import/Import_Default_Values_Widget.tpl', 39, false),)), $this); ?>

<div style="visibility: hidden; height: 0px;" id="defaultValuesElementsContainer">
	<?php $_from = $this->_tpl_vars['AVAILABLE_FIELDS']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['_FIELD_NAME'] => $this->_tpl_vars['_FIELD_INFO']):
?>
	<span id="<?php echo $this->_tpl_vars['_FIELD_NAME']; ?>
_defaultvalue_container" name="<?php echo $this->_tpl_vars['_FIELD_NAME']; ?>
_defaultvalue" class="small">
		<?php $this->assign('_FIELD_TYPE', $this->_tpl_vars['_FIELD_INFO']->getFieldDataType()); ?>
		<?php if ($this->_tpl_vars['_FIELD_TYPE'] == 'picklist' || $this->_tpl_vars['_FIELD_TYPE'] == 'multipicklist'): ?>
			<select id="<?php echo $this->_tpl_vars['_FIELD_NAME']; ?>
_defaultvalue" name="<?php echo $this->_tpl_vars['_FIELD_NAME']; ?>
_defaultvalue" class="small">
			<?php $_from = $this->_tpl_vars['_FIELD_INFO']->getPicklistDetails(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['_PICKLIST_DETAILS']):
?>
				<option value="<?php echo $this->_tpl_vars['_PICKLIST_DETAILS']['value']; ?>
"><?php echo getTranslatedString($this->_tpl_vars['_PICKLIST_DETAILS']['label'], $this->_tpl_vars['FOR_MODULE']); ?>
</option>
			<?php endforeach; endif; unset($_from); ?>
			</select>
		<?php elseif ($this->_tpl_vars['_FIELD_TYPE'] == 'integer'): ?>
			<input type="text" id="<?php echo $this->_tpl_vars['_FIELD_NAME']; ?>
_defaultvalue" name="<?php echo $this->_tpl_vars['_FIELD_NAME']; ?>
_defaultvalue" class="small" value="0" />
		<?php elseif ($this->_tpl_vars['_FIELD_TYPE'] == 'owner' || $this->_tpl_vars['_FIELD_INFO']->getUIType() == '52'): ?>
			<select id="<?php echo $this->_tpl_vars['_FIELD_NAME']; ?>
_defaultvalue" name="<?php echo $this->_tpl_vars['_FIELD_NAME']; ?>
_defaultvalue" class="small">
				<option value="">--<?php echo getTranslatedString('LBL_NONE', $this->_tpl_vars['FOR_MODULE']); ?>
--</option>
			<?php $_from = $this->_tpl_vars['USERS_LIST']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['_ID'] => $this->_tpl_vars['_NAME']):
?>
				<option value="<?php echo $this->_tpl_vars['_ID']; ?>
"><?php echo $this->_tpl_vars['_NAME']; ?>
</option>
			<?php endforeach; endif; unset($_from); ?>
			<?php if ($this->_tpl_vars['_FIELD_INFO']->getUIType() == '53'): ?>
				<?php $_from = $this->_tpl_vars['GROUPS_LIST']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['_ID'] => $this->_tpl_vars['_NAME']):
?>
				<option value="<?php echo $this->_tpl_vars['_ID']; ?>
"><?php echo $this->_tpl_vars['_NAME']; ?>
</option>
				<?php endforeach; endif; unset($_from); ?>
			<?php endif; ?>
			</select>
		<?php elseif ($this->_tpl_vars['_FIELD_TYPE'] == 'date'): ?>
			<input type="text" id="<?php echo $this->_tpl_vars['_FIELD_NAME']; ?>
_defaultvalue" name="<?php echo $this->_tpl_vars['_FIELD_NAME']; ?>
_defaultvalue" class="small" value="" />
			<img border=0 src="<?php echo vtiger_imageurl('btnL3Calendar.gif', $this->_tpl_vars['THEME']); ?>
" id="jscal_trigger_<?php echo $this->_tpl_vars['_FIELD_NAME']; ?>
"
				 alt="<?php echo getTranslatedString('LBL_SET_DATE', $this->_tpl_vars['FOR_MODULE']); ?>
" title="<?php echo getTranslatedString('LBL_SET_DATE', $this->_tpl_vars['FOR_MODULE']); ?>
" />
			<script type="text/javascript">
			Calendar.setup (
				{
					inputField : "<?php echo $this->_tpl_vars['_FIELD_NAME']; ?>
_defaultvalue",
					ifFormat : "%Y-%m-%d",
					showsTime : false,
					button : "jscal_trigger_<?php echo $this->_tpl_vars['_FIELD_NAME']; ?>
",
					singleClick : true, step : 1
				}
			);
			</script>
		<?php elseif ($this->_tpl_vars['_FIELD_TYPE'] == 'datetime'): ?>
			<input type="text" id="<?php echo $this->_tpl_vars['_FIELD_NAME']; ?>
_defaultvalue" name="<?php echo $this->_tpl_vars['_FIELD_NAME']; ?>
_defaultvalue" class="small" value="" />
			<img border=0 src="<?php echo vtiger_imageurl('btnL3Calendar.gif', $this->_tpl_vars['THEME']); ?>
" id="jscal_trigger_<?php echo $this->_tpl_vars['_FIELD_NAME']; ?>
"
				 alt="<?php echo getTranslatedString('LBL_SET_DATE_TIME', $this->_tpl_vars['FOR_MODULE']); ?>
" title="<?php echo getTranslatedString('LBL_SET_DATE_TIME', $this->_tpl_vars['FOR_MODULE']); ?>
" />
			<script type="text/javascript">
			Calendar.setup (
				{
					inputField : "<?php echo $this->_tpl_vars['_FIELD_NAME']; ?>
_defaultvalue",
					ifFormat : "%Y-%m-%d",
					showsTime : true,
					button : "jscal_trigger_<?php echo $this->_tpl_vars['_FIELD_NAME']; ?>
",
					singleClick : true, step : 1
				}
			);
			</script>
		<?php elseif ($this->_tpl_vars['_FIELD_TYPE'] == 'boolean'): ?>
			<input type="checkbox" id="<?php echo $this->_tpl_vars['_FIELD_NAME']; ?>
_defaultvalue" name="<?php echo $this->_tpl_vars['_FIELD_NAME']; ?>
_defaultvalue" class="small" />
		<?php elseif ($this->_tpl_vars['_FIELD_TYPE'] != 'reference'): ?>
			<input type="input" id="<?php echo $this->_tpl_vars['_FIELD_NAME']; ?>
_defaultvalue" name="<?php echo $this->_tpl_vars['_FIELD_NAME']; ?>
_defaultvalue" class="small" />
		<?php endif; ?>
	</span>
	<?php endforeach; endif; unset($_from); ?>
</div>