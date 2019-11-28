<?php /* Smarty version 2.6.18, created on 2015-02-17 23:22:42
         compiled from modules/MailManager/Folder.Open.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'getTranslatedString', 'modules/MailManager/Folder.Open.tpl', 24, false),array('modifier', 'htmlentities', 'modules/MailManager/Folder.Open.tpl', 51, false),)), $this); ?>
<span class="moduleName" id="mail_fldrname"><?php echo $this->_tpl_vars['FOLDER']->name(); ?>
</span>
<div class="mailClientBg mm_outerborder" id="email_con" name="email_con">
<table width="100%" cellpadding=3 cellspacing=0 border=0 class="small">
	
<?php if ($this->_tpl_vars['FOLDER']->mails()): ?>
<tr>
	<td>
		
	</td>
	<td align="right" colspan="4">
		<table>
			<tr>
			<?php if ($this->_tpl_vars['FOLDER']->hasPrevPage()): ?>
				<td>
					<a href="#<?php echo $this->_tpl_vars['FOLDER']->name(); ?>
/page/<?php echo $this->_tpl_vars['FOLDER']->pageCurrent(-1); ?>
" onclick="MailManager.folder_open('<?php echo $this->_tpl_vars['FOLDER']->name(); ?>
', <?php echo $this->_tpl_vars['FOLDER']->pageCurrent(-1); ?>
);">
					<img border="0" src="modules/Webmails/images/previous.gif" title="<?php echo getTranslatedString('LBL_Previous'); ?>
"></a>
				</td>
			<?php endif; ?>

				<td><b><?php echo $this->_tpl_vars['FOLDER']->pageInfo(); ?>
</b></td>

			<?php if ($this->_tpl_vars['FOLDER']->hasNextPage()): ?>
				<td><a href="#<?php echo $this->_tpl_vars['FOLDER']->name(); ?>
/page/<?php echo $this->_tpl_vars['FOLDER']->pageCurrent(1); ?>
" onclick="MailManager.folder_open('<?php echo $this->_tpl_vars['FOLDER']->name(); ?>
', <?php echo $this->_tpl_vars['FOLDER']->pageCurrent(1); ?>
);">
					<img border="0" src="modules/Webmails/images/next.gif" title="<?php echo getTranslatedString('LBL_Next'); ?>
"></a>
				</td>
			<?php endif; ?>
			</tr>
		</table>
	</td>
</tr>
<tr>
	<td colspan="5">
	<table class="mm_tableHeadBg small" width="100%" cellspacing="0" border="0" cellpadding="2px" >
		<tr>
			<td align="left" style="width: 10%;" nowrap="nowrap">
				<input align="left" type="checkbox" class='small'  name="selectall" id="parentCheckBox" onClick='MailManager.toggleSelect(this.checked,"mc_box");'/>&nbsp;&nbsp;
				<input type=button class='crmbutton small delete' onclick="MailManager.massMailDelete('<?php echo $this->_tpl_vars['FOLDER']->name(); ?>
');" value="<?php echo getTranslatedString('LBL_Delete'); ?>
"/>
			</td>
			<td align="left">
				<select class='small' id="moveFolderList" onchange="MailManager.moveMail(this);">
					<option value=""><?php echo getTranslatedString('LBL_MOVE_TO', $this->_tpl_vars['MODULE']); ?>
</option>
					<?php $_from = $this->_tpl_vars['FOLDERLIST']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['folder']):
?>
						<option value="<?php echo htmlentities($this->_tpl_vars['folder']); ?>
" ><?php echo htmlentities($this->_tpl_vars['folder']); ?>
</option>
					<?php endforeach; endif; unset($_from); ?>
				</select>
			</td>
			<td class="moduleName" align="right"><?php echo getTranslatedString('LBL_Search'); ?>

				<input type="text" id='search_txt' class='small' value="<?php echo $this->_tpl_vars['QUERY']; ?>
" />
				<?php echo getTranslatedString('LBL_IN'); ?>

				<select class='small' id="search_type">
					<?php $_from = $this->_tpl_vars['SEARCHOPTIONS']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['arr']):
?>
						<?php if ($this->_tpl_vars['arr'] == $this->_tpl_vars['TYPE']): ?>
							<option value="<?php echo $this->_tpl_vars['arr']; ?>
" selected><?php echo ((is_array($_tmp=$this->_tpl_vars['arr'])) ? $this->_run_mod_handler('getTranslatedString', true, $_tmp) : getTranslatedString($_tmp)); ?>
</option>
						<?php else: ?>
							<option value="<?php echo $this->_tpl_vars['arr']; ?>
" ><?php echo ((is_array($_tmp=$this->_tpl_vars['arr'])) ? $this->_run_mod_handler('getTranslatedString', true, $_tmp) : getTranslatedString($_tmp)); ?>
</option>
						<?php endif; ?>
					<?php endforeach; endif; unset($_from); ?>
				</select>
				<input type=submit class="crmbutton small edit" onclick="MailManager.search_mails('<?php echo $this->_tpl_vars['FOLDER']->name(); ?>
');" value="<?php echo getTranslatedString('LBL_FIND'); ?>
" id="mm_search"/>
			</td>
		</tr>
	</table>
	</td>
</tr>
<?php endif; ?>

<table cellpadding="0" cellspacing="0" border="0" width="100%" class="cmall mm_mailwrapper">
	<?php $_from = $this->_tpl_vars['FOLDER']->mails(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['MAIL']):
?>
	<tr class="mm_lvtColData <?php if ($this->_tpl_vars['MAIL']->isRead()): ?>mm_normal<?php else: ?>mm_bold<?php endif; ?> mm_clickable"
		 id="_mailrow_<?php echo $this->_tpl_vars['MAIL']->msgNo(); ?>
" onmouseover='MailManager.highLightListMail(this);' onmouseout='MailManager.unHighLightListMail(this);'>
		<td width="3%"><input type='checkbox' value = "<?php echo $this->_tpl_vars['MAIL']->msgNo(); ?>
" name = 'mc_box' class='small'
                              onclick='MailManager.toggleSelectMail(this.checked, this);'></td>
		<td width="27%" onclick="MailManager.mail_open('<?php echo $this->_tpl_vars['FOLDER']->name(); ?>
', <?php echo $this->_tpl_vars['MAIL']->msgNo(); ?>
);"><?php echo $this->_tpl_vars['MAIL']->from(30); ?>
</td>
		<td onclick="MailManager.mail_open('<?php echo $this->_tpl_vars['FOLDER']->name(); ?>
', <?php echo $this->_tpl_vars['MAIL']->msgNo(); ?>
);"><?php echo $this->_tpl_vars['MAIL']->subject(); ?>
</td>
		<td width="17%" align="right" onclick="MailManager.mail_open('<?php echo $this->_tpl_vars['FOLDER']->name(); ?>
', <?php echo $this->_tpl_vars['MAIL']->msgNo(); ?>
);"><?php echo $this->_tpl_vars['MAIL']->date(true); ?>
</td>
	</tr>
	<?php endforeach; else: ?>
</table>
<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
	<td><a href='#<?php echo $this->_tpl_vars['FOLDER']->name(); ?>
' onclick="MailManager.folder_open('<?php echo $this->_tpl_vars['FOLDER']->name(); ?>
');"><b><?php echo $this->_tpl_vars['FOLDER']->name(); ?>
</b></a></td>
</tr>
<tr>
	<td><?php echo getTranslatedString('LBL_No_Mails_Found'); ?>
</td>
</tr>
<?php endif; unset($_from); ?>
</table>
</div>