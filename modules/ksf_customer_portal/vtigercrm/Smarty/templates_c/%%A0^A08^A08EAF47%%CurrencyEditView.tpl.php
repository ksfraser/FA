<?php /* Smarty version 2.6.18, created on 2015-02-18 08:25:46
         compiled from CurrencyEditView.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'vtiger_imageurl', 'CurrencyEditView.tpl', 17, false),array('modifier', 'getTranslatedCurrencyString', 'CurrencyEditView.tpl', 52, false),)), $this); ?>
<script language="JAVASCRIPT" type="text/javascript" src="include/js/smoothscroll.js"></script>
<script language="JavaScript" type="text/javascript" src="include/js/menu.js"></script>
<br>
<table align="center" border="0" cellpadding="0" cellspacing="0" width="98%">
<tbody><tr>
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
			<!-- DISPLAY -->
			<table border=0 cellspacing=0 cellpadding=5 width=100% class="settingsSelUITopLine">
			<form action="index.php" method="post" name="index" id="form" onsubmit="VtigerJS_DialogBox.block();">
			<input type="hidden" name="module" value="Settings">
			<input type="hidden" name="parenttab" value="<?php echo $this->_tpl_vars['PARENTTAB']; ?>
">
			<input type="hidden" name="action" value="index">
			<input type="hidden" name="record" value="<?php echo $this->_tpl_vars['ID']; ?>
">
			<tr>
				<td width=50 rowspan=2 valign=top><img src="<?php echo vtiger_imageurl('currency.gif', $this->_tpl_vars['THEME']); ?>
" alt="<?php echo $this->_tpl_vars['MOD']['LBL_USERS']; ?>
" width="48" height="48" border=0 title="<?php echo $this->_tpl_vars['MOD']['LBL_USERS']; ?>
"></td>
				<td class="heading2" valign="bottom" ><b><a href="index.php?module=Settings&action=index&parenttab=Settings"><?php echo $this->_tpl_vars['MOD']['LBL_SETTINGS']; ?>
</a> > <a href="index.php?module=Settings&action=CurrencyListView&parenttab=Settings"><?php echo $this->_tpl_vars['MOD']['LBL_CURRENCY_SETTINGS']; ?>
</a> > 
				<?php if ($this->_tpl_vars['ID'] != ''): ?>
					<?php echo $this->_tpl_vars['MOD']['LBL_EDIT']; ?>
 &quot;<?php echo $this->_tpl_vars['CURRENCY_NAME']; ?>
&quot; 
				<?php else: ?>
					<?php echo $this->_tpl_vars['MOD']['LBL_NEW_CURRENCY']; ?>

				<?php endif; ?>
				</b></td>
			</tr>
			<tr>
				<td valign=top class="small"><?php echo $this->_tpl_vars['MOD']['LBL_CURRENCY_DESCRIPTION']; ?>
</td>
			</tr>
			</table>
				
				<br>
				<table border=0 cellspacing=0 cellpadding=10 width=100% >
				<tr>
				<td>
				
					<table border=0 cellspacing=0 cellpadding=5 width=100% class="tableHeading">
					<tr>
						<?php if ($this->_tpl_vars['ID'] != ''): ?>
							<td class="big"><strong><?php echo $this->_tpl_vars['MOD']['LBL_SETTINGS']; ?>
 <?php echo $this->_tpl_vars['APP']['LBL_FOR']; ?>
 &quot;<?php echo getTranslatedCurrencyString($this->_tpl_vars['CURRENCY_NAME']); ?>
&quot;  </strong></td>
						<?php else: ?>
							<td class="big"><strong>&quot;<?php echo $this->_tpl_vars['MOD']['LBL_NEW_CURRENCY']; ?>
&quot;  </strong></td>
						<?php endif; ?>
						<td class="small" align=right>
							<input title="<?php echo $this->_tpl_vars['APP']['LBL_SAVE_BUTTON_LABEL']; ?>
" accessKey="<?php echo $this->_tpl_vars['APP']['LBL_SAVE_BUTTON_KEY']; ?>
" class="crmButton small save" onclick="this.form.action.value='SaveCurrencyInfo'; return validate()" type="submit" name="button" value="<?php echo $this->_tpl_vars['APP']['LBL_SAVE_BUTTON_LABEL']; ?>
" >&nbsp;&nbsp;
							<div id="CurrencyEditLay"  class="layerPopup" style="display:none;width:25%;">
								<table width="100%" border="0" cellpadding="3" cellspacing="0" class="layerHeadingULine">
								<tr>
									<td class="layerPopupHeading"  align="left" width="60%"><?php echo $this->_tpl_vars['MOD']['LBL_TRANSFER_CURRENCY']; ?>
</td>
									<td align="right" width="40%"><img src="<?php echo vtiger_imageurl('close.gif', $this->_tpl_vars['THEME']); ?>
" border=0 alt="<?php echo $this->_tpl_vars['APP']['LBL_CLOSE']; ?>
" title="<?php echo $this->_tpl_vars['APP']['LBL_CLOSE']; ?>
" style="cursor:pointer;" onClick="document.getElementById('CurrencyEditLay').style.display='none'";></td>
								</tr>
								<table>
								<table border=0 cellspacing=0 cellpadding=5 width=95% align=center> 
									<tr>
										<td class=small >
											<table border=0 celspacing=0 cellpadding=5 width=100% align=center bgcolor=white>
												<tr>
													<td width="50%" class="cellLabel small"><b><?php echo $this->_tpl_vars['MOD']['LBL_CURRENT_CURRENCY']; ?>
</b></td>
													<td width="50%" class="cellText small"><b><?php echo getTranslatedCurrencyString($this->_tpl_vars['CURRENCY_NAME']); ?>
</b></td>
												</tr>
												<tr>
													<td class="cellLabel small"><b><?php echo $this->_tpl_vars['MOD']['LBL_TRANSCURR']; ?>
</b></td>
													<td class="cellText small">
														<select class="select small" name="transfer_currency_id" id="transfer_currency_id">';
														 <?php $_from = $this->_tpl_vars['OTHER_CURRENCIES']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['cur_id'] => $this->_tpl_vars['cur_name']):
?>
															 <option value="<?php echo $this->_tpl_vars['cur_id']; ?>
"><?php echo getTranslatedCurrencyString($this->_tpl_vars['cur_name']); ?>
</option>
														 <?php endforeach; endif; unset($_from); ?>
													</td>
												</tr>
											</table>
										</td>
									</tr>
								</table>
								<table border=0 cellspacing=0 cellpadding=5 width=100% class="layerPopupTransport">
									<tr>
										<td align="center"><input type="button" onclick="form.submit();" name="Update" value="<?php echo $this->_tpl_vars['APP']['LBL_SAVE_BUTTON_LABEL']; ?>
" class="crmbutton small save">
										</td>
									</tr>
								</table>
							</div>
							<input title="<?php echo $this->_tpl_vars['APP']['LBL_CANCEL_BUTTON_LABEL']; ?>
" accessKey="<?php echo $this->_tpl_vars['APP']['LBL_CANCEL_BUTTON_KEY']; ?>
" class="crmButton small cancel" onclick="window.history.back()" type="button" name="button" value="<?php echo $this->_tpl_vars['APP']['LBL_CANCEL_BUTTON_LABEL']; ?>
">
						</td>
					</tr>
					</table>
					
			<table border=0 cellspacing=0 cellpadding=0 width=100% class="listRow">
			<tr>
				<td class="small" valign=top >
				<table width="100%"  border="0" cellspacing="0" cellpadding="5">
				<tr>
					<td width="20%" nowrap class="small cellLabel"><font color="red">*</font><strong><?php echo $this->_tpl_vars['MOD']['LBL_CURRENCY_NAME']; ?>
</strong></td>
					<td width="80%" class="small cellText">
						<!-- input type="hidden" class="detailedViewTextBox small" value="" name="currency_name" -->
						<select name="currency_name" id="currency_name" class="small" onChange='updateSymbolAndCode();'>
					<?php $_from = $this->_tpl_vars['CURRENCIES']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['header'] => $this->_tpl_vars['currency']):
?>
			        	        <?php if ($this->_tpl_vars['header'] == $this->_tpl_vars['CURRENCY_NAME']): ?>
			        	        	<option value="<?php echo $this->_tpl_vars['header']; ?>
" selected><?php echo getTranslatedCurrencyString($this->_tpl_vars['header']); ?>
(<?php echo $this->_tpl_vars['currency']['1']; ?>
)</option>
			        	        <?php else: ?>
			        	        	<option value="<?php echo $this->_tpl_vars['header']; ?>
" ><?php echo getTranslatedCurrencyString($this->_tpl_vars['header']); ?>
(<?php echo $this->_tpl_vars['currency']['1']; ?>
)</option>
			        	        <?php endif; ?>
   					<?php endforeach; endif; unset($_from); ?>
 						</select>
 					</td>
				</tr>
				<tr valign="top">
					<td nowrap class="small cellLabel"><font color="red">*</font><strong><?php echo $this->_tpl_vars['MOD']['LBL_CURRENCY_CODE']; ?>
</strong></td>
 					<td class="small cellText"><input type="text" readonly class="detailedViewTextBox small" value="<?php echo $this->_tpl_vars['CURRENCY_CODE']; ?>
" name="currency_code" id="currency_code"></td>
				</tr>
				<tr valign="top">
					<td nowrap class="small cellLabel"><font color="red">*</font><strong><?php echo $this->_tpl_vars['MOD']['LBL_CURRENCY_SYMBOL']; ?>
</strong></td>
					<td class="small cellText"><input type="text" readonly class="detailedViewTextBox small" value="<?php echo $this->_tpl_vars['CURRENCY_SYMBOL']; ?>
" name="currency_symbol" id="currency_symbol"></td>
				</tr>
				<tr valign="top">
					<td nowrap class="small cellLabel"><font color="red">*</font><strong><?php echo $this->_tpl_vars['MOD']['LBL_CURRENCY_CRATE']; ?>
</strong><br>(<?php echo $this->_tpl_vars['MOD']['LBL_BASE_CURRENCY']; ?>
<?php echo getTranslatedCurrencyString($this->_tpl_vars['MASTER_CURRENCY']); ?>
)</td>
					<td class="small cellText"><input type="text" class="detailedViewTextBox small" value="<?php echo $this->_tpl_vars['CONVERSION_RATE']; ?>
" name="conversion_rate"></td>
				</tr>
				<tr>
					<td nowrap class="small cellLabel"><strong><?php echo $this->_tpl_vars['MOD']['LBL_CURRENCY_STATUS']; ?>
</strong></td>
					<td class="small cellText">
						<input type="hidden" value="<?php echo $this->_tpl_vars['CURRENCY_STATUS']; ?>
" id="old_currency_status" />
							<select name="currency_status" <?php echo $this->_tpl_vars['STATUS_DISABLE']; ?>
 class="importBox">
									<option value="Active"  <?php echo $this->_tpl_vars['ACTSELECT']; ?>
><?php echo $this->_tpl_vars['MOD']['LBL_ACTIVE']; ?>
</option>
				        	        <option value="Inactive" <?php echo $this->_tpl_vars['INACTSELECT']; ?>
><?php echo $this->_tpl_vars['MOD']['LBL_INACTIVE']; ?>
</option>
							</select>
					</td>
				</tr>	
                       </table>
						
						</td>
					  </tr>
					</table>
					<table border=0 cellspacing=0 cellpadding=5 width=100% >
					<tr>
					  <td class="small" nowrap align=right><a href="#top"><?php echo $this->_tpl_vars['MOD']['LBL_SCROLL']; ?>
</a></td>
					</tr>
					</table>
				</td>
				</tr>
				</table>
			
			
			
			</td>
			</tr>
			</table>
		</td>
	</tr>
	</form>
	</table>
		
	</div>
</td>
        <td valign="top"><img src="<?php echo vtiger_imageurl('showPanelTopRight.gif', $this->_tpl_vars['THEME']); ?>
"></td>
   </tr>
</tbody>
</table>
<?php echo '
<script>
        function validate() {
			if (!emptyCheck("currency_name","Currency Name","text")) return false
			if (!emptyCheck("currency_code","Currency Code","text")) return false
			if (!emptyCheck("currency_symbol","Currency Symbol","text")) return false
			if (!emptyCheck("conversion_rate","Conversion Rate","text")) return false
			if (!emptyCheck("currency_status","Currency Status","text")) return false
			if(isNaN(getObj("conversion_rate").value) || eval(getObj("conversion_rate").value) <= 0)
			{
'; ?>

            	alert("<?php echo $this->_tpl_vars['APP']['ENTER_VALID_CONVERSION_RATE']; ?>
")
                return false
<?php echo '
			}
			if (getObj("currency_status") != null && getObj("currency_status").value == "Inactive" 
					&& getObj("old_currency_status") != null && getObj("old_currency_status").value == "Active")
			{
				if (getObj("CurrencyEditLay") != null) getObj("CurrencyEditLay").style.display = "block";
				return false;
			} 
			else 
			{
				return true;
			}
        }
'; ?>

var currency_array = <?php echo $this->_tpl_vars['CURRENCIES_ARRAY']; ?>

<?php echo '
updateSymbolAndCode();
function updateSymbolAndCode(){
	selected_curr = document.getElementById(\'currency_name\').value;
	getObj(\'currency_code\').value = currency_array[selected_curr][0];
	getObj(\'currency_symbol\').value = currency_array[selected_curr][1];
}
</script>
'; ?>
