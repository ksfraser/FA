<?php /* Smarty version 2.6.18, created on 2015-03-17 18:44:37
         compiled from modules/Import/ImportStatus.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'getTranslatedString', 'modules/Import/ImportStatus.tpl', 39, false),)), $this); ?>
<script language="JavaScript" type="text/javascript" src="modules/MailManager/resources/jquery-1.6.2.min.js"></script>
<script type="text/javascript" charset="utf-8">
	jQuery.noConflict();
</script>
<script language="JavaScript" type="text/javascript" src="modules/Import/resources/Import.js"></script>
<?php echo '
<script type="text/javascript">
jQuery(document).ready(function() {
	setTimeout(function() {
		jQuery("[name=importStatusForm]").get(0).submit();
		}, 500);
});
</script>
'; ?>


<form onsubmit="VtigerJS_DialogBox.block();" action="index.php" enctype="multipart/form-data" method="POST" name="importStatusForm">
	<input type="hidden" name="module" value="<?php echo $this->_tpl_vars['FOR_MODULE']; ?>
" />
	<input type="hidden" name="action" value="Import" />
	<?php if ($this->_tpl_vars['CONTINUE_IMPORT'] == 'true'): ?>
	<input type="hidden" name="mode" value="continue_import" />
	<?php else: ?>
	<input type="hidden" name="mode" value="" />
	<?php endif; ?>
</form>
<table style="width:70%;margin-left:auto;margin-right:auto;margin-top:10px;" cellpadding="10" cellspacing="10" class="searchUIBasic">
	<tr>
		<td class="heading2" align="left" colspan="2">
			<?php echo getTranslatedString('LBL_IMPORT', $this->_tpl_vars['MODULE']); ?>
 <?php echo getTranslatedString($this->_tpl_vars['FOR_MODULE'], $this->_tpl_vars['FOR_MODULE']); ?>
 - 
			<span class="style1"><?php echo getTranslatedString('LBL_RUNNING', $this->_tpl_vars['MODULE']); ?>
 ... </span>
		</td>
	</tr>
	<?php if ($this->_tpl_vars['ERROR_MESSAGE'] != ''): ?>
	<tr>
		<td class="style1" align="left" colspan="2">
			<?php echo $this->_tpl_vars['ERROR_MESSAGE']; ?>

		</td>
	</tr>
	<?php endif; ?>
	<tr>
		<td valign="top">
			<table cellpadding="10" cellspacing="0" align="center" class="dvtSelectedCell thickBorder">
				<tr>
					<td><?php echo getTranslatedString('LBL_TOTAL_RECORDS_IMPORTED', $this->_tpl_vars['MODULE']); ?>
</td>
					<td width="10%">:</td>
					<td width="30%"><?php echo $this->_tpl_vars['IMPORT_RESULT']['IMPORTED']; ?>
 / <?php echo $this->_tpl_vars['IMPORT_RESULT']['TOTAL']; ?>
</td>
				</tr>
				<tr>
					<td colspan="3">
						<table cellpadding="10" cellspacing="0" class="calDayHour">
							<tr>
								<td><?php echo getTranslatedString('LBL_NUMBER_OF_RECORDS_CREATED', $this->_tpl_vars['MODULE']); ?>
</td>
								<td width="10%">:</td>
								<td width="10%"><?php echo $this->_tpl_vars['IMPORT_RESULT']['CREATED']; ?>
</td>
							</tr>
							<tr>
								<td><?php echo getTranslatedString('LBL_NUMBER_OF_RECORDS_UPDATED', $this->_tpl_vars['MODULE']); ?>
</td>
								<td width="10%">:</td>
								<td width="10%"><?php echo $this->_tpl_vars['IMPORT_RESULT']['UPDATED']; ?>
</td>
							</tr>
							<tr>
								<td><?php echo getTranslatedString('LBL_NUMBER_OF_RECORDS_SKIPPED', $this->_tpl_vars['MODULE']); ?>
</td>
								<td width="10%">:</td>
								<td width="10%"><?php echo $this->_tpl_vars['IMPORT_RESULT']['SKIPPED']; ?>
</td>
							</tr>
							<tr>
								<td><?php echo getTranslatedString('LBL_NUMBER_OF_RECORDS_MERGED', $this->_tpl_vars['MODULE']); ?>
</td>
								<td width="10%">:</td>
								<td width="10%"><?php echo $this->_tpl_vars['IMPORT_RESULT']['MERGED']; ?>
</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td align="right">
		<input type="button" name="cancel" value="<?php echo getTranslatedString('LBL_CANCEL_IMPORT', $this->_tpl_vars['MODULE']); ?>
" class="crmButton small delete"
			   onclick="location.href='index.php?module=<?php echo $this->_tpl_vars['FOR_MODULE']; ?>
&action=Import&mode=cancel_import&import_id=<?php echo $this->_tpl_vars['IMPORT_ID']; ?>
'" />
		</td>
	</tr>
</table>