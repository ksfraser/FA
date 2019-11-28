<?php /* Smarty version 2.6.18, created on 2015-03-17 18:50:32
         compiled from modules/Import/ListViewEntries.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'getTranslatedString', 'modules/Import/ListViewEntries.tpl', 18, false),array('modifier', 'vtiger_imageurl', 'modules/Import/ListViewEntries.tpl', 19, false),array('modifier', 'strip_tags', 'modules/Import/ListViewEntries.tpl', 39, false),)), $this); ?>
<table width="100%" class="layerPopupTransport" cellpadding="5">
	<tr>
		<td align="left" class="small">
			<?php echo $this->_tpl_vars['recordListRange']; ?>

		</td>
		<td align="right" class="small">
			<a href="javascript:;" onClick="ImportJs.loadListViewPage('<?php echo $this->_tpl_vars['FOR_MODULE']; ?>
', 1, '<?php echo $this->_tpl_vars['FOR_USER']; ?>
');" title="<?php echo getTranslatedString('LBL_FIRST', $this->_tpl_vars['FOR_MODULE']); ?>
">
				<img src="<?php echo vtiger_imageurl('start.gif', $this->_tpl_vars['THEME']); ?>
" border="0" align="absmiddle" alt="<?php echo getTranslatedString('LBL_FIRST', $this->_tpl_vars['FOR_MODULE']); ?>
">
			</a>
			<a href="javascript:;" onClick="ImportJs.loadListViewPage('<?php echo $this->_tpl_vars['FOR_MODULE']; ?>
', <?php echo $this->_tpl_vars['CURRENT_PAGE']; ?>
-1, '<?php echo $this->_tpl_vars['FOR_USER']; ?>
');" title="<?php echo getTranslatedString('LNK_LIST_PREVIOUS', $this->_tpl_vars['FOR_MODULE']); ?>
">
				<img src="<?php echo vtiger_imageurl('previous.gif', $this->_tpl_vars['THEME']); ?>
" border="0" align="absmiddle" alt="<?php echo getTranslatedString('LNK_LIST_PREVIOUS', $this->_tpl_vars['FOR_MODULE']); ?>
">
			</a>
			<input class="small" id="page_num" type="text" value="<?php echo $this->_tpl_vars['CURRENT_PAGE']; ?>
" style="width: 3em;margin-right: 0.7em;"
				   onchange="ImportJs.loadListViewSelectedPage('<?php echo $this->_tpl_vars['FOR_MODULE']; ?>
', '<?php echo $this->_tpl_vars['FOR_USER']; ?>
');"
				   onkeypress="return VT_disableFormSubmit(event);" />
			<a href="javascript:;" onClick="ImportJs.loadListViewPage('<?php echo $this->_tpl_vars['FOR_MODULE']; ?>
', <?php echo $this->_tpl_vars['CURRENT_PAGE']; ?>
+1, '<?php echo $this->_tpl_vars['FOR_USER']; ?>
');" title="<?php echo getTranslatedString('LNK_LIST_NEXT', $this->_tpl_vars['FOR_MODULE']); ?>
">
				<img src="<?php echo vtiger_imageurl('next.gif', $this->_tpl_vars['THEME']); ?>
" border="0" align="absmiddle" alt="<?php echo getTranslatedString('LNK_LIST_NEXT', $this->_tpl_vars['FOR_MODULE']); ?>
">
			</a>
			<a href="javascript:;" onClick="ImportJs.loadListViewPage('<?php echo $this->_tpl_vars['FOR_MODULE']; ?>
', 'last', '<?php echo $this->_tpl_vars['FOR_USER']; ?>
');" title="<?php echo getTranslatedString('LBL_LAST', $this->_tpl_vars['FOR_MODULE']); ?>
">
				<img src="<?php echo vtiger_imageurl('end.gif', $this->_tpl_vars['THEME']); ?>
" border="0" align="absmiddle" alt="<?php echo getTranslatedString('LBL_LAST', $this->_tpl_vars['FOR_MODULE']); ?>
">
			</a>
		</td>
	</tr>
</table>
<table border=0 cellspacing=1 cellpadding=3 width=100% class="lvt small">
	<tr>
		<?php $_from = $this->_tpl_vars['LISTHEADER']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['listviewforeach'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['listviewforeach']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['header']):
        $this->_foreach['listviewforeach']['iteration']++;
?>
		<td class="lvtCol"><?php echo smarty_modifier_strip_tags($this->_tpl_vars['header']); ?>
</td>
		<?php endforeach; endif; unset($_from); ?>
	</tr>
	<?php $_from = $this->_tpl_vars['LISTENTITY']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['entity_id'] => $this->_tpl_vars['entity']):
?>
		<tr bgcolor=white onMouseOver="this.className='lvtColDataHover'" onMouseOut="this.className='lvtColData'" id="row_<?php echo $this->_tpl_vars['entity_id']; ?>
">
			<?php $_from = $this->_tpl_vars['entity']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['data']):
?>
						<td onmouseover="vtlib_listview.trigger('cell.onmouseover', $(this))" onmouseout="vtlib_listview.trigger('cell.onmouseout', $(this))"><?php echo $this->_tpl_vars['data']; ?>
</td>
				        <?php endforeach; endif; unset($_from); ?>
		</tr>
	<?php endforeach; else: ?>
		<tr>
			<td style="background-color:#efefef;height:340px" align="center" colspan="<?php echo $this->_foreach['listviewforeach']['iteration']+1; ?>
">
				<div style="border: 3px solid rgb(153, 153, 153); background-color: rgb(255, 255, 255); width: 45%; position: relative; z-index: 10000000;">
					<table border="0" cellpadding="5" cellspacing="0" width="98%">
						<tr>
							<td rowspan="2" width="25%"><img src="<?php echo vtiger_imageurl('empty.jpg', $this->_tpl_vars['THEME']); ?>
" height="60" width="61"></td>
							<td style="border-bottom: 1px solid rgb(204, 204, 204);" nowrap="nowrap" width="75%">
								<span class="genHeaderSmall">
								<?php echo getTranslatedString('LBL_NO', $this->_tpl_vars['FOR_MODULE']); ?>
 <?php echo getTranslatedString($this->_tpl_vars['FOR_MODULE'], $this->_tpl_vars['FOR_MODULE']); ?>
 <?php echo getTranslatedString('LBL_FOUND', $this->_tpl_vars['FOR_MODULE']); ?>
 !
								</span>
							</td>
						</tr>
					</table>
				</div>
			</td>
		</tr>
	<?php endif; unset($_from); ?>
</table>