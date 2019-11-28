<?php /* Smarty version 2.6.18, created on 2015-03-17 06:11:52
         compiled from modules/Import/Import_Step4.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'getTranslatedString', 'modules/Import/Import_Step4.tpl', 16, false),array('modifier', 'textlength_check', 'modules/Import/Import_Step4.tpl', 55, false),)), $this); ?>

<table width="100%" cellspacing="0" cellpadding="5">
	<tr>
		<td class="heading2" width="10%">
			<?php echo getTranslatedString('LBL_IMPORT_STEP_4', $this->_tpl_vars['MODULE']); ?>
:
		</td>
		<td>
			<span class="big"><?php echo getTranslatedString('LBL_IMPORT_STEP_4_DESCRIPTION', $this->_tpl_vars['MODULE']); ?>
</span>
		</td>
		<td width="10%">&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td align="right">
			<div id="savedMapsContainer">
				<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "modules/Import/Import_Saved_Maps.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
			</div>
		</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td>
			<input type="hidden" name="field_mapping" id="field_mapping" value="" />
			<input type="hidden" name="default_values" id="default_values" value="" />
			<table width="100%" cellspacing="0" cellpadding="5" class="listRow">
				<tr>
					<?php if ($this->_tpl_vars['HAS_HEADER'] == true): ?>
					<td class="big tableHeading" width="25%"><b><?php echo getTranslatedString('LBL_FILE_COLUMN_HEADER', $this->_tpl_vars['MODULE']); ?>
</b></td>
					<?php endif; ?>
					<td class="big tableHeading" width="25%"><b><?php echo getTranslatedString('LBL_ROW_1', $this->_tpl_vars['MODULE']); ?>
</b></td>
					<td class="big tableHeading" width="25%"><b><?php echo getTranslatedString('LBL_CRM_FIELDS', $this->_tpl_vars['MODULE']); ?>
</b></td>
					<td class="big tableHeading" width="25%"><b><?php echo getTranslatedString('LBL_DEFAULT_VALUE', $this->_tpl_vars['MODULE']); ?>
</b></td>
				</tr>
				<?php $_from = $this->_tpl_vars['ROW_1_DATA']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['headerIterator'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['headerIterator']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['_HEADER_NAME'] => $this->_tpl_vars['_FIELD_VALUE']):
        $this->_foreach['headerIterator']['iteration']++;
?>
				<?php $this->assign('_COUNTER', $this->_foreach['headerIterator']['iteration']); ?>
				<tr class="fieldIdentifier" id="fieldIdentifier<?php echo $this->_tpl_vars['_COUNTER']; ?>
">
					<?php if ($this->_tpl_vars['HAS_HEADER'] == true): ?>
					<td class="cellLabel">
						<span name="header_name"><?php echo $this->_tpl_vars['_HEADER_NAME']; ?>
</span>
					</td>
					<?php endif; ?>
					<td class="cellLabel">
						<span><?php echo textlength_check($this->_tpl_vars['_FIELD_VALUE']); ?>
</span>
					</td>
					<td class="cellLabel">
						<input type="hidden" name="row_counter" value="<?php echo $this->_tpl_vars['_COUNTER']; ?>
" />
						<select name="mapped_fields" class="txtBox" style="width: 100%" onchange="ImportJs.loadDefaultValueWidget('fieldIdentifier<?php echo $this->_tpl_vars['_COUNTER']; ?>
')">
							<option value=""><?php echo getTranslatedString('LBL_NONE', $this->_tpl_vars['FOR_MODULE']); ?>
</option>
							<?php $_from = $this->_tpl_vars['AVAILABLE_FIELDS']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['_FIELD_NAME'] => $this->_tpl_vars['_FIELD_INFO']):
?>
							<?php $this->assign('_TRANSLATED_FIELD_LABEL', getTranslatedString($this->_tpl_vars['_FIELD_INFO']->getFieldLabelKey(), $this->_tpl_vars['FOR_MODULE'])); ?>
							<option value="<?php echo $this->_tpl_vars['_FIELD_NAME']; ?>
" <?php if ($this->_tpl_vars['_HEADER_NAME'] == $this->_tpl_vars['_TRANSLATED_FIELD_LABEL']): ?> selected <?php endif; ?> >
								<?php echo $this->_tpl_vars['_TRANSLATED_FIELD_LABEL']; ?>

								<?php if ($this->_tpl_vars['_FIELD_INFO']->isMandatory() == 'true'): ?>&nbsp; (*)<?php endif; ?>
							</option>
							<?php endforeach; endif; unset($_from); ?>
						</select>
					</td>
					<td class="cellLabel" name="default_value_container">&nbsp;</td>
				</tr>
				<?php endforeach; endif; unset($_from); ?>
			</table>
		</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td align="right">
			<input type="checkbox" name="save_map" id="save_map" class="small" />
			<span class="small"><?php echo getTranslatedString('LBL_SAVE_AS_CUSTOM_MAPPING', $this->_tpl_vars['MODULE']); ?>
</span>&nbsp; : &nbsp;
			<input type="text" name="save_map_as" id="save_map_as" class="small" />
		</td>
		<td>&nbsp;</td>
	</tr>
</table>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "modules/Import/Import_Default_Values_Widget.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>