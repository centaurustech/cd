<link type="text/css" rel="stylesheet" href="../modules/chronossimo/chronossimo.css" />
<div id="chronossimo_logo">
	<img src="../modules/chronossimo/logo_chronossimo.png" alt="Logo Chronossimo" title="Logo Chronossimo">
</div>
<div id="utiliser_chronossimo"><a href="index.php?tab=AdminChronossimo&token={$tokenAdminChronossimo}" title="{l s='Module accessible depuis l\'onglet des commandes'}" >{l s='Cliquez ici pour lancer le module'}</a></div>

	<form name="form_chronossimo_config" action="" method="post">
		
			<fieldset>
				<legend>{l s='Configuration de Chronossimo'}</legend>
				
				<label>{l s='Adresse email'}<sup>*</sup> : </label>
				<div class="margin-form">
					<input type="text" name="email" value="{$email}" />
				</div>
				
				<label>{l s='Mot de passe'}<sup></sup> : </label>
				<div class="margin-form">
					<input type="password" name="mdp" value="{$mdp}" />
				</div>
				<fieldset>
				<legend>{l s='Informations de facturation'}</legend>
				<div class="margin-form">{l s='Informations de facturation de votre entreprise'}</div>
					<label>{l s='Société'}<sup>*</sup> : </label>
						<div class="margin-form">
							<input type="text" name="fact_societe" value="{$fact_societe}" />
						</div>
					<label>{l s='SIRET'}<sup>*</sup> : </label>
						<div class="margin-form">
							<input type="text" name="fact_siret" value="{$fact_siret}" />
						</div>
					<label>{l s='Numéro de TVA'} : </label>
						<div class="margin-form">
							<input type="text" name="fact_tva" value="{$fact_tva}" />
						</div>
					<label>{l s='Adresse (ligne 1)'}<sup>*</sup> : </label>
						<div class="margin-form">
							<input type="text" name="fact_addr1" value="{$fact_address1}" />
						</div>
					<label>{l s='Adresse (ligne 2)'} : </label>
						<div class="margin-form">
							<input type="text" name="fact_addr2" value="{$fact_address2}" />
						</div>
					<label>{l s='Code postal'}<sup>*</sup> : </label>
						<div class="margin-form">
							<input type="text" name="fact_cp" value="{$fact_postal_code}" />
						</div>
					<label>{l s='Ville'}<sup>*</sup> : </label>
						<div class="margin-form">
							<input type="text" name="fact_ville" value="{$fact_city}" />
						</div>
					<label>{l s='Pays'}<sup>*</sup> : </label>
						<div class="margin-form">
							<input type="text" name="fact_pays" value="{$fact_country}" />
						</div>
					<label>{l s='Téléphone'} : </label>
						<div class="margin-form">
							<input type="text" name="fact_tel" value="{$fact_phone}" />
						</div>
				</fieldset>
				<br />
				<fieldset>
				<legend>{l s='Adresse d\'expédition des colis'}</legend>
				<div class="margin-form">{l s='Adresse de retour en cas de non livraison visible par le destinataire'}</div>
				
					<label>{l s='Sexe'} : </label>
						<div class="margin-form">
							<input type="radio" size="33" name="exp_gender" id="gender_1" value="1" {if $exp_gender!=2}checked="checked" {/if}/>
							<label class="t" for="gender_1"> {l s='Homme'}</label>
							<input type="radio" size="33" name="exp_gender" id="gender_2" value="2" {if $exp_gender==2}checked="checked" {/if}/>
							<label class="t" for="gender_2"> {l s='Femme'}</label>
						</div>
				
					<label>{l s='Nom'}<sup>*</sup> : </label>
						<div class="margin-form">
							<input type="text" name="exp_nom" value="{$exp_nom}" />
						</div>
					<label>{l s='Prénom'}<sup>*</sup> : </label>
						<div class="margin-form">
							<input type="text" name="exp_prenom" value="{$exp_prenom}" />
						</div>
					<label>{l s='Société'}<sup>*</sup> : </label>
						<div class="margin-form">
							<input type="text" name="exp_societe" value="{$exp_societe}" />
						</div>
					<label>{l s='Adresse (ligne 1)'}<sup>*</sup> : </label>
						<div class="margin-form">
							<input type="text" name="exp_addr1" value="{$exp_address1}" />
						</div>
					<label>{l s='Adresse (ligne 2)'} : </label>
						<div class="margin-form">
							<input type="text" name="exp_addr2" value="{$exp_address2}" />
						</div>
					<label>{l s='Code postal'}<sup>*</sup> : </label>
						<div class="margin-form">
							<input type="text" name="exp_cp" value="{$exp_postal_code}" />
						</div>
					<label>{l s='Ville'}<sup>*</sup> : </label>
						<div class="margin-form">
							<input type="text" name="exp_ville" value="{$exp_city}" />
						</div>
					<label>{l s='Pays'}<sup>*</sup> : </label>
						<div class="margin-form">
							<input type="text" name="exp_pays" value="{$exp_country}" />
						</div>
					<label>{l s='Téléphone'}<sup>*</sup> : </label>
						<div class="margin-form">
							<input type="text" name="exp_tel" value="{$exp_phone}" />
						</div>
				</fieldset>
				<br />
				<fieldset>
				<legend>{l s='Informations de paiement'}</legend>
				<div class="margin-form">{l s='Remplissez les informations de votre moyen de paiement'}</div>
				<div class="margin-form">{l s='Si vous ne souhaitez pas sauvegarder vos informations de paiement, ils seront demandé à chaque paiement'}</div>
					<label>{l s='Nom'} : </label>
						<div class="margin-form">
							<input type="text" name="cb_name" value="{$cb_name}" />
						</div>
					<label>{l s='Numéro de carte bleu'} : </label>
						<div class="margin-form">
							<input type="text" name="cb_num" value="{$cb_num}" />
						</div>
					<label>{l s='Code de vérification'} : </label>
						<div class="margin-form">
							<input type="text" name="cb_verif" value="{$cb_verif}" />
						</div>
					<label>{l s='Date de validité'} : </label>
						<div class="margin-form">
							<input class= "cb_validite" type="text" name="cb_month" value="{if $cb_month}{$cb_month}{else}MM{/if}" /> / <input class= "cb_validite_year" type="text" name="cb_year" value="{if $cb_year}{$cb_year}{else}AAAA{/if}" />
						</div>
				</fieldset>
	<br />
	
				<fieldset class="fieldset_inside">
				<legend>{l s='Accepter les transporteurs suivant'}</legend>
				<div class="margin-form">{l s='Choisissez le transporteur des commandes à expédier'}</div>
							<label><input type="checkbox" name="carriers_all" value="1" {if $Carriers_all}checked="checked"{/if} /> {l s='Tous'}</label><br /><br />
						{foreach from=$Carriers item=carrier}
							<label><input type="checkbox" name="carriers[]" value="{$carrier.id_carrier}" {if in_array($carrier.id_carrier, $Carriers_used)}checked="checked"{/if} /> {$carrier.name}</label><br />
						{/foreach}
						
				
				</fieldset>
				
				<br />
				<fieldset class="fieldset_inside">
				<legend>{l s='Paramètres de Chronossimo'}</legend>
				<div class="margin-form">{l s='Le statut des commandes en cours de livraison seront automatiquement mis sur "Livré" une fois la livraison terminé'}</div>
						<label><input type="checkbox" name="auto_update_order" value="1" {if $auto_update_order}checked="checked"{/if}/> {l s='Mise à jour automatique du statut des commandes'}</label><br />
						{*
							<div class="margin-form">{l s='Choisissez le statut des commandes que peut modifier Chronossimo'}</div>
						{foreach from=$OrderState item=state}
							<label><input type="checkbox" name="ordertoUpdate[]" value="{$state.id_order_state}" /> {$state.name}</label><br />
						{/foreach}
						*}
				<br /><br />
				<div class="margin-form">{l s='Permet de générer les bordereaux dans un fichier séparé pour faciliter l\'impression sur des étiquettes autocollantes'}</div>
						<label><input type="checkbox" name="split_bordereaux" value="1" {if $split_bordereaux}checked="checked"{/if}/> {l s='Générer les borderaux dans un fichier séparé'}</label><br />
						
				<br /><br />
				<div class="margin-form">{l s='Utiliser une connexion sécurisé SSL pour tous les échanges de données'}</div>
						<label><input type="checkbox" name="ssl" value="1" {if $ssl}checked="checked"{/if}/> {l s='Utiliser SSL'}</label><br />
				
				</fieldset>

				
				<div class="margin-form"><sup>*</sup>{l s='Informations obligatoires'}</div>
			<input type="submit" value="{l s='Sauvegarder la configuration'}" name="submitTwengaLogin" class="button"/>
			
			</fieldset>
			</form><br />
