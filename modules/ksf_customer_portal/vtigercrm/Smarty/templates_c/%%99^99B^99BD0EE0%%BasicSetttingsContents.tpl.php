<?php /* Smarty version 2.6.18, created on 2015-02-18 04:16:56
         compiled from modules/CustomerPortal/BasicSetttingsContents.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'vtiger_imageurl', 'modules/CustomerPortal/BasicSetttingsContents.tpl', 33, false),array('modifier', 'getTranslatedString', 'modules/CustomerPortal/BasicSetttingsContents.tpl', 39, false),)), $this); ?>
<script language="JavaScript" type="text/javascript" src="modules/<?php echo $this->_tpl_vars['MODULE']; ?>
/<?php echo $this->_tpl_vars['MODULE']; ?>
.js"></script>

<script type="text/javascript">
	var moduleInfo = JSON.parse('<?php echo $this->_tpl_vars['PORTALMODULES']; ?>
');
	function initialModuleSettings(){
		renderModuleSettings(moduleInfo);
	}
	function visibleValue(sequence,tabid){
		visibleValueChange(sequence,tabid,moduleInfo);
	}
	function prefValue(sequence,tabid){
		prefValueChange(sequence,tabid,moduleInfo);
	}
	function moveModules(sequence,move){
		if(move == "Up")
			moveUp(moduleInfo,sequence);
		else
			moveDown(moduleInfo,sequence);
	}
	function renderModuleSettings(moduleInfo){
	
		var upImage = "<?php echo vtiger_imageurl('arrow_up.png', $this->_tpl_vars['THEME']); ?>
";
		var downImage = "<?php echo vtiger_imageurl('arrow_down.png', $this->_tpl_vars['THEME']); ?>
";
		var blankImage = "<?php echo vtiger_imageurl('blank.gif', $this->_tpl_vars['THEME']); ?>
";
		var displayData =
				'<table id="my_table" border=0 cellspacing=0 cellpadding=5 width="100%" class="dvtContentSpace" align="center">'+
				'<tr>'+
					'<td class="detailedViewHeader" colspan="4" ><b><?php echo getTranslatedString('LBL_MODULE_INFORMATION', $this->_tpl_vars['MODULE']); ?>
</b></td>'+
				'</tr>'+
				'<tr align="left">'+
					'<td class="colHeader small"><?php echo getTranslatedString('LBL_MODULE', $this->_tpl_vars['MODULE']); ?>
</td>'+
					'<td class="colHeader small"><?php echo getTranslatedString('LBL_SEQUENCE', $this->_tpl_vars['MODULE']); ?>
</td>'+
					'<td class="colHeader small"><?php echo getTranslatedString('LBL_VISIBLE', $this->_tpl_vars['MODULE']); ?>
</td>'+
					'<td class="colHeader small"><?php echo getTranslatedString('LBL_VIEW_ALL_RECORD', $this->_tpl_vars['MODULE']); ?>
</td>'+
				'</tr>';
			
		for(i=1;i<=moduleInfo.size();i++){
			var upImageTag = '<img src="'+upImage+'" style="width:16px;height:16px;" border="0"/>';
			var downImageTag = '<img src="'+downImage+'" style="width:16px;height:16px;" border="0"/>';
			var blankImageTag = '<img src="'+blankImage+'" style="width:16px;height:16px;" border="0"/>';
			
			if(moduleInfo[i].sequence == 1) {
				upImageTag = '';
			}
			else if(moduleInfo[i].sequence == moduleInfo.size()){
				downImageTag = '';
			}
			else {
				blankImageTag = '';
			}
			var visibleTag;
			if(moduleInfo[i].visible == 1){
				visibleTag = '<input type="checkbox" id="enable_disable_'+moduleInfo[i].name+'" onclick="javascript:visibleValue(\''+moduleInfo[i].sequence+'\',\''+moduleInfo[i].tabid+'\');" name="enable_disable_'+moduleInfo[i].name+'" checked>';
			}
			else{
				visibleTag = '<input type="checkbox" id="enable_disable_'+moduleInfo[i].name+'" onclick="javascript:visibleValue(\''+moduleInfo[i].sequence+'\',\''+moduleInfo[i].tabid+'\');" name="enable_disable_'+moduleInfo[i].name+'">';
			}
			//alert(upImageTag);
			var valueTag = '';
			if(moduleInfo[i].value == 1){
				valueTag = '<?php echo getTranslatedString('LBL_YES', $this->_tpl_vars['MODULE']); ?>
<input type="radio" name="view_'+moduleInfo[i].name+'" id="view_'+moduleInfo[i].name+'"  checked="checked" value="showall"> '+
							'<?php echo getTranslatedString('LBL_NO', $this->_tpl_vars['MODULE']); ?>
<input type="radio" name="view_'+moduleInfo[i].name+'" id="view_'+moduleInfo[i].name+'" onclick="javascript:prefValue(\''+moduleInfo[i].sequence+'\',\''+moduleInfo[i].tabid+'\');"  value="onlymine">';
			}
			else{
				valueTag = '<?php echo getTranslatedString('LBL_YES', $this->_tpl_vars['MODULE']); ?>
<input type="radio" name="view_'+moduleInfo[i].name+'" id="view_'+moduleInfo[i].name+'" onclick="javascript:prefValue(\''+moduleInfo[i].sequence+'\',\''+moduleInfo[i].tabid+'\');"  value="showall"> '+
							'<?php echo getTranslatedString('LBL_NO', $this->_tpl_vars['MODULE']); ?>
<input type="radio" name="view_'+moduleInfo[i].name+'" id="view_'+moduleInfo[i].name+'"  checked="checked" value="onlymine">';
			}
			displayData +=
				'<tr><td class="listTableRow small" width="35%">'+moduleInfo[i].name+'</td>' +
				'<input type="hidden" name="seq_'+moduleInfo[i].name+'" value="'+moduleInfo[i].sequence+'">' +
				'<td  align="center" class="listTableRow">' +
				'<a href="javascript:moveModules(\''+moduleInfo[i].sequence+'\',\'Up\');">' +
					upImageTag + '</a>' +
				blankImageTag +
				'<a href="javascript:moveModules(\''+moduleInfo[i].sequence+'\',\'Down\');">' +
					downImageTag + '</a>' +
				'</td>' +
				'<td class="listTableRow cellText small"  align="center">' +
					visibleTag +
				'</td>' +
				'<td class="listTableRow">' +
					valueTag +
				'</td>' +
				'</tr>';
		}
	displayData += '</table>';
	document.getElementById('displayData').innerHTML = displayData;
}
</script>

<table width="100%" border=0>
	<tr>
		<td width="55%">
			<div id="displayData" ></div>
		</td>
		<td valign="top">
			<table border=0 cellspacing=0 cellpadding=3 width="100%" align="center" class="dvtContentSpace">
				<tr>
					<td class="detailedViewHeader" colspan="4" ><b><?php echo getTranslatedString('LBL_USER_INFORMATION', $this->_tpl_vars['MODULE']); ?>
</b></td>
				</tr>
				<tr>
					<td  class="dvtCellLabel" align="right" width="40%"><?php echo getTranslatedString('LBL_SELECT_USERS', $this->_tpl_vars['MODULE']); ?>
</td>
					<td  class="dvtCellInfo" align="left">
						<select name="userid" class="small">
							<?php $_from = $this->_tpl_vars['USERS']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['user']):
?>
								<?php if ($this->_tpl_vars['USERID'] == $this->_tpl_vars['user']['id']): ?>
									<option value="<?php echo $this->_tpl_vars['user']['id']; ?>
" selected><?php echo $this->_tpl_vars['user']['name']; ?>
</option>
								<?php else: ?>
									<option value="<?php echo $this->_tpl_vars['user']['id']; ?>
"><?php echo $this->_tpl_vars['user']['name']; ?>
</option>
	<?php endif; ?>
							<?php endforeach; endif; unset($_from); ?>
						</select>
						<br><br>
						<span class="helpmessagebox" style="font-style: italic;"><?php echo getTranslatedString('LBL_USER_DESCRIPTION', $this->_tpl_vars['MODULE']); ?>
</span>
					</td>
				</tr>
				<tr>
					<td  class="dvtCellLabel" align="right"><?php echo getTranslatedString('LBL_DEFAULT_USERS', $this->_tpl_vars['MODULE']); ?>
</td>
					<td  class="dvtCellInfo" align="left">
						<select name="defaultAssignee" class="small">
							<optgroup style="border: none" label="Users" >
								<?php $_from = $this->_tpl_vars['USERS']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['user']):
?>
									<?php if ($this->_tpl_vars['DEFAULTASSIGNEE'] == $this->_tpl_vars['user']['id']): ?>
										<option value="<?php echo $this->_tpl_vars['user']['id']; ?>
" selected><?php echo $this->_tpl_vars['user']['name']; ?>
</option>
									<?php else: ?>
										<option value="<?php echo $this->_tpl_vars['user']['id']; ?>
"><?php echo $this->_tpl_vars['user']['name']; ?>
</option>
	<?php endif; ?>	
								<?php endforeach; endif; unset($_from); ?>
							</optgroup>
							<optgroup style="border: none" label="Groups">
								<?php $_from = $this->_tpl_vars['GROUPS']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['group']):
?>
									<?php if ($this->_tpl_vars['DEFAULTASSIGNEE'] == $this->_tpl_vars['group']['groupid']): ?>
										<option value="<?php echo $this->_tpl_vars['group']['groupid']; ?>
" selected><?php echo $this->_tpl_vars['group']['groupname']; ?>
</option>
		<?php else: ?>
										<option value="<?php echo $this->_tpl_vars['group']['groupid']; ?>
"><?php echo $this->_tpl_vars['group']['groupname']; ?>
</option>
		<?php endif; ?>
								<?php endforeach; endif; unset($_from); ?>
							</optgroup>
						</select>
						<br><br>
						<span class="helpmessagebox" style="font-style: italic;"><?php echo getTranslatedString('LBL_GROUP_DESCRIPTION', $this->_tpl_vars['MODULE']); ?>
</span>
					</td>
				</tr>
			</table>
	</td>
	</tr>
</table>	
<br><br>
		<center><input class="crmbutton small save" type="Submit" style="width:70px" title="<?php echo $this->_tpl_vars['APP']['LBL_SAVE_LABEL']; ?>
" value="<?php echo $this->_tpl_vars['APP']['LBL_SAVE_LABEL']; ?>
" alt="<?php echo $this->_tpl_vars['APP']['LBL_SAVE_LABEL']; ?>
" onclick=VtigerJS_DialogBox.block();></center>
		
<script>
	window.onload=function(){
		initialModuleSettings();
	}
</script>