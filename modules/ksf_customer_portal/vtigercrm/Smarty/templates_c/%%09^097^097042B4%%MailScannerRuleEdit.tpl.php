<?php /* Smarty version 2.6.18, created on 2015-03-13 22:52:31
         compiled from MailScanner/MailScannerRuleEdit.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'vtiger_imageurl', 'MailScanner/MailScannerRuleEdit.tpl', 19, false),)), $this); ?>

<script language="JAVASCRIPT" type="text/javascript" src="include/js/smoothscroll.js"></script>

<br>
<table align="center" border="0" cellpadding="0" cellspacing="0" width="98%">
<tbody>
<tr>
	<td valign="top"><img src="<?php echo vtiger_imageurl('showPanelTopLeft.gif', $this->_tpl_vars['THEME']); ?>
"></td>
    <td class="showPanelBg" style="padding: 10px;" valign="top" width="100%">

	<form action="index.php" method="post" id="form" onsubmit="VtigerJS_DialogBox.block();">
		<input type='hidden' name='module' value='Settings'>
		<input type='hidden' name='action' value='MailScanner'>
		<input type='hidden' name='mode' value='rulesave'>
		<input type='hidden' name='ruleid' value="<?php echo $this->_tpl_vars['SCANNERRULE']->ruleid; ?>
">
		<input type='hidden' name='return_action' value='MailScanner'>
		<input type='hidden' name='return_module' value='Settings'>
		<input type='hidden' name='parenttab' value='Settings'>

        <br>

		<div align=center>
			<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'SetMenu.tpl', 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
				<!-- DISPLAY -->
				<table border=0 cellspacing=0 cellpadding=5 width=100% class="settingsSelUITopLine">
				<tr>
					<td width=50 rowspan=2 valign=top><img src="<?php echo vtiger_imageurl('mailScanner.gif', $this->_tpl_vars['THEME']); ?>
" alt="<?php echo $this->_tpl_vars['MOD']['LBL_MAIL_SCANNER']; ?>
" width="48" height="48" border=0 title="<?php echo $this->_tpl_vars['MOD']['LBL_MAIL_SCANNER']; ?>
"></td>
					<td class=heading2 valign=bottom><b><a href="index.php?module=Settings&action=index&parenttab=Settings"><?php echo $this->_tpl_vars['MOD']['LBL_SETTINGS']; ?>
</a> > <?php echo $this->_tpl_vars['MOD']['LBL_MAIL_SCANNER']; ?>
</b></td>
				</tr>
				<tr>
					<td valign=top class="small"><?php echo $this->_tpl_vars['MOD']['LBL_MAIL_SCANNER_DESCRIPTION']; ?>
</td>
				</tr>
				</table>
				
				<br>
				<table border=0 cellspacing=0 cellpadding=10 width=100% >
				<tr>
				<td>
				
				<table border=0 cellspacing=0 cellpadding=5 width=100% class="tableHeading">
				<tr>
				<td class="big" width="70%"><strong><?php echo $this->_tpl_vars['MOD']['LBL_MAIL_SCANNER']; ?>
 <?php echo $this->_tpl_vars['MOD']['LBL_RULE']; ?>
 <?php echo $this->_tpl_vars['MOD']['LBL_INFORMATION']; ?>
</strong></td>
				</tr>
				</table>
				
				<table border=0 cellspacing=0 cellpadding=0 width=100% class="listRow">
				<tr>
	         	    <td class="small" valign=top ><table width="100%"  border="0" cellspacing="0" cellpadding="5">
						<tr>
                            <td width="20%" nowrap class="small cellLabel"><strong><?php echo $this->_tpl_vars['MOD']['LBL_SCANNER']; ?>
 <?php echo $this->_tpl_vars['MOD']['LBL_NAME']; ?>
</strong></td>
                            <td width="80%" colspan=2><?php echo $this->_tpl_vars['SCANNERINFO']['scannername']; ?>

								<input type="hidden" name="scannername" class="small" value="<?php echo $this->_tpl_vars['SCANNERINFO']['scannername']; ?>
" size=50 readonly></td>
                        </tr>
                        <tr>
                            <td width="20%" nowrap class="small cellLabel"><strong><?php echo $this->_tpl_vars['MOD']['LBL_FROM']; ?>
</strong></td>
                            <td width="80%" colspan=2><input type="text" name="rule_from" class="small" value="<?php echo $this->_tpl_vars['SCANNERRULE']->fromaddress; ?>
" size=50></td>
                        </tr>
                        <tr>
                            <td width="20%" nowrap class="small cellLabel"><strong><?php echo $this->_tpl_vars['MOD']['LBL_TO']; ?>
</strong></td>
                            <td width="80%" colspan=2><input type="text" name="rule_to" class="small" value="<?php echo $this->_tpl_vars['SCANNERRULE']->toaddress; ?>
" size=50></td>
                        </tr>
						<tr>
                            <td width="20%" nowrap class="small cellLabel"><strong><?php echo $this->_tpl_vars['MOD']['LBL_SUBJECT']; ?>
</strong></td>
                            <td width="10%">
								<select name="rule_subjectop" class="small">
									<option value=''>-- <?php echo $this->_tpl_vars['MOD']['LBL_SELECT']; ?>
 <?php echo $this->_tpl_vars['MOD']['LBL_CONDITION']; ?>
 --</option>
									<option value='Contains'    <?php if ($this->_tpl_vars['SCANNERRULE']->subjectop == 'Contains'): ?>selected=true<?php endif; ?>
									><?php echo $this->_tpl_vars['MOD']['LBL_CONTAINS']; ?>
</option>
									<option value='Not Contains' <?php if ($this->_tpl_vars['SCANNERRULE']->subjectop == 'Not Contains'): ?>selected=true<?php endif; ?>
									><?php echo $this->_tpl_vars['MOD']['LBL_NOT']; ?>
 <?php echo $this->_tpl_vars['MOD']['LBL_CONTAINS']; ?>
</option>
									<option value='Equals'      <?php if ($this->_tpl_vars['SCANNERRULE']->subjectop == 'Equals'): ?>selected=true<?php endif; ?>
									><?php echo $this->_tpl_vars['MOD']['LBL_EQUALS']; ?>
</option>
									<option value='Not Equals'  <?php if ($this->_tpl_vars['SCANNERRULE']->subjectop == 'Not Equals'): ?>selected=true<?php endif; ?>
									><?php echo $this->_tpl_vars['MOD']['LBL_NOT']; ?>
 <?php echo $this->_tpl_vars['MOD']['LBL_EQUALS']; ?>
</option>
									<option value='Begins With' <?php if ($this->_tpl_vars['SCANNERRULE']->subjectop == 'Begins With'): ?>selected=true<?php endif; ?>
									><?php echo $this->_tpl_vars['MOD']['LBL_BEGINS']; ?>
 <?php echo $this->_tpl_vars['MOD']['LBL_WITH']; ?>
</option>
									<option value='Ends With'   <?php if ($this->_tpl_vars['SCANNERRULE']->subjectop == 'Ends With'): ?>selected=true<?php endif; ?>
									><?php echo $this->_tpl_vars['MOD']['LBL_ENDS']; ?>
 <?php echo $this->_tpl_vars['MOD']['LBL_WITH']; ?>
</option>
									<option value='Regex'       <?php if ($this->_tpl_vars['SCANNERRULE']->subjectop == 'Regex'): ?>selected=true<?php endif; ?>
									><?php echo $this->_tpl_vars['MOD']['LBL_REGEX']; ?>
</option> 
								</select>
							</td>
							<td width="70%">
								<input type="text" name="rule_subject" class="small" value="<?php echo $this->_tpl_vars['SCANNERRULE']->subject; ?>
" size="65"/>
							</td>
                        </tr>
						<tr>
                            <td width="20%" nowrap class="small cellLabel"><strong><?php echo $this->_tpl_vars['MOD']['LBL_BODY']; ?>
</strong></td>
                            <td width="10%">
								<select name="rule_bodyop" class="small">
									<option value=''>-- <?php echo $this->_tpl_vars['MOD']['LBL_SELECT']; ?>
 <?php echo $this->_tpl_vars['MOD']['LBL_CONDITION']; ?>
 --</option>
									<option value='Contains'    <?php if ($this->_tpl_vars['SCANNERRULE']->bodyop == 'Contains'): ?>selected=true<?php endif; ?>
									><?php echo $this->_tpl_vars['MOD']['LBL_CONTAINS']; ?>
</option>
									<option value='Not Contains' <?php if ($this->_tpl_vars['SCANNERRULE']->subjectop == 'Not Contains'): ?>selected=true<?php endif; ?>
									><?php echo $this->_tpl_vars['MOD']['LBL_NOT']; ?>
 <?php echo $this->_tpl_vars['MOD']['LBL_CONTAINS']; ?>
</option>
									<option value='Equals'      <?php if ($this->_tpl_vars['SCANNERRULE']->bodyop == 'Equals'): ?>selected=true<?php endif; ?>
									><?php echo $this->_tpl_vars['MOD']['LBL_EQUALS']; ?>
</option>
									<option value='Not Equals'  <?php if ($this->_tpl_vars['SCANNERRULE']->bodyop == 'Not Equals'): ?>selected=true<?php endif; ?>
									><?php echo $this->_tpl_vars['MOD']['LBL_NOT']; ?>
 <?php echo $this->_tpl_vars['MOD']['LBL_EQUALS']; ?>
</option>
									<option value='Begins With' <?php if ($this->_tpl_vars['SCANNERRULE']->bodyop == 'Begins With'): ?>selected=true<?php endif; ?>
									><?php echo $this->_tpl_vars['MOD']['LBL_BEGINS']; ?>
 <?php echo $this->_tpl_vars['MOD']['LBL_WITH']; ?>
</option>
									<option value='Ends With'   <?php if ($this->_tpl_vars['SCANNERRULE']->bodyop == 'Ends With'): ?>selected=true<?php endif; ?>
									><?php echo $this->_tpl_vars['MOD']['LBL_ENDS']; ?>
 <?php echo $this->_tpl_vars['MOD']['LBL_WITH']; ?>
</option>
																										</select>
							</td>
							<td width="70%">
								<textarea name="rule_body" class="small"><?php echo $this->_tpl_vars['SCANNERRULE']->body; ?>
</textarea> 
							</td>
                        </tr>
						<tr>
                            <td width="20%" nowrap class="small cellLabel"><strong><?php echo $this->_tpl_vars['MOD']['LBL_MATCH']; ?>
</strong></td>
                            <td width="70%" colspan=2>
								<?php if ($this->_tpl_vars['SCANNERRULE']->matchusing == 'OR'): ?>
									<?php $this->assign('rule_match_or', "checked='true'"); ?>
									<?php $this->assign('rule_match_all', ""); ?>
								<?php else: ?>
									<?php $this->assign('rule_match_or', ""); ?>
									<?php $this->assign('rule_match_all', "checked='true'"); ?>
								<?php endif; ?>
								<input type="radio" class="small" name="rule_matchusing" value="AND" <?php echo $this->_tpl_vars['rule_match_all']; ?>
> <?php echo $this->_tpl_vars['MOD']['LBL_ALL']; ?>
 <?php echo $this->_tpl_vars['MOD']['LBL_CONDITION']; ?>

								<input type="radio" class="small" name="rule_matchusing" value="OR" <?php echo $this->_tpl_vars['rule_match_or']; ?>
> <?php echo $this->_tpl_vars['MOD']['LBL_ANY']; ?>
 <?php echo $this->_tpl_vars['MOD']['LBL_CONDITION']; ?>

							</td>
                        </tr>
						<tr>
                            <td width="20%" nowrap class="small cellLabel"><strong><?php echo $this->_tpl_vars['MOD']['LBL_ACTION']; ?>
</strong></td>
                            <td width="70%" colspan=2>
								<?php $this->assign('RULEACTIONTEXT', ""); ?>
								<?php if ($this->_tpl_vars['SCANNERRULE']->useaction): ?>
									<?php $this->assign('RULEACTIONTEXT', $this->_tpl_vars['SCANNERRULE']->useaction->actiontext); ?>
									<input type="hidden" class="small" name="actionid" value="<?php echo $this->_tpl_vars['SCANNERRULE']->useaction->actionid; ?>
">
								<?php else: ?>
									<input type="hidden" class="small" name="actionid" value="">
								<?php endif; ?>

								<select name="rule_actiontext" class="small">
																		<option value="CREATE,HelpDesk,FROM" <?php if ($this->_tpl_vars['RULEACTIONTEXT'] == 'CREATE,HelpDesk,FROM'): ?>selected=true<?php endif; ?>
									><?php echo $this->_tpl_vars['MOD']['LBL_CREATE']; ?>
 <?php echo $this->_tpl_vars['MOD']['LBL_TICKET']; ?>
</option>
									<option value="UPDATE,HelpDesk,SUBJECT" <?php if ($this->_tpl_vars['RULEACTIONTEXT'] == 'UPDATE,HelpDesk,SUBJECT'): ?>selected=true<?php endif; ?>
									><?php echo $this->_tpl_vars['MOD']['LBL_UPDATE']; ?>
 <?php echo $this->_tpl_vars['MOD']['LBL_TICKET']; ?>
</option>
									<option value="LINK,Contacts,FROM" <?php if ($this->_tpl_vars['RULEACTIONTEXT'] == 'LINK,Contacts,FROM'): ?>selected=true<?php endif; ?>
									><?php echo $this->_tpl_vars['MOD']['LBL_ADD']; ?>
 <?php echo $this->_tpl_vars['MOD']['LBL_TO_SMALL']; ?>
 <?php echo $this->_tpl_vars['MOD']['LBL_CONTACT']; ?>
 [<?php echo $this->_tpl_vars['MOD']['LBL_FROM_CAPS']; ?>
]</option>
									<option value="LINK,Contacts,TO" <?php if ($this->_tpl_vars['RULEACTIONTEXT'] == 'LINK,Contacts,TO'): ?>selected=true<?php endif; ?>
									><?php echo $this->_tpl_vars['MOD']['LBL_ADD']; ?>
 <?php echo $this->_tpl_vars['MOD']['LBL_TO_SMALL']; ?>
 <?php echo $this->_tpl_vars['MOD']['LBL_CONTACT']; ?>
 [<?php echo $this->_tpl_vars['MOD']['LBL_TO_CAPS']; ?>
]</option>
									<option value="LINK,Accounts,FROM" <?php if ($this->_tpl_vars['RULEACTIONTEXT'] == 'LINK,Accounts,FROM'): ?>selected=true<?php endif; ?>
									><?php echo $this->_tpl_vars['MOD']['LBL_ADD']; ?>
 <?php echo $this->_tpl_vars['MOD']['LBL_TO_SMALL']; ?>
 <?php echo $this->_tpl_vars['MOD']['LBL_ACCOUNT']; ?>
 [<?php echo $this->_tpl_vars['MOD']['LBL_FROM_CAPS']; ?>
]</option>
									<option value="LINK,Accounts,TO" <?php if ($this->_tpl_vars['RULEACTIONTEXT'] == 'LINK,Accounts,TO'): ?>selected=true<?php endif; ?>
									><?php echo $this->_tpl_vars['MOD']['LBL_ADD']; ?>
 <?php echo $this->_tpl_vars['MOD']['LBL_TO_SMALL']; ?>
 <?php echo $this->_tpl_vars['MOD']['LBL_ACCOUNT']; ?>
 [<?php echo $this->_tpl_vars['MOD']['LBL_TO_CAPS']; ?>
]</option>
								</select>
							</td>
                        </tr>
				    </td>
            	</tr>
				<tr>
					<td colspan=3 nowrap align="center">
						<input type="submit" class="crmbutton small save" value="<?php echo $this->_tpl_vars['APP']['LBL_SAVE_LABEL']; ?>
" />
						<input type="button" class="crmbutton small cancel" value="<?php echo $this->_tpl_vars['APP']['LBL_CANCEL_BUTTON_LABEL']; ?>
" 
							onclick="location.href='index.php?module=Settings&action=MailScanner&parenttab=Settings&mode=rule&scannername=<?php echo $this->_tpl_vars['SCANNERINFO']['scannername']; ?>
'"/>
					</td>
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
	</table>
		
	</div>

</td>
        <td valign="top"><img src="<?php echo vtiger_imageurl('showPanelTopRight.gif', $this->_tpl_vars['THEME']); ?>
"></td>
   </tr>
</tbody>
</form>
</table>

</tr>
</table>

</tr>
</table>