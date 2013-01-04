<link type="text/css" rel="stylesheet" href="../modules/chronossimo/chronossimo.css" />


{if $smarty.post}
<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('ok').'" />{l s='Mise à jour effectué'}</div>
{/if}


<div style="text-align: center;">
<div id="chronossimo_logo">
	<img src="../modules/chronossimo/logo_chronossimo.png" alt="Logo Chronossimo" title="Logo Chronossimo">
</div>

	
	<form name="form_orderToSend" action="index.php?tab=AdminChronossimo&token={$tokenAdminChronossimo}&action=1" method="post">	
			<fieldset>
				<legend>{l s='Commande à expédier en colissimo'}</legend>

				<label>{l s='Statut des commandes'} : </label>
				<div class="margin-form">
					<select name="order_statut_id">
						{foreach from=$OrderState item=state}
							<option value="{$state.id_order_state}"{if $state.id_order_state==$last_order_state} selected="selected"{/if}>{$state.name}</option>
						{/foreach}
					</select>
				</div><!-- .margin-form -->
				<div class="margin-form">{l s='Statut actuel des commandes que vous souhaitez expédier'}</div>
				<br />
				
				<div class="options">
					<label><input type="checkbox" name="signature_all" value="1" /> {l s='Remise contre signature'}</label><br />
					<label><input type="checkbox" name="assurance_all" value="1" /> {l s='Assurer la valeur du colis'}</label><br />
					<label><input type="checkbox" name="volumineux_all" value="1" /> {l s='Colis Volumineux'}</label><br />
				</div>
				
			<input type="submit" value="{l s='Lister les commandes'}" name="submit" class="button"/>
			</fieldset>
			</form><br />
			
			
			<p><a href="index.php?tab=AdminModules&configure=chronossimo&token={$tokenConfigAdminChronossimo}&tab_module=shipping_logistics&module_name=chronossimo">{l s='Editer la configuration'}</a> | <a href="index.php?tab=AdminChronossimo&token={$tokenAdminChronossimo}&action=100">{l s='Historique des expéditions'}</a></p>
</div>
