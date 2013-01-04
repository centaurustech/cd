<h3>{l s='Affranchissement des colis effectué'}</h3>
{if $orders}
<table class="table" cellspacing="0" cellpadding="0">
	<thead>
		<tr class="nodrag nodrop">
			<th>{l s='ID'}</th>
			<th>{l s='Client'}</th>
			<!--<th>{l s='Statut'}</th>-->
			<th>{l s='Total'}</th>
			<th>{l s='Date'}</th>
			<th>{l s='Poids'}</th>
			<th>{l s='Pays'}</th>
			
			<th>{l s='Signature'}</th>
			<th>{l s='Assurance'}</th>
			<th>{l s='Volumineux'}</th>
			<th>{l s='Actions'}</th>
			<th>{l s='Tarif'}</th>
		</tr>
	</thead>
	<tbody>
		{foreach from=$orders item=order}
		<tr>
			<td>{$order.order_id}</td>
			<td>{$order.adresse.firstname} {$order.adresse.lastname}</td>
			<!--<td>{$order.statut}</td>-->
			<td>{$order.total} €</td>
			<td>{$order.order_fields.date_add}</td>
			<td>{$order.poids} {$order.unite_poids}</td>
			<td>{$order.pays}</td>
			
			<td><input type="checkbox" name="signature[]" disabled="disabled" value="{$order.order_id}"{if $order.signature} checked="checked"{/if} /></td>
			<td><input type="checkbox" name="assurance[]" disabled="disabled" value="{$order.order_id}"{if $order.assurance} checked="checked"{/if} /></td>
			<td><input type="checkbox" name="volumineux[]" disabled="disabled" value="{$order.order_id}"{if $order.volumineux} checked="checked"{/if} /></td>
			<td><a href="{$order.deliveryLink}" target="_blank" ><img src="../img/admin/delivery.gif" title="{l s='Suivi de colis'}" /></a><a href="pdf.php?id_order={$order.order_id}&pdf" target="_blank" ><img src="../img/admin/tab-invoice.gif" title="{l s='Voir la facture'}" /></a> <a href="index.php?tab=AdminOrders&id_order={$order.order_id}&vieworder&token={$tokenAdminOrders}" target="_blank" ><img src="../img/admin/details.gif" title="{l s='Voir le détail de la commande'}" /></a></td>
			<td>{$order.tarif}</td>
		</tr>
		{/foreach}

	</tbody>
</table>
{/if}

<br /><br />
<h3>{l s='Total'}: {$total} €</h3>
<br />
{if $pdf}

<a class="pdfGlobal" href="{$pdf}" target="_blank"><img src="../modules/chronossimo/pdf.png" width="42" height="42" title="{l s='Imprimer le document'}" />{l s='Imprimer le document'}</a>


<div class="detailsAffranchissement">
	<a href="../modules/chronossimo/pdf.php?action=factures&security_key={$security_key}&orders={foreach from=$orders item=order}{$order.order_id},{/foreach}" target="_blank" ><img src="../img/admin/pdf.gif" title="{l s='Toutes les factures'}" /> {l s='Télécharger l\'ensemble des factures des commandes'}</a> <br />
	{if $pdf_bordereaux}<a href="{$pdf_bordereaux}" target="_blank" ><img src="../img/admin/pdf.gif" title="{l s='Voir les bordereaux'}" /> {l s='Télécharger uniquement les bordereaux'}</a><span class="petit">(pour impression sur étiquettes autocollantes etc)</span><br />{/if}
	{if $pdf_sans_bordereaux}<a href="{$pdf_sans_bordereaux}" target="_blank" ><img src="../img/admin/pdf.gif" title="{l s='Voir la facture'}" /> {l s='Télécharger le document sans les borderaux'}</a><span class="petit">(Facture, formulaires CN23 etc)</span> <br />{/if}
</div>
{else}
<div class="error">
	<span style="float:right"><a id="hideError" href=""><img alt="X" src="../img/admin/close.png" /></a></span>
	<img src="../img/admin/error2.png" />{l s='Fichier PDF inaccessible'}<br />
</div>
{/if}

<br /><br />
{literal}
<style>
.pdfGlobal {
	font-weight: bold;
}
	.pdfGlobal img {
		margin-right: 10px;
	}
	.detailsAffranchissement {
		background-color: #E2EBEE;
		border: 1px solid #999;
		font-family: Trebuchet,Arial,Helvetica,sans-serif;
		font-size: 13px;
		margin-top: 40px;
		margin-bottom: 20px;
		padding: 5px;
	}
	.detailsAffranchissement .petit {
		margin-left: 5px;
		font-size: 10px;
	}
</style>
{/literal}
