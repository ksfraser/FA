<?php /* Smarty version 2.6.18, created on 2015-03-13 22:52:19
         compiled from MailScanner/MailScannerRule.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'vtiger_imageurl', 'MailScanner/MailScannerRule.tpl', 19, false),)), $this); ?>

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
		<input type='hidden' name='mode' value='ruleedit'>
		<input type='hidden' name='scannername' value='<?php echo $this->_tpl_vars['SCANNERINFO']['scannername']; ?>
'>
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
				<td class="big" width="70%"><strong><?php echo $this->_tpl_vars['MOD']['LBL_RULES']; ?>
 <?php echo $this->_tpl_vars['MOD']['LBL_FOR']; ?>
 <?php echo $this->_tpl_vars['MOD']['LBL_MAIL_SCANNER']; ?>
 [<?php echo $this->_tpl_vars['SCANNERINFO']['scannername']; ?>
]</strong></td>
				<td width="30%" nowrap align="right">
					<input type="button" class="crmbutton small cancel" value="<?php echo $this->_tpl_vars['APP']['LBL_BACK']; ?>
" 
						onclick="location.href='index.php?module=Settings&action=MailScanner&parenttab=Settings'" />
					<input type="submit" class="crmbutton small create" onclick="this.form.mode.value='ruleedit'" value="<?php echo $this->_tpl_vars['APP']['LBL_ADD_NEW']; ?>
 <?php echo $this->_tpl_vars['MOD']['LBL_RULE']; ?>
" />
				</td>
				</tr>
				</table>
				
				<table border=0 cellspacing=0 cellpadding=0 width=100% class="listRow">
				<tr>
	         	    <td class="small" valign=top ><table width="100%"  border="0" cellspacing="0" cellpadding="5">

						<?php $this->assign('PREV_RULEID', ""); ?>
						<?php $_from = $this->_tpl_vars['SCANNERRULES']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['RULEINDEX'] => $this->_tpl_vars['SCANNERRULE']):
?>
							<?php $this->assign('NEXT_RULEID', ""); ?>
							<?php if ($this->_tpl_vars['RULEINDEX'] != ( count ( $this->_tpl_vars['SCANNERRULES'] ) -1 )): ?>
								<?php $this->assign('RULEINDEX1', $this->_tpl_vars['RULEINDEX']+1); ?>
								<?php $this->assign('NEXT_RULEID', $this->_tpl_vars['SCANNERRULES'][$this->_tpl_vars['RULEINDEX1']]->ruleid); ?>
							<?php endif; ?>
						<tr>
							<td nowrap class="small cellLabel">
								<strong><?php echo $this->_tpl_vars['MOD']['LBL_PRIORITY']; ?>
</strong>
								<span style='margin-left: 100px;'>
								<?php if ($this->_tpl_vars['NEXT_RULEID']): ?>
<a href="index.php?module=Settings&action=MailScanner&parenttabl=Settings&mode=rulemove_down&scannername=<?php echo $this->_tpl_vars['SCANNERINFO']['scannername']; ?>
&targetruleid=<?php echo $this->_tpl_vars['NEXT_RULEID']; ?>
&ruleid=<?php echo $this->_tpl_vars['SCANNERRULE']->ruleid; ?>
" title="<?php echo $this->_tpl_vars['MOD']['LBL_MOVE']; ?>
 <?php echo $this->_tpl_vars['MOD']['LBL_DOWN']; ?>
"><img src="<?php echo vtiger_imageurl('arrow_down.gif', $this->_tpl_vars['THEME']); ?>
" border=0></a>
								<?php endif; ?>
								<?php if ($this->_tpl_vars['PREV_RULEID']): ?>
<a href="index.php?module=Settings&action=MailScanner&parenttabl=Settings&mode=rulemove_up&scannername=<?php echo $this->_tpl_vars['SCANNERINFO']['scannername']; ?>
&targetruleid=<?php echo $this->_tpl_vars['PREV_RULEID']; ?>
&ruleid=<?php echo $this->_tpl_vars['SCANNERRULE']->ruleid; ?>
" title="<?php echo $this->_tpl_vars['MOD']['LBL_MOVE']; ?>
 <?php echo $this->_tpl_vars['MOD']['LBL_UP']; ?>
"><img src="<?php echo vtiger_imageurl('arrow_up.gif', $this->_tpl_vars['THEME']); ?>
" border=0></a>
								<?php endif; ?>
								</span>
							</td>
							<td nowrap class="small cellLabel" align=right colspan=2>
								<a href="index.php?module=Settings&action=MailScanner&parenttab=Settings&mode=ruleedit&scannername=<?php echo $this->_tpl_vars['SCANNERINFO']['scannername']; ?>
&ruleid=<?php echo $this->_tpl_vars['SCANNERRULE']->ruleid; ?>
"><?php echo $this->_tpl_vars['APP']['LBL_EDIT']; ?>
</a> |
								<a href="index.php?module=Settings&action=MailScanner&parenttab=Settings&mode=ruledelete&scannername=<?php echo $this->_tpl_vars['SCANNERINFO']['scannername']; ?>
&ruleid=<?php echo $this->_tpl_vars['SCANNERRULE']->ruleid; ?>
" onclick="return confirm('Are you sure to delete this Rule?');"><?php echo $this->_tpl_vars['APP']['LBL_DELETE']; ?>
</a>
							</td>
						</tr>
						<tr>
                            <td nowrap class="small cellLabel" width="20%"><strong><?php echo $this->_tpl_vars['MOD']['LBL_FROM']; ?>
</strong></td>
                            <td nowrap class="small cellText" width="80%" colspan=2>
								<?php echo $this->_tpl_vars['SCANNERRULE']->fromaddress; ?>

							</td>
						</tr>
						<tr>
                            <td nowrap class="small cellLabel" width="20%"><strong><?php echo $this->_tpl_vars['MOD']['LBL_TO']; ?>
</strong></td>
                            <td nowrap class="small cellText" width="80%" colspan=2>
								<?php echo $this->_tpl_vars['SCANNERRULE']->toaddress; ?>

							</td>
						</tr>
						<tr>
                            <td nowrap class="small cellLabel" width="20%"><strong><?php echo $this->_tpl_vars['MOD']['LBL_SUBJECT']; ?>
</strong></td>
                            <td nowrap class="small cellText" width="10%"><?php echo $this->_tpl_vars['SCANNERRULE']->subjectop; ?>
</td>
                            <td nowrap class="small cellText" width="70%">
								<?php echo $this->_tpl_vars['SCANNERRULE']->subject; ?>

							</td>
						</tr>
						<tr>
                            <td nowrap class="small cellLabel" width="20%"><strong><?php echo $this->_tpl_vars['MOD']['LBL_BODY']; ?>
</strong></td>
                            <td nowrap class="small cellText" width="10%"><?php echo $this->_tpl_vars['SCANNERRULE']->bodyop; ?>
</td>
                            <td nowrap class="small cellText" width="70%">
								<?php echo $this->_tpl_vars['SCANNERRULE']->body; ?>

							</td>
						</tr>
						<tr>
                            <td nowrap class="small cellLabel" width="20%"><strong><?php echo $this->_tpl_vars['MOD']['LBL_MATCH']; ?>
</strong></td>
                            <td nowrap class="small cellText" width="80%" colspan=2>
								<?php if ($this->_tpl_vars['SCANNERRULE']->matchusing == 'OR'): ?><?php echo $this->_tpl_vars['MOD']['LBL_ANY']; ?>
 <?php echo $this->_tpl_vars['MOD']['LBL_CONDITION']; ?>

								<?php else: ?> <?php echo $this->_tpl_vars['MOD']['LBL_ALL']; ?>
 <?php echo $this->_tpl_vars['MOD']['LBL_CONDITION']; ?>
 <?php endif; ?>
							</td>
						</tr>
						<tr>
                            <td nowrap class="small cellLabel" width="20%"><strong><?php echo $this->_tpl_vars['MOD']['LBL_ACTION']; ?>
</strong></td>
                            <td nowrap class="small cellText" width="80%" colspan=2>
								<?php if ($this->_tpl_vars['SCANNERRULE']->useaction->actiontext == 'CREATE,HelpDesk,FROM'): ?> <?php echo $this->_tpl_vars['MOD']['LBL_CREATE']; ?>
 <?php echo $this->_tpl_vars['MOD']['LBL_TICKET']; ?>

								<?php elseif ($this->_tpl_vars['SCANNERRULE']->useaction->actiontext == 'UPDATE,HelpDesk,SUBJECT'): ?> <?php echo $this->_tpl_vars['MOD']['LBL_UPDATE']; ?>
 <?php echo $this->_tpl_vars['MOD']['LBL_TICKET']; ?>

								<?php elseif ($this->_tpl_vars['SCANNERRULE']->useaction->actiontext == 'LINK,Contacts,FROM'): ?><?php echo $this->_tpl_vars['MOD']['LBL_ADD']; ?>
 <?php echo $this->_tpl_vars['MOD']['LBL_TO']; ?>
 <?php echo $this->_tpl_vars['MOD']['LBL_CONTACT']; ?>
 [<?php echo $this->_tpl_vars['MOD']['LBL_FROM_CAPS']; ?>
]
								<?php elseif ($this->_tpl_vars['SCANNERRULE']->useaction->actiontext == 'LINK,Contacts,TO'): ?><?php echo $this->_tpl_vars['MOD']['LBL_ADD']; ?>
 <?php echo $this->_tpl_vars['MOD']['LBL_TO']; ?>
 <?php echo $this->_tpl_vars['MOD']['LBL_CONTACT']; ?>
 [<?php echo $this->_tpl_vars['MOD']['LBL_TO_CAPS']; ?>
]
								<?php elseif ($this->_tpl_vars['SCANNERRULE']->useaction->actiontext == 'LINK,Accounts,FROM'): ?><?php echo $this->_tpl_vars['MOD']['LBL_ADD']; ?>
 <?php echo $this->_tpl_vars['MOD']['LBL_TO']; ?>
 <?php echo $this->_tpl_vars['MOD']['LBL_ACCOUNT']; ?>
 [<?php echo $this->_tpl_vars['MOD']['LBL_FROM_CAPS']; ?>
]
								<?php elseif ($this->_tpl_vars['SCANNERRULE']->useaction->actiontext == 'LINK,Accounts,TO'): ?><?php echo $this->_tpl_vars['MOD']['LBL_ADD']; ?>
 <?php echo $this->_tpl_vars['MOD']['LBL_TO']; ?>
 <?php echo $this->_tpl_vars['MOD']['LBL_ACCOUNT']; ?>
 [<?php echo $this->_tpl_vars['MOD']['LBL_TO_CAPS']; ?>
]
								<?php endif; ?>
							</td>
						</tr>
						<?php if ($this->_tpl_vars['NEXT_RULEID']): ?>
							<tr><td colspan=3 class="small cellText">&nbsp;</td></tr>
						<?php endif; ?>
						<?php $this->assign('PREV_RULEID', $this->_tpl_vars['SCANNERRULE']->ruleid); ?>
					<?php endforeach; endif; unset($_from); ?>
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