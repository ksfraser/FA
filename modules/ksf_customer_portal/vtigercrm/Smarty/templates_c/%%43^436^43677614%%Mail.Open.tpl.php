<?php /* Smarty version 2.6.18, created on 2015-02-17 23:24:18
         compiled from modules/MailManager/Mail.Open.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'getTranslatedString', 'modules/MailManager/Mail.Open.tpl', 15, false),array('modifier', 'escape', 'modules/MailManager/Mail.Open.tpl', 62, false),array('modifier', 'vtiger_imageurl', 'modules/MailManager/Mail.Open.tpl', 115, false),)), $this); ?>
<span class="moduleName" id="mail_fldrname"><?php echo $this->_tpl_vars['FOLDER']->name(); ?>
</span>
<div class="mm_outerborder" id="open_email_con" name="open_email_con">
<table width="100%" cellpadding=2 cellspacing=0 border=0 class="small" style='clear: both;'>
	<tr class="mailSubHeader" valign="top">
		
		<td align=left>
			<a href='javascript:void(0);' onclick="MailManager.mail_close();"><b style="font-size:14px">&#171; <?php echo getTranslatedString('LBL_Go_Back'); ?>
</b></a>&nbsp;&nbsp;&nbsp;
			<span class="dvHeaderText" id="_mailopen_subject"><?php echo $this->_tpl_vars['MAIL']->subject(); ?>
</span>
		</td>
		<td align="right" nowrap="nowrap">
			<?php if ($this->_tpl_vars['MAIL']->msgno() < $this->_tpl_vars['FOLDER']->count()): ?>
				<a href='javascript:void(0);' onclick="MailManager.mail_open( '<?php echo $this->_tpl_vars['FOLDER']->name(); ?>
', <?php echo $this->_tpl_vars['MAIL']->msgno(1); ?>
 );">
					<img border="0" src="modules/Webmails/images/previous.gif" title="<?php echo getTranslatedString('LBL_Previous'); ?>
"></a>
			<?php endif; ?>
			<?php if ($this->_tpl_vars['MAIL']->msgno() > 1): ?>
				<a href='javascript:void(0);' onclick="MailManager.mail_open( '<?php echo $this->_tpl_vars['FOLDER']->name(); ?>
', <?php echo $this->_tpl_vars['MAIL']->msgno(-1); ?>
 );">
				<img border="0" src="modules/Webmails/images/next.gif" title="<?php echo getTranslatedString('LBL_Next'); ?>
"></a>
			<?php endif; ?>
		</td>
	</tr>
<?php echo '<tr valign=top><td>&nbsp;<button class="crmbutton small edit" onclick="MailManager.mail_reply(true);">'; ?><?php echo getTranslatedString('LBL_Reply_All'); ?><?php echo '</button>&nbsp;<button class="crmbutton small edit" onclick="MailManager.mail_reply(false);">'; ?><?php echo getTranslatedString('LBL_Reply'); ?><?php echo '</button>&nbsp;<button class="crmbutton small edit" onclick="MailManager.mail_forward('; ?><?php echo $this->_tpl_vars['MAIL']->msgno(); ?><?php echo ');">'; ?><?php echo getTranslatedString('LBL_Forward'); ?><?php echo '</button>&nbsp;<button class="crmbutton small edit" onclick="MailManager.mail_mark_unread(\''; ?><?php echo $this->_tpl_vars['FOLDER']->name(); ?><?php echo '\', '; ?><?php echo $this->_tpl_vars['MAIL']->msgno(); ?><?php echo ');">'; ?><?php echo getTranslatedString('LBL_Mark_As_Unread'); ?><?php echo '</button>&nbsp;<button class="crmbutton small delete" id = \'mail_delete_dtlview\' class="small" onclick="MailManager.maildelete(\''; ?><?php echo $this->_tpl_vars['FOLDER']->name(); ?><?php echo '\','; ?><?php echo $this->_tpl_vars['MAIL']->msgno(); ?><?php echo ',true);">'; ?><?php echo getTranslatedString('LBL_Delete'); ?><?php echo '</button></td><td rowspan=3 align=right colspan=2><table cellpadding=0 cellspacing=0 border=0 width="100%"><tr><td colspan=2 nowrap="nowrap"><table width=100% cellpadding=0 cellspacing=0 border=0 class="rightMailMerge"><tr><td class="rightMailMergeHeader" align="center"><b>'; ?><?php echo getTranslatedString('LBL_RELATED_RECORDS'); ?><?php echo '</b></td></tr><tr><td class="rightMailMergeContent" align="center"><button class="small" id="_mailrecord_findrel_btn_" onclick="MailManager.mail_find_relationship();">'; ?><?php echo getTranslatedString('JSLBL_Find_Relation_Now'); ?><?php echo '</button><div id="_mailrecord_relationshipdiv_"></div></td></tr></table></td></tr></table></td></tr><tr valign=top><td><span id="_mailopen_msgid_" style="display:none;">'; ?><?php echo smarty_modifier_escape($this->_tpl_vars['MAIL']->_uniqueid, 'UTF-8'); ?><?php echo '</span><table width="100%" cellpadding=2 cellspacing=0 border=0 class="small"><tr><td width="100px" align=right>'; ?><?php echo getTranslatedString('LBL_FROM'); ?><?php echo ':</td><td id="_mailopen_from">'; ?><?php $_from = $this->_tpl_vars['MAIL']->from(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['SENDER']):
?><?php echo ''; ?><?php echo $this->_tpl_vars['SENDER']; ?><?php echo ''; ?><?php endforeach; endif; unset($_from); ?><?php echo '</td></tr>'; ?><?php if ($this->_tpl_vars['MAIL']->to()): ?><?php echo '<tr><td width="100px" align=right>'; ?><?php echo getTranslatedString('LBL_TO'); ?><?php echo ':</td><td id="_mailopen_to">'; ?><?php $_from = $this->_tpl_vars['MAIL']->to(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['TO'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['TO']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['RECEPIENT']):
        $this->_foreach['TO']['iteration']++;
?><?php echo ''; ?><?php if (($this->_foreach['TO']['iteration']-1) > 0): ?><?php echo ', '; ?><?php endif; ?><?php echo ''; ?><?php echo $this->_tpl_vars['RECEPIENT']; ?><?php echo ''; ?><?php endforeach; endif; unset($_from); ?><?php echo '</td></tr>'; ?><?php endif; ?><?php echo ''; ?><?php if ($this->_tpl_vars['MAIL']->cc()): ?><?php echo '<tr><td width="100px" align=right>'; ?><?php echo getTranslatedString('LBL_CC'); ?><?php echo ':</td><td id="_mailopen_cc">'; ?><?php $_from = $this->_tpl_vars['MAIL']->cc(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['CC'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['CC']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['CC']):
        $this->_foreach['CC']['iteration']++;
?><?php echo ''; ?><?php if (($this->_foreach['CC']['iteration']-1) > 0): ?><?php echo ', '; ?><?php endif; ?><?php echo ''; ?><?php echo $this->_tpl_vars['CC']; ?><?php echo ''; ?><?php endforeach; endif; unset($_from); ?><?php echo '</td></tr>'; ?><?php endif; ?><?php echo ''; ?><?php if ($this->_tpl_vars['MAIL']->bcc()): ?><?php echo '<tr><td width="100px" align=right>'; ?><?php echo getTranslatedString('LBL_BCC'); ?><?php echo ':</td><td id="_mailopen_bcc">'; ?><?php $_from = $this->_tpl_vars['MAIL']->bcc(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['BCC'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['BCC']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['BCC']):
        $this->_foreach['BCC']['iteration']++;
?><?php echo ''; ?><?php if (($this->_foreach['BCC']['iteration']-1) > 0): ?><?php echo ', '; ?><?php endif; ?><?php echo ''; ?><?php echo $this->_tpl_vars['BCC']; ?><?php echo ''; ?><?php endforeach; endif; unset($_from); ?><?php echo '</td></tr>'; ?><?php endif; ?><?php echo '<tr><td width="100px" align=right>'; ?><?php echo getTranslatedString('LBL_Date'); ?><?php echo ':</td><td id="_mailopen_date">'; ?><?php echo $this->_tpl_vars['MAIL']->date(); ?><?php echo '</td></tr>'; ?><?php if ($this->_tpl_vars['MAIL']->attachments(false)): ?><?php echo '<tr><td width="100px" align=right>'; ?><?php echo getTranslatedString('LBL_Attachments'); ?><?php echo ':</td><td>'; ?><?php $_from = $this->_tpl_vars['MAIL']->attachments(false); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['attach'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['attach']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['ATTACHNAME'] => $this->_tpl_vars['ATTACHVALUE']):
        $this->_foreach['attach']['iteration']++;
?><?php echo '<img border=0 src="'; ?><?php echo vtiger_imageurl('attachments.gif', $this->_tpl_vars['THEME']); ?><?php echo '"><a href="index.php?module='; ?><?php echo $this->_tpl_vars['MODULE']; ?><?php echo '&action='; ?><?php echo $this->_tpl_vars['MODULE']; ?><?php echo 'Ajax&file=index&_operation=mail&_operationarg=attachment_dld&_muid='; ?><?php echo $this->_tpl_vars['MAIL']->muid(); ?><?php echo '&_atname='; ?><?php echo smarty_modifier_escape($this->_tpl_vars['ATTACHNAME'], 'htmlall', 'UTF-8'); ?><?php echo '">'; ?><?php echo $this->_tpl_vars['ATTACHNAME']; ?><?php echo '</a>&nbsp;'; ?><?php endforeach; endif; unset($_from); ?><?php echo '<input type="hidden" id="_mail_attachmentcount_" value="'; ?><?php echo $this->_foreach['attach']['total']; ?><?php echo '" ></td></tr>'; ?><?php endif; ?><?php echo '</table></td></tr>'; ?>

<tr valign=top>
	<td width="100%">
		<div class='mm_body' id="_mailopen_body">
			<?php echo $this->_tpl_vars['MAIL']->body(); ?>

		</div>
	</td>
</tr>
</table>

</div>