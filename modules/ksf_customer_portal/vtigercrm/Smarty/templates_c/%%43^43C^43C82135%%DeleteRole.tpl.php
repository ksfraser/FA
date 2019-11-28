<?php /* Smarty version 2.6.18, created on 2015-02-14 03:25:53
         compiled from DeleteRole.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'vtiger_imageurl', 'DeleteRole.tpl', 22, false),)), $this); ?>
<script language="JavaScript" type="text/javascript">
function openPopup(del_roleid){
                window.open("index.php?module=Users&action=UsersAjax&file=RolePopup&maskid="+del_roleid+"&parenttab=Settings","roles_popup_window","height=425,width=640,toolbar=no,menubar=no,dependent=yes,resizable =no");
        }
</script>
<br>
<table align="center" border="0" cellpadding="0" cellspacing="0" width="98%">
<tr>
    <td valign="top"><img src="<?php echo vtiger_imageurl('showPanelTopLeft.gif', $this->_tpl_vars['THEME']); ?>
"></td>
    <td class="showPanelBg" style="padding: 10px;" valign="top" width="100%">
    <br>

    <div align=center>

	<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'SetMenu.tpl', 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<?php echo '
<form name="newProfileForm" action="index.php" onsubmit="if(roleDeleteValidate()) { VtigerJS_DialogBox.block();} else { return false; }">
'; ?>

<input type="hidden" name="module" value="Users">
<input type="hidden" name="action" value="DeleteRole">
<input type="hidden" name="delete_role_id" value="<?php echo $this->_tpl_vars['ROLEID']; ?>
">	
<table width="100%" border="0" cellpadding="3" cellspacing="0">
<tr>
	<td class="genHeaderSmall" align="left" style="border-bottom:1px solid #CCCCCC;" width="50%"><?php echo $this->_tpl_vars['CMOD']['LBL_DELETE_ROLE']; ?>
</td>
	<td style="border-bottom:1px solid #CCCCCC;">&nbsp;</td>
	<td align="right" style="border-bottom:1px solid #CCCCCC;" width="40%"><a href="#" onClick="window.history.back();"><?php echo $this->_tpl_vars['APP']['LBL_BACK']; ?>
</a></td>
</tr>
<tr>
	<td colspan="3">&nbsp;</td>
</tr>
<tr>
	<td width="50%"><b><?php echo $this->_tpl_vars['CMOD']['LBL_ROLE_TO_BE_DELETED']; ?>
</b></td>
	<td width="2%"><b>:</b></td>
	<td width="48%"><b><?php echo $this->_tpl_vars['ROLENAME']; ?>
</b></td>
</tr>
<tr>
	<td style="text-align:left;"><b><?php echo $this->_tpl_vars['CMOD']['LBL_TRANSFER_USER_ROLE']; ?>
</b></td>
	<td ><b>:</b></td>
	<td align="left">
	<input type="text" name="role_name"  id="role_name" value="" class="txtBox" readonly="readonly">&nbsp;
        	<?php echo $this->_tpl_vars['ROLEPOPUPBUTTON']; ?>

        <input type="hidden" name="user_role" id="user_role" value="">	
           
	</td>
</tr>
<tr><td colspan="3" style="border-bottom:1px dashed #CCCCCC;">&nbsp;</td></tr>
<tr>
    <td colspan="3" align="center"><input type="submit" name="Delete" value="<?php echo $this->_tpl_vars['APP']['LBL_SAVE_BUTTON_LABEL']; ?>
" class="crmbutton small save">
	</td>
</tr>
</table>
</form></div>
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
<td valign="top"><img src="<?php echo vtiger_imageurl('showPanelTopRight.gif', $this->_tpl_vars['THEME']); ?>
"></td>
</tr>
</table>
<br>
<script>
<?php echo '
function roleDeleteValidate()
{
	if(document.getElementById(\'role_name\').value == \'\')
	{
		'; ?>

                alert('<?php echo $this->_tpl_vars['APP']['SPECIFY_ROLE_INFO']; ?>
');
                return false;
                <?php echo '
	}
	return true;
}
'; ?>

</script>
