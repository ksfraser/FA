<?php /* Smarty version 2.6.18, created on 2015-03-17 18:44:57
         compiled from modules/Import/Import_Result_Details.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'getTranslatedString', 'modules/Import/Import_Result_Details.tpl', 15, false),)), $this); ?>

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
	<tr>
		<td><?php echo getTranslatedString('LBL_TOTAL_RECORDS_FAILED', $this->_tpl_vars['MODULE']); ?>
</td>
		<td width="10%">:</td>
		<td width="30%"><?php echo $this->_tpl_vars['IMPORT_RESULT']['FAILED']; ?>
 / <?php echo $this->_tpl_vars['IMPORT_RESULT']['TOTAL']; ?>
</td>
	</tr>
</table>