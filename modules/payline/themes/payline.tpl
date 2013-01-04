{if isset($paylineProduction) AND !$paylineProduction}
<div class="error" style="margin-left:15px;">
	{l s='The payment Payline is in test mode.' mod='payline'}
</div>
{/if}
{if isset($cardData) AND $paylineWallet AND $cardData}
<div class="paylineSpecificBlock">
	<img src="{$base_dir}modules/payline/img/payline-by-monext-fr-rvb.png" alt="" />
	{$paylineObj->wallet_title}
	<div class="cardsWallet">
	{foreach from=$cardData item=card name=myLoop}
		<ul class="cardWallet {if $smarty.foreach.myLoop.last}last_item{elseif $smarty.foreach.myLoop.first}first_item{/if} {if $smarty.foreach.myLoop.index % 2}alternate_item{else}item{/if}">
			<li class="card_title">
				<input type="radio" name="card" value="{$card.cardInd}" onclick="javascript:document.location.href='{$base_dir}modules/payline/redirect.php?cardInd={$card.cardInd}&type={$card.type}'"/> {l s='Card' mod='payline'} {$card.cardInd}
			</li>
			<li class="card_type">{l s='Type' mod='payline'} <b>{$card.type}</b></li>
			<li class="card_name">{l s='Holder' mod='payline'} <b>{$card.firstName} {$card.lastName}</b></li>
			<li class="card_number">{l s='Card number' mod='payline'} <b>{$card.number}</b></li>
			<li class="card_expiration">{l s='Expiration date' mod='payline'} <b>{$card.expirationDate}</b></li>
		</ul>
	{/foreach}
	<p class="clear"> </p>
	</div>
</div>
{/if}
<!-- WEB CASH PAYMENT //-->
{if isset($cards) AND $paylineWebcash}
	{foreach from=$cards item=card name=cards}
<p class="payment_module">
	<a href="javascript:$('#contractNumber').val('{$card.contract}');$('#mode').val('webCash');$('#type').val('{$card.type}');document.forms['WebPaymentPayline'].submit();">
		<img src="{$base_dir}modules/payline/img/{$card.type}.gif" alt="{$card.type}" title="{$card.type}" />{l s='Payment' mod='payline'} {$card.type}<img src="{$base_dir}modules/payline/img/payline-by-monext-fr-rvb.png" alt="{l s='Click here to pay with Payline gateway' mod='payline'}" style="float:right;" />
	</a>
</p>
	{/foreach}
{/if}
<!-- END WEB CASH PAYMENT //-->

<!-- PAYLINE DIRECT //-->
{if $paylineDirect}
	<div class="paylineSpecificBlock">
		<img src="{$base_dir}modules/payline/img/payline-by-monext-fr-rvb.png" alt="" />
		{$paylineObj->direct_title}
		<div class="cardsWallet">
			<form action="{$base_dir}modules/payline/redirect.php" method="post" name="directPaymentPayline" id="directPaymentPayline" class="payline-form">
				<input type="hidden" name="directmode" id="directmode" value="direct" />
				<div>
					{foreach from=$cardsNX item=card name=cards}
							<input type="radio" name="contractNumber" value="{$card.contract}" /> <img src="{$base_dir}modules/payline/img/{$card.type}.gif" alt="{$card.type}" title="{$card.type}" />
					{/foreach}
				</div>
				<label for="holder">{l s='Holder' mod='payline'}</label>
				<input type="text" name="holder" value="" id="holder" size="30" />
				<label for="cardNumber">{l s='Card number' mod='payline'}</label>
				<input type="text" name="cardNumber" value="" id="cardNumber" size="30" maxlength="16" />
				<label for="ExpirationDate">{l s='Expiration date' mod='payline'}</label>
				<select name="monthExpire" style="width:60px;">
					<option value="">{l s='Month'}</option>
					{section name=foo start=1 loop=13 step=1}
  						<option value="{$smarty.section.foo.index}">{$smarty.section.foo.index}</option>
					{/section}
				</select>
				<select name="yearExpire" style="width:60px;">
					<option value="">{l s='Year'}</option>
					{assign var=year value=$smarty.now|date_format:"%Y"}
					{section name=foo start=$year loop=$year+7 step=1}
  						<option value="{$smarty.section.foo.index}">{$smarty.section.foo.index}</option>
					{/section}
				</select>
				<label for="crypto">{l s='Verification number' mod='payline'}</label>
				<input type="text" name="crypto" value="" id="crypto" size="5" maxlength="4" />
				<p class="clear"> </p>
				<input type="submit" name="submit" value="{l s='Order now' mod='payline'}" class="button" />
			</form>
		</div>
	</div>
	<p class="clear"> </p>
{/if}
<!-- END PAYLINE DIRECT //-->

<!-- RECURRING PAYMENT //-->
{if isset($cards) AND $paylineRecurring}
	<div class="paylineSpecificBlock">
		<div id="recurringTitle">
			<b>{$paylineObj->recurring_title}</b><br/>
			{$paylineObj->recurring_subtitle}
		</div>
		<div id="recurringCard">
		{foreach from=$cardsNX item=card name=cards}
			<a href="javascript:$('#contractNumber').val('{$card.contract}');$('#mode').val('recurring');$('#type').val('{$card.type}');document.forms['WebPaymentPayline'].submit();" class="recurringLink">
				<img src="{$base_dir}modules/payline/img/{$card.type}.gif" alt="{$card.type}" title="{$card.type}" />
			</a>
		{/foreach}
		</div>
		<div id="recurringLogo">
			<img src="{$base_dir}modules/payline/img/payline-by-monext-fr-rvb.png" alt="{l s='Click here to pay with Payline gateway' mod='payline'}" />
		</div>
		<br style="clear:both" />
	</div>
{/if}
<!-- END RECURRING PAYMENT //-->

{if isset($payline)}
	{$payline}
{/if}
<script type="text/javascript">
{literal}
	$(document).ready(function(){
		$('#directPaymentPayline').bind('submit',function(){
		
			$('#directPaymentPayline input').each(function(){
				$(this).removeClass('invalid');
			});
			$('#directPaymentPayline select').each(function(){
				$(this).removeClass('invalid');
			});
			
			var allow = false;
			
			var contractNumber = $('input[name="contractNumber"]:checked').val();
			if(typeof(contractNumber) == 'undefined')
				$('input[name="contractNumber"]').each(function(){
					$(this).addClass('invalid');
				});	
			
			var holder = $('#holder').val();
			if(holder.length <= 0)
				$('#holder').addClass('invalid');
			
			var cardNumber = $('#cardNumber').val();
			var digitOnlyRegex = /[^0-9]/;
			if(cardNumber.length <= 0 || cardNumber.length < 16 || digitOnlyRegex.test(cardNumber))
				$('#cardNumber').addClass('invalid');
			
			var monthExpire = $('select[name="monthExpire"] option:selected').val();
			if(monthExpire.length <= 0 || digitOnlyRegex.test(monthExpire))
				$('select[name="monthExpire"]').addClass('invalid');
				
			var yearExpire = $('select[name="yearExpire"] option:selected').val();
			if(yearExpire.length <= 0 || digitOnlyRegex.test(yearExpire))
				$('select[name="yearExpire"]').addClass('invalid');
			
			var crypto = $('#crypto').val();
			if(crypto.length <= 0 || crypto.length < 3 || digitOnlyRegex.test(crypto))
				$('#crypto').addClass('invalid');
				
			allow = (($('#directPaymentPayline').find('.invalid').length > 0)? false : true);
				
			return allow;
		});
	});
{/literal}
</script>