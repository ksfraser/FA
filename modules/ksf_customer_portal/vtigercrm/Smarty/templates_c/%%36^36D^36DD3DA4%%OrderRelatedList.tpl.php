<?php /* Smarty version 2.6.18, created on 2015-02-17 04:33:04
         compiled from Settings/OrderRelatedList.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'vtiger_imageurl', 'Settings/OrderRelatedList.tpl', 23, false),)), $this); ?>
	
		<form action="index.php" method="post" name="form" onsubmit="VtigerJS_DialogBox.block();">
		<input type="hidden" name="fld_module" value="<?php echo $this->_tpl_vars['MODULE']; ?>
">
		<input type="hidden" name="module" value="Settings">
		<input type="hidden" name="parenttab" value="Settings">
		<?php $this->assign('entries', $this->_tpl_vars['CFENTRIES']); ?>
		<br>
		<br>
		<div style="display:block; position:relative; width:225px;" class="layerPopup">
			<table width="100%" border="0" cellpadding="3" cellspacing="0" class="layerHeadingULine" >
					<tr class="colHeader big">
						<td width="95%" colspan="2">
							<?php echo $this->_tpl_vars['MOD']['LBL_RELATED_LIST']; ?>

						</td>
						<td width="5%" align="right"><img src="<?php echo vtiger_imageurl('close.gif', $this->_tpl_vars['THEME']); ?>
" border="0"  align="absmiddle" onclick="fninvsh('relatedlistdiv');"/>
						</td>
					</tr>
					<?php $_from = $this->_tpl_vars['RELATEDLIST']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['relinfo'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['relinfo']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['related']):
        $this->_foreach['relinfo']['iteration']++;
?>
					<tr>
						<td><?php echo $this->_tpl_vars['related']['label']; ?>

						</td>
						<?php if (($this->_foreach['relinfo']['iteration'] <= 1)): ?>
						<td align="right" >
		 					<img src="<?php echo vtiger_imageurl('blank.gif', $this->_tpl_vars['THEME']); ?>
" style="width:16px;height:16px;" border="0" />&nbsp;&nbsp;
						</td>
						<td align="right" valign="middle">
			 				<img src="<?php echo vtiger_imageurl('arrow_down.png', $this->_tpl_vars['THEME']); ?>
" border="0" style="cursor:pointer;" onclick="changeRelatedListorder('move_down','<?php echo $this->_tpl_vars['related']['tabid']; ?>
','<?php echo $this->_tpl_vars['related']['sequence']; ?>
','<?php echo $this->_tpl_vars['related']['id']; ?>
','<?php echo $this->_tpl_vars['MODULE']; ?>
'); " alt="<?php echo $this->_tpl_vars['MOD']['DOWN']; ?>
" title="<?php echo $this->_tpl_vars['MOD']['DOWN']; ?>
">&nbsp;&nbsp;
						</td>

					<?php elseif (($this->_foreach['relinfo']['iteration'] == $this->_foreach['relinfo']['total'])): ?>
					<td align="right" valign="middle">
				 		<img src="<?php echo vtiger_imageurl('arrow_up.png', $this->_tpl_vars['THEME']); ?>
" border="0" style="cursor:pointer;" onclick="changeRelatedListorder('move_up','<?php echo $this->_tpl_vars['related']['tabid']; ?>
','<?php echo $this->_tpl_vars['related']['sequence']; ?>
','<?php echo $this->_tpl_vars['related']['id']; ?>
','<?php echo $this->_tpl_vars['MODULE']; ?>
'); " alt="<?php echo $this->_tpl_vars['MOD']['UP']; ?>
" title="<?php echo $this->_tpl_vars['MOD']['UP']; ?>
">&nbsp;&nbsp;
					</td>
					<td align="right" >
				 		<img src="<?php echo vtiger_imageurl('blank.gif', $this->_tpl_vars['THEME']); ?>
" style="width:16px;height:16px;" border="0" />&nbsp;&nbsp;
					</td>
					<?php else: ?>
					<td align="right" valign="middle">
				 	 	<img src="<?php echo vtiger_imageurl('arrow_up.png', $this->_tpl_vars['THEME']); ?>
" border="0" style="cursor:pointer;" onclick="changeRelatedListorder('move_up','<?php echo $this->_tpl_vars['related']['tabid']; ?>
','<?php echo $this->_tpl_vars['related']['sequence']; ?>
','<?php echo $this->_tpl_vars['related']['id']; ?>
','<?php echo $this->_tpl_vars['MODULE']; ?>
') " alt="<?php echo $this->_tpl_vars['MOD']['UP']; ?>
" title="<?php echo $this->_tpl_vars['MOD']['UP']; ?>
">&nbsp;&nbsp;
					</td>
					<td align="right" valign="middle">
				 	 	<img src="<?php echo vtiger_imageurl('arrow_down.png', $this->_tpl_vars['THEME']); ?>
" border="0" style="cursor:pointer;" onclick="changeRelatedListorder('move_down','<?php echo $this->_tpl_vars['related']['tabid']; ?>
','<?php echo $this->_tpl_vars['related']['sequence']; ?>
','<?php echo $this->_tpl_vars['related']['id']; ?>
','<?php echo $this->_tpl_vars['MODULE']; ?>
') " alt="<?php echo $this->_tpl_vars['MOD']['DOWN']; ?>
" title="<?php echo $this->_tpl_vars['MOD']['DOWN']; ?>
">&nbsp;&nbsp;
					</td>
					<?php endif; ?>
				</tr>
				<?php endforeach; endif; unset($_from); ?>		
			</table>		
		</div>
	</form>	