<?php /* Smarty version 2.6.18, created on 2015-02-18 21:53:45
         compiled from Inventory/ProductDetailsEditView.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'getTranslatedCurrencyString', 'Inventory/ProductDetailsEditView.tpl', 115, false),array('modifier', 'cat', 'Inventory/ProductDetailsEditView.tpl', 150, false),array('modifier', 'vtiger_imageurl', 'Inventory/ProductDetailsEditView.tpl', 184, false),)), $this); ?>

<script type="text/javascript" src="include/js/Inventory.js"></script>
<!-- Added to display the Product Details -->
<script type="text/javascript">
if(!e)
	window.captureEvents(Event.MOUSEMOVE);

//  window.onmousemove= displayCoords;
//  window.onclick = fnRevert;
  
function displayCoords(currObj,obj,mode,curr_row) 
{
	if(mode != 'discount_final' && mode != 'sh_tax_div_title' && mode != 'group_tax_div_title')
	{
		var curr_productid = document.getElementById("hdnProductId"+curr_row).value;
		if(curr_productid == '')
		{
			alert("<?php echo $this->_tpl_vars['APP']['PLEASE_SELECT_LINE_ITEM']; ?>
");
			return false;
		}
		var curr_quantity = document.getElementById("qty"+curr_row).value;
		if(curr_quantity == '')
		{
			alert("<?php echo $this->_tpl_vars['APP']['PLEASE_FILL_QUANTITY']; ?>
");
			return false;
		}
	}

	//Set the Header value for Discount
	if(mode == 'discount')
	{
		document.getElementById("discount_div_title"+curr_row).innerHTML = '<b><?php echo $this->_tpl_vars['APP']['LABEL_SET_DISCOUNT_FOR_X_COLON']; ?>
 '+document.getElementById("productTotal"+curr_row).innerHTML+'</b>';
	}
	else if(mode == 'tax')
	{
		document.getElementById("tax_div_title"+curr_row).innerHTML = "<b><?php echo $this->_tpl_vars['APP']['LABEL_SET_TAX_FOR']; ?>
 "+document.getElementById("totalAfterDiscount"+curr_row).innerHTML+'</b>';
	}
	else if(mode == 'discount_final')
	{
		document.getElementById("discount_div_title_final").innerHTML = '<b><?php echo $this->_tpl_vars['APP']['LABEL_SET_DISCOUNT_FOR_COLON']; ?>
 '+document.getElementById("netTotal").innerHTML+'</b>';
	}
	else if(mode == 'sh_tax_div_title')
	{
		document.getElementById("sh_tax_div_title").innerHTML = '<b><?php echo $this->_tpl_vars['APP']['LABEL_SET_SH_TAX_FOR_COLON']; ?>
 '+document.getElementById("shipping_handling_charge").value+'</b>';
	}
	else if(mode == 'group_tax_div_title')
	{
		var net_total_after_discount = eval(document.getElementById("netTotal").innerHTML)-eval(document.getElementById("discountTotal_final").innerHTML);
		document.getElementById("group_tax_div_title").innerHTML = '<b><?php echo $this->_tpl_vars['APP']['LABEL_SET_GROUP_TAX_FOR_COLON']; ?>
 '+net_total_after_discount+'</b>';
	}

	fnvshobj(currObj,'tax_container');
	if(document.all)
	{
		var divleft = document.getElementById("tax_container").style.left;
		var divabsleft = divleft.substring(0,divleft.length-2);
		document.getElementById(obj).style.left = eval(divabsleft) - 120;

		var divtop = document.getElementById("tax_container").style.top;
		var divabstop =  divtop.substring(0,divtop.length-2);
		document.getElementById(obj).style.top = eval(divabstop);
	}else
	{
		document.getElementById(obj).style.left =  document.getElementById("tax_container").left;
		document.getElementById(obj).style.top = document.getElementById("tax_container").top;
	}
	document.getElementById(obj).style.display = "block";

}
  
	function doNothing(){
	}
	
	function fnHidePopDiv(obj){
		document.getElementById(obj).style.display = 'none';
	}
</script>


<tr><td colspan="4" align="left">

<table width="100%"  border="0" align="center" cellpadding="5" cellspacing="0" class="crmTable" id="proTab">
   <tr>
   	<?php if ($this->_tpl_vars['MODULE'] != 'PurchaseOrder'): ?>
			<td colspan="3" class="dvInnerHeader">
	<?php else: ?>
			<td colspan="2" class="dvInnerHeader">
	<?php endif; ?>
		<b><?php echo $this->_tpl_vars['APP']['LBL_ITEM_DETAILS']; ?>
</b>
	</td>
	
	<td class="dvInnerHeader" align="center" colspan="2">
		<input type="hidden" value="<?php echo $this->_tpl_vars['INV_CURRENCY_ID']; ?>
" id="prev_selected_currency_id" />
		<b><?php echo $this->_tpl_vars['APP']['LBL_CURRENCY']; ?>
</b>&nbsp;&nbsp;
		<select class="small" id="inventory_currency" name="inventory_currency" onchange="updatePrices();">
		<?php $_from = $this->_tpl_vars['CURRENCIES_LIST']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['count'] => $this->_tpl_vars['currency_details']):
?>
			<?php if ($this->_tpl_vars['currency_details']['curid'] == $this->_tpl_vars['INV_CURRENCY_ID']): ?>
				<?php $this->assign('currency_selected', 'selected'); ?>
			<?php else: ?>
				<?php $this->assign('currency_selected', ""); ?>
			<?php endif; ?>
			<OPTION value="<?php echo $this->_tpl_vars['currency_details']['curid']; ?>
" <?php echo $this->_tpl_vars['currency_selected']; ?>
><?php echo getTranslatedCurrencyString($this->_tpl_vars['currency_details']['currencylabel']); ?>
 (<?php echo $this->_tpl_vars['currency_details']['currencysymbol']; ?>
)</OPTION>
		<?php endforeach; endif; unset($_from); ?>
		</select>
	</td>
	
	<td class="dvInnerHeader" align="center" colspan="2">
		<b><?php echo $this->_tpl_vars['APP']['LBL_TAX_MODE']; ?>
</b>&nbsp;&nbsp;
		
		<?php if ($this->_tpl_vars['ASSOCIATEDPRODUCTS']['1']['final_details']['taxtype'] == 'group'): ?>
			<?php $this->assign('group_selected', 'selected'); ?>
		<?php else: ?>
			<?php $this->assign('individual_selected', 'selected'); ?>
		<?php endif; ?>

		<select class="small" id="taxtype" name="taxtype" onchange="decideTaxDiv(); calcTotal();">
			<OPTION value="individual" <?php echo $this->_tpl_vars['individual_selected']; ?>
><?php echo $this->_tpl_vars['APP']['LBL_INDIVIDUAL']; ?>
</OPTION>
			<OPTION value="group" <?php echo $this->_tpl_vars['group_selected']; ?>
><?php echo $this->_tpl_vars['APP']['LBL_GROUP']; ?>
</OPTION>
		</select>
	</td>
   </tr>

   <!-- Header for the Product Details -->
   <tr valign="top">
	<td width=5% valign="top" class="lvtCol" align="right"><b><?php echo $this->_tpl_vars['APP']['LBL_TOOLS']; ?>
</b></td>
	<td width=40% class="lvtCol"><font color='red'>*</font><b><?php echo $this->_tpl_vars['APP']['LBL_ITEM_NAME']; ?>
</b></td>
	<?php if ($this->_tpl_vars['MODULE'] != 'PurchaseOrder'): ?>
		<td width=10% class="lvtCol"><b><?php echo $this->_tpl_vars['APP']['LBL_QTY_IN_STOCK']; ?>
</b></td>
	<?php endif; ?>
	<td width=10% class="lvtCol"><b><?php echo $this->_tpl_vars['APP']['LBL_QTY']; ?>
</b></td>
	<td width=10% class="lvtCol" align="right"><b><?php echo $this->_tpl_vars['APP']['LBL_LIST_PRICE']; ?>
</b></td>
	<td width=12% nowrap class="lvtCol" align="right"><b><?php echo $this->_tpl_vars['APP']['LBL_TOTAL']; ?>
</b></td>
	<td width=13% valign="top" class="lvtCol" align="right"><b><?php echo $this->_tpl_vars['APP']['LBL_NET_PRICE']; ?>
</b></td>
   </tr>

   <?php $_from = $this->_tpl_vars['ASSOCIATEDPRODUCTS']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['outer1'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['outer1']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['row_no'] => $this->_tpl_vars['data']):
        $this->_foreach['outer1']['iteration']++;
?>
	<?php $this->assign('deleted', ((is_array($_tmp='deleted')) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['row_no']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['row_no']))); ?>
	<?php $this->assign('hdnProductId', ((is_array($_tmp='hdnProductId')) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['row_no']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['row_no']))); ?>
	<?php $this->assign('productName', ((is_array($_tmp='productName')) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['row_no']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['row_no']))); ?>
	<?php $this->assign('comment', ((is_array($_tmp='comment')) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['row_no']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['row_no']))); ?>
	<?php $this->assign('productDescription', ((is_array($_tmp='productDescription')) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['row_no']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['row_no']))); ?>
	<?php $this->assign('qtyInStock', ((is_array($_tmp='qtyInStock')) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['row_no']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['row_no']))); ?>
	<?php $this->assign('qty', ((is_array($_tmp='qty')) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['row_no']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['row_no']))); ?>
	<?php $this->assign('listPrice', ((is_array($_tmp='listPrice')) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['row_no']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['row_no']))); ?>
	<?php $this->assign('productTotal', ((is_array($_tmp='productTotal')) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['row_no']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['row_no']))); ?>
	<?php $this->assign('subproduct_ids', ((is_array($_tmp='subproduct_ids')) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['row_no']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['row_no']))); ?>
	<?php $this->assign('subprod_names', ((is_array($_tmp='subprod_names')) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['row_no']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['row_no']))); ?>
	<?php $this->assign('entityIdentifier', ((is_array($_tmp='entityType')) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['row_no']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['row_no']))); ?>
	<?php $this->assign('entityType', $this->_tpl_vars['data'][$this->_tpl_vars['entityIdentifier']]); ?>

	<?php $this->assign('discount_type', ((is_array($_tmp='discount_type')) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['row_no']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['row_no']))); ?>
	<?php $this->assign('discount_percent', ((is_array($_tmp='discount_percent')) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['row_no']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['row_no']))); ?>
	<?php $this->assign('checked_discount_percent', ((is_array($_tmp='checked_discount_percent')) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['row_no']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['row_no']))); ?>
	<?php $this->assign('style_discount_percent', ((is_array($_tmp='style_discount_percent')) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['row_no']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['row_no']))); ?>
	<?php $this->assign('discount_amount', ((is_array($_tmp='discount_amount')) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['row_no']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['row_no']))); ?>
	<?php $this->assign('checked_discount_amount', ((is_array($_tmp='checked_discount_amount')) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['row_no']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['row_no']))); ?>
	<?php $this->assign('style_discount_amount', ((is_array($_tmp='style_discount_amount')) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['row_no']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['row_no']))); ?>
	<?php $this->assign('checked_discount_zero', ((is_array($_tmp='checked_discount_zero')) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['row_no']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['row_no']))); ?>

	<?php $this->assign('discountTotal', ((is_array($_tmp='discountTotal')) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['row_no']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['row_no']))); ?>
	<?php $this->assign('totalAfterDiscount', ((is_array($_tmp='totalAfterDiscount')) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['row_no']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['row_no']))); ?>
	<?php $this->assign('taxTotal', ((is_array($_tmp='taxTotal')) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['row_no']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['row_no']))); ?>
	<?php $this->assign('netPrice', ((is_array($_tmp='netPrice')) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['row_no']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['row_no']))); ?>


   <tr id="row<?php echo $this->_tpl_vars['row_no']; ?>
" valign="top">

	<!-- column 1 - delete link - starts -->
	<td  class="crmTableRow small lineOnTop">
		<?php if ($this->_tpl_vars['row_no'] != 1): ?>
			<img src="<?php echo vtiger_imageurl('delete.gif', $this->_tpl_vars['THEME']); ?>
" border="0" onclick="deleteRow('<?php echo $this->_tpl_vars['MODULE']; ?>
',<?php echo $this->_tpl_vars['row_no']; ?>
,'<?php echo $this->_tpl_vars['IMAGE_PATH']; ?>
')">
		<?php endif; ?><br/><br/>
		<?php if ($this->_tpl_vars['row_no'] != 1): ?>
			&nbsp;<a href="javascript:moveUpDown('UP','<?php echo $this->_tpl_vars['MODULE']; ?>
',<?php echo $this->_tpl_vars['row_no']; ?>
)" title="Move Upward"><img src="<?php echo vtiger_imageurl('up_layout.gif', $this->_tpl_vars['THEME']); ?>
" border="0"></a>
		<?php endif; ?>
		<?php if (! ($this->_foreach['outer1']['iteration'] == $this->_foreach['outer1']['total'])): ?>
			&nbsp;<a href="javascript:moveUpDown('DOWN','<?php echo $this->_tpl_vars['MODULE']; ?>
',<?php echo $this->_tpl_vars['row_no']; ?>
)" title="Move Downward"><img src="<?php echo vtiger_imageurl('down_layout.gif', $this->_tpl_vars['THEME']); ?>
" border="0" ></a>
		<?php endif; ?>
		<input type="hidden" id="<?php echo $this->_tpl_vars['deleted']; ?>
" name="<?php echo $this->_tpl_vars['deleted']; ?>
" value="0">
	</td>

	<!-- column 2 - Product Name - starts -->
	<td class="crmTableRow small lineOnTop">
		<!-- Product Re-Ordering Feature Code Addition Starts -->
		<input type="hidden" name="hidtax_row_no<?php echo $this->_tpl_vars['row_no']; ?>
" id="hidtax_row_no<?php echo $this->_tpl_vars['row_no']; ?>
" value="<?php echo $this->_tpl_vars['tax_row_no']; ?>
"/>
		<!-- Product Re-Ordering Feature Code Addition ends -->
		<table width="100%"  border="0" cellspacing="0" cellpadding="1">
			<tr>
				<td class="small" valign="top">
					<input type="text" id="<?php echo $this->_tpl_vars['productName']; ?>
" name="<?php echo $this->_tpl_vars['productName']; ?>
" value="<?php echo $this->_tpl_vars['data'][$this->_tpl_vars['productName']]; ?>
" class="small" style="width: 70%;" readonly />
					<input type="hidden" id="<?php echo $this->_tpl_vars['hdnProductId']; ?>
" name="<?php echo $this->_tpl_vars['hdnProductId']; ?>
" value="<?php echo $this->_tpl_vars['data'][$this->_tpl_vars['hdnProductId']]; ?>
" />
					<input type="hidden" id="lineItemType<?php echo $this->_tpl_vars['row_no']; ?>
" name="lineItemType<?php echo $this->_tpl_vars['row_no']; ?>
" value="<?php echo $this->_tpl_vars['entityType']; ?>
" />
					&nbsp;
					<?php if ($this->_tpl_vars['entityType'] == 'Services'): ?>
						<img id="searchIcon<?php echo $this->_tpl_vars['row_no']; ?>
" title="Services" src="<?php echo vtiger_imageurl('services.gif', $this->_tpl_vars['THEME']); ?>
" style="cursor: pointer;" align="absmiddle" onclick="servicePickList(this,'<?php echo $this->_tpl_vars['MODULE']; ?>
','<?php echo $this->_tpl_vars['row_no']; ?>
')" />
					<?php else: ?>
						<img id="searchIcon<?php echo $this->_tpl_vars['row_no']; ?>
" title="Products" src="<?php echo vtiger_imageurl('products.gif', $this->_tpl_vars['THEME']); ?>
" style="cursor: pointer;" align="absmiddle" onclick="productPickList(this,'<?php echo $this->_tpl_vars['MODULE']; ?>
','<?php echo $this->_tpl_vars['row_no']; ?>
')" />
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<td class="small">
					<input type="hidden" value="<?php echo $this->_tpl_vars['data'][$this->_tpl_vars['subproduct_ids']]; ?>
" id="<?php echo $this->_tpl_vars['subproduct_ids']; ?>
" name="<?php echo $this->_tpl_vars['subproduct_ids']; ?>
" />
					<span id="<?php echo $this->_tpl_vars['subprod_names']; ?>
" name="<?php echo $this->_tpl_vars['subprod_names']; ?>
"  style="color:#C0C0C0;font-style:italic;"><?php echo $this->_tpl_vars['data'][$this->_tpl_vars['subprod_names']]; ?>
</span>
				</td>
			</tr>
			<tr>
				<td class="small" id="setComment">
					<textarea id="<?php echo $this->_tpl_vars['comment']; ?>
" name="<?php echo $this->_tpl_vars['comment']; ?>
" class=small style="width:70%;height:40px"><?php echo $this->_tpl_vars['data'][$this->_tpl_vars['comment']]; ?>
</textarea>
					<img src="<?php echo vtiger_imageurl('clear_field.gif', $this->_tpl_vars['THEME']); ?>
" onClick="<?php echo '$'; ?>
('<?php echo $this->_tpl_vars['comment']; ?>
').value=''"; style="cursor:pointer;" />
				</td>
			</tr>
		</table>
	</td>
	<!-- column 2 - Product Name - ends -->

	<!-- column 3 - Quantity in Stock - starts -->
	<?php if ($this->_tpl_vars['MODULE'] == 'Quotes' || $this->_tpl_vars['MODULE'] == 'SalesOrder' || $this->_tpl_vars['MODULE'] == 'Invoice'): ?>
	   <td class="crmTableRow small lineOnTop" valign="top"><span id="<?php echo $this->_tpl_vars['qtyInStock']; ?>
"><?php echo $this->_tpl_vars['data'][$this->_tpl_vars['qtyInStock']]; ?>
</span></td>
	<?php endif; ?>
	<!-- column 3 - Quantity in Stock - ends -->


	<!-- column 4 - Quantity - starts -->
	<td class="crmTableRow small lineOnTop" valign="top">
		<input id="<?php echo $this->_tpl_vars['qty']; ?>
" name="<?php echo $this->_tpl_vars['qty']; ?>
" type="text" class="small " style="width:50px" onfocus="this.className='detailedViewTextBoxOn'" onBlur="settotalnoofrows(); calcTotal(); loadTaxes_Ajax('<?php echo $this->_tpl_vars['row_no']; ?>
');<?php if ($this->_tpl_vars['MODULE'] == 'Invoice' && $this->_tpl_vars['entityType'] != 'Services'): ?> stock_alert('<?php echo $this->_tpl_vars['row_no']; ?>
');<?php endif; ?>" onChange="setDiscount(this,'<?php echo $this->_tpl_vars['row_no']; ?>
')" value="<?php echo $this->_tpl_vars['data'][$this->_tpl_vars['qty']]; ?>
"/><br><span id="stock_alert<?php echo $this->_tpl_vars['row_no']; ?>
"></span>
	</td>
	<!-- column 4 - Quantity - ends -->

	<!-- column 5 - List Price with Discount, Total After Discount and Tax as table - starts -->
	<td class="crmTableRow small lineOnTop" align="right" valign="top">
		<table width="100%" cellpadding="0" cellspacing="0">
		   <tr>
			<td align="right">
				<input id="<?php echo $this->_tpl_vars['listPrice']; ?>
" name="<?php echo $this->_tpl_vars['listPrice']; ?>
" value="<?php echo $this->_tpl_vars['data'][$this->_tpl_vars['listPrice']]; ?>
" type="text" class="small " style="width:70px" onBlur="calcTotal(); setDiscount(this,'<?php echo $this->_tpl_vars['row_no']; ?>
');callTaxCalc('<?php echo $this->_tpl_vars['row_no']; ?>
');"/>&nbsp;<img src="<?php echo vtiger_imageurl('pricebook.gif', $this->_tpl_vars['THEME']); ?>
" onclick="priceBookPickList(this,'<?php echo $this->_tpl_vars['row_no']; ?>
')">
			</td>
		   </tr>
		   <tr>
			<td align="right" style="padding:5px;" nowrap>
				(-)&nbsp;<b><a href="javascript:doNothing();" onClick="displayCoords(this,'discount_div<?php echo $this->_tpl_vars['row_no']; ?>
','discount','<?php echo $this->_tpl_vars['row_no']; ?>
')" ><?php echo $this->_tpl_vars['APP']['LBL_DISCOUNT']; ?>
</a> : </b>
				<div class="discountUI" id="discount_div<?php echo $this->_tpl_vars['row_no']; ?>
">
					<input type="hidden" id="discount_type<?php echo $this->_tpl_vars['row_no']; ?>
" name="discount_type<?php echo $this->_tpl_vars['row_no']; ?>
" value="<?php echo $this->_tpl_vars['data'][$this->_tpl_vars['discount_type']]; ?>
">
					<table width="100%" border="0" cellpadding="5" cellspacing="0" class="small">
					   <tr>
						<td id="discount_div_title<?php echo $this->_tpl_vars['row_no']; ?>
" nowrap align="left" ></td>
						<td align="right"><img src="<?php echo vtiger_imageurl('close.gif', $this->_tpl_vars['THEME']); ?>
" border="0" onClick="fnHidePopDiv('discount_div<?php echo $this->_tpl_vars['row_no']; ?>
')" style="cursor:pointer;"></td>
					   </tr>
					   <tr>
						<td align="left" class="lineOnTop"><input type="radio" name="discount<?php echo $this->_tpl_vars['row_no']; ?>
" <?php echo $this->_tpl_vars['data'][$this->_tpl_vars['checked_discount_zero']]; ?>
 onclick="setDiscount(this,'<?php echo $this->_tpl_vars['row_no']; ?>
'); callTaxCalc('<?php echo $this->_tpl_vars['row_no']; ?>
');calcTotal();">&nbsp; <?php echo $this->_tpl_vars['APP']['LBL_ZERO_DISCOUNT']; ?>
</td>
						<td class="lineOnTop">&nbsp;</td>
					   </tr>
					   <tr>
						<td align="left"><input type="radio" name="discount<?php echo $this->_tpl_vars['row_no']; ?>
" onclick="setDiscount(this,'<?php echo $this->_tpl_vars['row_no']; ?>
'); callTaxCalc('<?php echo $this->_tpl_vars['row_no']; ?>
'); calcTotal();" <?php echo $this->_tpl_vars['data'][$this->_tpl_vars['checked_discount_percent']]; ?>
>&nbsp; % <?php echo $this->_tpl_vars['APP']['LBL_OF_PRICE']; ?>
</td>
						<td align="right"><input type="text" class="small" size="5" id="discount_percentage<?php echo $this->_tpl_vars['row_no']; ?>
" name="discount_percentage<?php echo $this->_tpl_vars['row_no']; ?>
" value="<?php echo $this->_tpl_vars['data'][$this->_tpl_vars['discount_percent']]; ?>
" <?php echo $this->_tpl_vars['data'][$this->_tpl_vars['style_discount_percent']]; ?>
 onBlur="setDiscount(this,'<?php echo $this->_tpl_vars['row_no']; ?>
'); callTaxCalc('<?php echo $this->_tpl_vars['row_no']; ?>
'); calcTotal();">&nbsp;%</td>
					   </tr>
					   <tr>
						<td align="left" nowrap><input type="radio" name="discount<?php echo $this->_tpl_vars['row_no']; ?>
" onclick="setDiscount(this,'<?php echo $this->_tpl_vars['row_no']; ?>
'); callTaxCalc('<?php echo $this->_tpl_vars['row_no']; ?>
'); calcTotal();" <?php echo $this->_tpl_vars['data'][$this->_tpl_vars['checked_discount_amount']]; ?>
>&nbsp;<?php echo $this->_tpl_vars['APP']['LBL_DIRECT_PRICE_REDUCTION']; ?>
</td>
						<td align="right"><input type="text" id="discount_amount<?php echo $this->_tpl_vars['row_no']; ?>
" name="discount_amount<?php echo $this->_tpl_vars['row_no']; ?>
" size="5" value="<?php echo $this->_tpl_vars['data'][$this->_tpl_vars['discount_amount']]; ?>
" <?php echo $this->_tpl_vars['data'][$this->_tpl_vars['style_discount_amount']]; ?>
 onBlur="setDiscount(this,<?php echo $this->_tpl_vars['row_no']; ?>
); callTaxCalc('<?php echo $this->_tpl_vars['row_no']; ?>
'); calcTotal();"></td>
					   </tr>
					</table>
				</div>
			</td>
		   </tr>
		   <tr>
			<td align="right" style="padding:5px;" nowrap>
				<b><?php echo $this->_tpl_vars['APP']['LBL_TOTAL_AFTER_DISCOUNT']; ?>
 :</b>
			</td>
		   </tr>
		   <tr id="individual_tax_row<?php echo $this->_tpl_vars['row_no']; ?>
" class="TaxShow">
			<td align="right" style="padding:5px;" nowrap>
				(+)&nbsp;<b><a href="javascript:doNothing();" onClick="displayCoords(this,'tax_div<?php echo $this->_tpl_vars['row_no']; ?>
','tax','<?php echo $this->_tpl_vars['row_no']; ?>
')" ><?php echo $this->_tpl_vars['APP']['LBL_TAX']; ?>
 </a> : </b>
				<div class="discountUI" id="tax_div<?php echo $this->_tpl_vars['row_no']; ?>
">
					<!-- we will form the table with all taxes -->
					<table width="100%" border="0" cellpadding="5" cellspacing="0" class="small" id="tax_table<?php echo $this->_tpl_vars['row_no']; ?>
">
					   <tr>
						<td id="tax_div_title<?php echo $this->_tpl_vars['row_no']; ?>
" nowrap align="left" ><b>Set Tax for : <?php echo $this->_tpl_vars['data'][$this->_tpl_vars['totalAfterDiscount']]; ?>
</b></td>
						<td>&nbsp;</td>
						<td align="right"><img src="<?php echo vtiger_imageurl('close.gif', $this->_tpl_vars['THEME']); ?>
" border="0" onClick="fnHidePopDiv('tax_div<?php echo $this->_tpl_vars['row_no']; ?>
')" style="cursor:pointer;"></td>
					   </tr>

					<?php $_from = $this->_tpl_vars['data']['taxes']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['tax_row_no'] => $this->_tpl_vars['tax_data']):
?>
					   <?php $this->assign('taxname', ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['tax_data']['taxname'])) ? $this->_run_mod_handler('cat', true, $_tmp, '_percentage') : smarty_modifier_cat($_tmp, '_percentage')))) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['row_no']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['row_no']))); ?>
					   <?php $this->assign('tax_id_name', ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp='hidden_tax')) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['tax_row_no']+1) : smarty_modifier_cat($_tmp, $this->_tpl_vars['tax_row_no']+1)))) ? $this->_run_mod_handler('cat', true, $_tmp, '_percentage') : smarty_modifier_cat($_tmp, '_percentage')))) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['row_no']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['row_no']))); ?>
					   <?php $this->assign('taxlabel', ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['tax_data']['taxlabel'])) ? $this->_run_mod_handler('cat', true, $_tmp, '_percentage') : smarty_modifier_cat($_tmp, '_percentage')))) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['row_no']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['row_no']))); ?>
					   <?php $this->assign('popup_tax_rowname', ((is_array($_tmp='popup_tax_row')) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['row_no']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['row_no']))); ?>
					   <tr>
						<td align="left" class="lineOnTop">
							<input type="text" class="small" size="5" name="<?php echo $this->_tpl_vars['taxname']; ?>
" id="<?php echo $this->_tpl_vars['taxname']; ?>
" value="<?php echo $this->_tpl_vars['tax_data']['percentage']; ?>
" onBlur="calcCurrentTax('<?php echo $this->_tpl_vars['taxname']; ?>
',<?php echo $this->_tpl_vars['row_no']; ?>
,<?php echo $this->_tpl_vars['tax_row_no']; ?>
)">&nbsp;%
							<input type="hidden" id="<?php echo $this->_tpl_vars['tax_id_name']; ?>
" value="<?php echo $this->_tpl_vars['taxname']; ?>
">
						</td>
						<td align="center" class="lineOnTop"><?php echo $this->_tpl_vars['tax_data']['taxlabel']; ?>
</td>
						<td align="right" class="lineOnTop">
							<input type="text" class="small" size="6" name="<?php echo $this->_tpl_vars['popup_tax_rowname']; ?>
" id="<?php echo $this->_tpl_vars['popup_tax_rowname']; ?>
" style="cursor:pointer;" value="0.0" readonly>
						</td>
					   </tr>

					<?php endforeach; endif; unset($_from); ?>

					</table>
				</div>
				<!-- This above div is added to display the tax informations -->
			</td>
		   </tr>
		</table>
	</td>
	<!-- column 5 - List Price with Discount, Total After Discount and Tax as table - ends -->


	<!-- column 6 - Product Total - starts -->
	<td class="crmTableRow small lineOnTop" align="right">
		<table width="100%" cellpadding="5" cellspacing="0">
		   <tr>
			<td id="productTotal<?php echo $this->_tpl_vars['row_no']; ?>
" align="right"><?php echo $this->_tpl_vars['data'][$this->_tpl_vars['productTotal']]; ?>
</td>
		   </tr>
		   <tr>
			<td id="discountTotal<?php echo $this->_tpl_vars['row_no']; ?>
" align="right"><?php echo $this->_tpl_vars['data'][$this->_tpl_vars['discountTotal']]; ?>
</td>
		   </tr>
		   <tr>
			<td id="totalAfterDiscount<?php echo $this->_tpl_vars['row_no']; ?>
" align="right"><?php echo $this->_tpl_vars['data'][$this->_tpl_vars['totalAfterDiscount']]; ?>
</td>
		   </tr>
		   <tr>
			<td id="taxTotal<?php echo $this->_tpl_vars['row_no']; ?>
" align="right"><?php echo $this->_tpl_vars['data'][$this->_tpl_vars['taxTotal']]; ?>
</td>
		   </tr>
		</table>
	</td>
	<!-- column 6 - Product Total - ends -->

	<!-- column 7 - Net Price - starts -->
	<td valign="bottom" class="crmTableRow small lineOnTop" align="right">
		<span id="netPrice<?php echo $this->_tpl_vars['row_no']; ?>
"><b><?php echo $this->_tpl_vars['data'][$this->_tpl_vars['netPrice']]; ?>
</b></span>
	</td>
	<!-- column 7 - Net Price - ends -->


   </tr>
   <!-- Product Details First row - Ends -->
   <?php endforeach; endif; unset($_from); ?>
</table>



<table width="100%"  border="0" align="center" cellpadding="5" cellspacing="0" class="crmTable">
   <!-- Add Product Button -->
   <tr>
	<td colspan="3">
			<input type="button" name="Button" class="crmbutton small create" value="<?php echo $this->_tpl_vars['APP']['LBL_ADD_PRODUCT']; ?>
" onclick="fnAddProductRow('<?php echo $this->_tpl_vars['MODULE']; ?>
','<?php echo $this->_tpl_vars['IMAGE_PATH']; ?>
');" />
			<input type="button" name="Button" class="crmbutton small create" value="<?php echo $this->_tpl_vars['APP']['LBL_ADD_SERVICE']; ?>
" onclick="fnAddServiceRow('<?php echo $this->_tpl_vars['MODULE']; ?>
','<?php echo $this->_tpl_vars['IMAGE_PATH']; ?>
');" />
	</td>
   </tr>




<!--
All these details are stored in the first element in the array with the index name as final_details 
so we will get that array, parse that array and fill the details
-->
<?php $this->assign('FINAL', $this->_tpl_vars['ASSOCIATEDPRODUCTS']['1']['final_details']); ?>

   <!-- Product Details Final Total Discount, Tax and Shipping&Hanling  - Starts -->
   <tr valign="top">
	<td width="88%" colspan="2" class="crmTableRow small lineOnTop" align="right"><b><?php echo $this->_tpl_vars['APP']['LBL_NET_TOTAL']; ?>
</b></td>
	<td width="12%" id="netTotal" class="crmTableRow small lineOnTop" align="right">0.00</td>
   </tr>

   <tr valign="top">
	<td class="crmTableRow small lineOnTop" width="60%" style="border-right:1px #dadada;">&nbsp;</td>
	<td class="crmTableRow small lineOnTop" align="right">
		(-)&nbsp;<b><a href="javascript:doNothing();" onClick="displayCoords(this,'discount_div_final','discount_final','1')"><?php echo $this->_tpl_vars['APP']['LBL_DISCOUNT']; ?>
</a>

		<!-- Popup Discount DIV -->
		<div class="discountUI" id="discount_div_final">
			<input type="hidden" id="discount_type_final" name="discount_type_final" value="<?php echo $this->_tpl_vars['FINAL']['discount_type_final']; ?>
">
			<table width="100%" border="0" cellpadding="5" cellspacing="0" class="small">
			   <tr>
				<td id="discount_div_title_final" nowrap align="left" ></td>
				<td align="right"><img src="<?php echo vtiger_imageurl('close.gif', $this->_tpl_vars['THEME']); ?>
" border="0" onClick="fnHidePopDiv('discount_div_final')" style="cursor:pointer;"></td>
			   </tr>
			   <tr>
				<td align="left" class="lineOnTop"><input type="radio" name="discount_final" checked onclick="setDiscount(this,'_final'); calcGroupTax(); calcTotal();">&nbsp; <?php echo $this->_tpl_vars['APP']['LBL_ZERO_DISCOUNT']; ?>
</td>
				<td class="lineOnTop">&nbsp;</td>
			   </tr>
			   <tr>
				<td align="left"><input type="radio" name="discount_final" onclick="setDiscount(this,'_final');  calcTotal(); calcGroupTax();" <?php echo $this->_tpl_vars['FINAL']['checked_discount_percentage_final']; ?>
>&nbsp; % <?php echo $this->_tpl_vars['APP']['LBL_OF_PRICE']; ?>
</td>
				<td align="right"><input type="text" class="small" size="5" id="discount_percentage_final" name="discount_percentage_final" value="<?php echo $this->_tpl_vars['FINAL']['discount_percentage_final']; ?>
" <?php echo $this->_tpl_vars['FINAL']['style_discount_percentage_final']; ?>
 onBlur="setDiscount(this,'_final'); calcGroupTax(); calcTotal();">&nbsp;%</td>
			   </tr>
			   <tr>
				<td align="left" nowrap><input type="radio" name="discount_final" onclick="setDiscount(this,'_final');  calcTotal(); calcGroupTax();" <?php echo $this->_tpl_vars['FINAL']['checked_discount_amount_final']; ?>
>&nbsp;<?php echo $this->_tpl_vars['APP']['LBL_DIRECT_PRICE_REDUCTION']; ?>
</td>
				<td align="right"><input type="text" id="discount_amount_final" name="discount_amount_final" size="5" value="<?php echo $this->_tpl_vars['FINAL']['discount_amount_final']; ?>
" <?php echo $this->_tpl_vars['FINAL']['style_discount_amount_final']; ?>
 onBlur="setDiscount(this,'_final');  calcGroupTax(); calcTotal();"></td>
			   </tr>
			</table>
		</div>
		<!-- End Div -->

	</td>
	<td id="discountTotal_final" class="crmTableRow small lineOnTop" align="right"><?php echo $this->_tpl_vars['FINAL']['discountTotal_final']; ?>
</td>
   </tr>


   <!-- Group Tax - starts -->
   <tr id="group_tax_row" valign="top" class="TaxHide">
	<td class="crmTableRow small lineOnTop" style="border-right:1px #dadada;">&nbsp;</td>
	<td class="crmTableRow small lineOnTop" align="right">
		(+)&nbsp;<b><a href="javascript:doNothing();" onClick="displayCoords(this,'group_tax_div','group_tax_div_title','');  calcTotal(); calcGroupTax();" ><?php echo $this->_tpl_vars['APP']['LBL_TAX']; ?>
</a></b>

				<!-- Pop Div For Group TAX -->
				<div class="discountUI" id="group_tax_div">
					<table width="100%" border="0" cellpadding="5" cellspacing="0" class="small">
					   <tr>
						<td id="group_tax_div_title" colspan="2" nowrap align="left" ></td>
						<td align="right"><img src="<?php echo vtiger_imageurl('close.gif', $this->_tpl_vars['THEME']); ?>
" border="0" onClick="fnHidePopDiv('group_tax_div')" style="cursor:pointer;"></td>
					   </tr>

					<?php $_from = $this->_tpl_vars['FINAL']['taxes']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['group_tax_loop'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['group_tax_loop']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['loop_count'] => $this->_tpl_vars['tax_detail']):
        $this->_foreach['group_tax_loop']['iteration']++;
?>

					   <tr>
						<td align="left" class="lineOnTop">
							<input type="text" class="small" size="5" name="<?php echo $this->_tpl_vars['tax_detail']['taxname']; ?>
_group_percentage" id="group_tax_percentage<?php echo $this->_foreach['group_tax_loop']['iteration']; ?>
" value="<?php echo $this->_tpl_vars['tax_detail']['percentage']; ?>
" onBlur="calcTotal()">&nbsp;%
						</td>
						<td align="center" class="lineOnTop"><?php echo $this->_tpl_vars['tax_detail']['taxlabel']; ?>
</td>
						<td align="right" class="lineOnTop">
							<input type="text" class="small" size="6" name="<?php echo $this->_tpl_vars['tax_detail']['taxname']; ?>
_group_amount" id="group_tax_amount<?php echo $this->_foreach['group_tax_loop']['iteration']; ?>
" style="cursor:pointer;" value="0.00" readonly>
						</td>
					   </tr>

					<?php endforeach; endif; unset($_from); ?>
					<input type="hidden" id="group_tax_count" value="<?php echo $this->_foreach['group_tax_loop']['iteration']; ?>
">

					</table>

				</div>
				<!-- End Popup Div Group Tax -->


	</td>
	<td id="tax_final" class="crmTableRow small lineOnTop" align="right"><?php echo $this->_tpl_vars['FINAL']['tax_totalamount']; ?>
</td>
   </tr>
   <!-- Group Tax - ends -->



   <tr valign="top">
	<td class="crmTableRow small" style="border-right:1px #dadada;">&nbsp;</td>
	<td class="crmTableRow small" align="right">
		(+)&nbsp;<b><?php echo $this->_tpl_vars['APP']['LBL_SHIPPING_AND_HANDLING_CHARGES']; ?>
 </b>
	</td>
	<td class="crmTableRow small" align="right">
		<input id="shipping_handling_charge" name="shipping_handling_charge" type="text" class="small" style="width:40px" align="right" value="<?php echo $this->_tpl_vars['FINAL']['shipping_handling_charge']; ?>
" onBlur="calcSHTax();">
	</td>
   </tr>


   <tr valign="top">
	<td class="crmTableRow small" style="border-right:1px #dadada;">&nbsp;</td>
	<td class="crmTableRow small" align="right">
		(+)&nbsp;<b><a href="javascript:doNothing();" onClick="displayCoords(this,'shipping_handling_div','sh_tax_div_title',''); calcSHTax();" ><?php echo $this->_tpl_vars['APP']['LBL_TAX_FOR_SHIPPING_AND_HANDLING']; ?>
 </a></b>

				<!-- Pop Div For Shipping and Handlin TAX -->
				<div class="discountUI" id="shipping_handling_div">
					<table width="100%" border="0" cellpadding="5" cellspacing="0" class="small">
					   <tr>
						<td id="sh_tax_div_title" colspan="2" nowrap align="left" ></td>
						<td align="right"><img src="<?php echo vtiger_imageurl('close.gif', $this->_tpl_vars['THEME']); ?>
" border="0" onClick="fnHidePopDiv('shipping_handling_div')" style="cursor:pointer;"></td>
					   </tr>

					<?php $_from = $this->_tpl_vars['FINAL']['sh_taxes']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['sh_loop'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['sh_loop']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['loop_count'] => $this->_tpl_vars['tax_detail']):
        $this->_foreach['sh_loop']['iteration']++;
?>

					   <tr>
						<td align="left" class="lineOnTop">
							<input type="text" class="small" size="3" name="<?php echo $this->_tpl_vars['tax_detail']['taxname']; ?>
_sh_percent" id="sh_tax_percentage<?php echo $this->_foreach['sh_loop']['iteration']; ?>
" value="<?php echo $this->_tpl_vars['tax_detail']['percentage']; ?>
" onBlur="calcSHTax()">&nbsp;%
						</td>
						<td align="center" class="lineOnTop"><?php echo $this->_tpl_vars['tax_detail']['taxlabel']; ?>
</td>
						<td align="right" class="lineOnTop">
							<input type="text" class="small" size="4" name="<?php echo $this->_tpl_vars['tax_detail']['taxname']; ?>
_sh_amount" id="sh_tax_amount<?php echo $this->_foreach['sh_loop']['iteration']; ?>
" style="cursor:pointer;" value="0.00" readonly>
						</td>
					   </tr>

					<?php endforeach; endif; unset($_from); ?>
					<input type="hidden" id="sh_tax_count" value="<?php echo $this->_foreach['sh_loop']['iteration']; ?>
">

					</table>
				</div>
				<!-- End Popup Div for Shipping and Handling TAX -->

	</td>
	<td id="shipping_handling_tax" class="crmTableRow small" align="right"><?php echo $this->_tpl_vars['FINAL']['shtax_totalamount']; ?>
</td>
   </tr>


   <tr valign="top">
	<td class="crmTableRow small" style="border-right:1px #dadada;">&nbsp;</td>
	<td class="crmTableRow small" align="right">
		<?php echo $this->_tpl_vars['APP']['LBL_ADJUSTMENT']; ?>

		<select id="adjustmentType" name="adjustmentType" class=small onchange="calcTotal();">
			<option value="+"><?php echo $this->_tpl_vars['APP']['LBL_ADD_ITEM']; ?>
</option>
			<option value="-"><?php echo $this->_tpl_vars['APP']['LBL_DEDUCT']; ?>
</option>
		</select>
	</td>
	<td class="crmTableRow small" align="right">
		<input id="adjustment" name="adjustment" type="text" class="small" style="width:40px" align="right" value="<?php echo $this->_tpl_vars['FINAL']['adjustment']; ?>
" onBlur="calcTotal();">
	</td>
   </tr>


   <tr valign="top">
	<td class="crmTableRow big lineOnTop" style="border-right:1px #dadada;">&nbsp;</td>
	<td class="crmTableRow big lineOnTop" align="right"><b><?php echo $this->_tpl_vars['APP']['LBL_GRAND_TOTAL']; ?>
</b></td>
	<td id="grandTotal" name="grandTotal" class="crmTableRow big lineOnTop" align="right"><?php echo $this->_tpl_vars['FINAL']['grandTotal']; ?>
</td>
   </tr>
</table>

		<input type="hidden" name="totalProductCount" id="totalProductCount" value="<?php echo $this->_tpl_vars['row_no']; ?>
">
		<input type="hidden" name="subtotal" id="subtotal" value="">
		<input type="hidden" name="total" id="total" value="">
</td></tr>
<!-- Upto this Added to display the Product Details -->

<?php $_from = $this->_tpl_vars['ASSOCIATEDPRODUCTS']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['row_no'] => $this->_tpl_vars['data']):
?>
	<!-- This is added to call the function calcCurrentTax which will calculate the tax amount from percentage -->
	<?php $_from = $this->_tpl_vars['data']['taxes']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['tax_row_no'] => $this->_tpl_vars['tax_data']):
?>
		<?php $this->assign('taxname', ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['tax_data']['taxname'])) ? $this->_run_mod_handler('cat', true, $_tmp, '_percentage') : smarty_modifier_cat($_tmp, '_percentage')))) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['row_no']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['row_no']))); ?>
			<script>calcCurrentTax('<?php echo $this->_tpl_vars['taxname']; ?>
',<?php echo $this->_tpl_vars['row_no']; ?>
,<?php echo $this->_tpl_vars['tax_row_no']; ?>
);</script>
	<?php endforeach; endif; unset($_from); ?>
	<?php $this->assign('entityIndentifier', ((is_array($_tmp='entityType')) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['row_no']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['row_no']))); ?>
	<?php if ($this->_tpl_vars['MODULE'] == 'Invoice' && $this->_tpl_vars['data'][$this->_tpl_vars['entityIndentifier']] != 'Services'): ?>
		<script>stock_alert('<?php echo $this->_tpl_vars['row_no']; ?>
');</script>
	<?php endif; ?>
<?php endforeach; endif; unset($_from); ?>


<!-- Added to calculate the tax and total values when page loads -->
<script>decideTaxDiv();</script>
<script>calcTotal();</script>
<script>calcSHTax();</script>
<!-- This above div is added to display the tax informations --> 

