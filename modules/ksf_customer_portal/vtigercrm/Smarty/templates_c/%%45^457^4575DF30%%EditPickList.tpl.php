<?php /* Smarty version 2.6.18, created on 2015-02-18 01:22:41
         compiled from modules/PickList/EditPickList.tpl */ ?>
<div style="position:relative; display:block; padding:10px" class="layerPopup">
	<table border="0" cellpadding="5" cellspacing="0" class="layerHeadingULine">
		<tr>
			<td class="layerPopupHeading" align="left" nowrap>
				<?php echo $this->_tpl_vars['MOD']['EDIT_PICKLIST_VALUE']; ?>
 - <?php echo $this->_tpl_vars['FIELDLABEL']; ?>

			</td>
		</tr>
	</table>

	<table border=0 cellspacing=0 cellpadding=5>
		<tr>
		<td valign=top align=left>
			<b><?php echo $this->_tpl_vars['MOD']['LBL_SELECT_TO_EDIT']; ?>
</b>
			<br>
			<select id="edit_availPickList" name="availList" size="10" style="width:250px;border:1px solid #666666;font-family:Arial, Helvetica, sans-serif;font-size:11px;" onchange="selectForEdit();">
				<?php $_from = $this->_tpl_vars['PICKVAL']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['pick_key'] => $this->_tpl_vars['pick_val']):
?>
					<option value="<?php echo $this->_tpl_vars['pick_key']; ?>
"><?php echo $this->_tpl_vars['pick_val']; ?>
</option>
				<?php endforeach; endif; unset($_from); ?>
			</select>

			<?php if (is_array ( $this->_tpl_vars['NONEDITPICKLIST'] )): ?>
			<table border=0 cellspacing=0 cellpadding=0 width=100%>
				<tr><td><b><?php echo $this->_tpl_vars['MOD']['LBL_NON_EDITABLE_PICKLIST_ENTRIES']; ?>
 :</b></td></tr>
				<tr><td><b>
					<div id="nonedit_pl_values">
						<?php $_from = $this->_tpl_vars['NONEDITPICKLIST']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['nonedit']):
?>
							<span class="nonEditablePicklistValues">
								<?php echo $this->_tpl_vars['nonedit']; ?>

							</span><br>
						<?php endforeach; endif; unset($_from); ?>
					</div>
				</b></td></tr>
			</table>
			<?php endif; ?>
		</td>
		</tr>
		<tr>
			<td>
				<b><?php echo $this->_tpl_vars['MOD']['LBL_EDIT_HERE']; ?>
</b>&nbsp;
				<input type="text" id="replaceVal" class="small" style="width: 60%" onchange="pushEditedValue(event)"/>
			</td>
		</tr>
		<tr>
			<td valign=top>
				<input type="button" value="<?php echo $this->_tpl_vars['APP']['LBL_APPLY_BUTTON_LABEL']; ?>
" name="apply" class="crmButton small edit" onclick="validateEdit('<?php echo $this->_tpl_vars['FIELDNAME']; ?>
','<?php echo $this->_tpl_vars['MODULE']; ?>
');">
				<input type="button" value="<?php echo $this->_tpl_vars['APP']['LBL_CANCEL_BUTTON_LABEL']; ?>
" name="cancel" class="crmButton small cancel" onclick="fnhide('actiondiv');">
			</td>
		</tr>
	</table>
</div>