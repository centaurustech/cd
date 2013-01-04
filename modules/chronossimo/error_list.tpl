<div class="error">
	<span style="float:right"><a id="hideError" href=""><img alt="X" src="/modules/chronossimo/img/close.png" /></a></span>
	<img src="/modules/chronossimo/img/error.png" />{$error_msg}<br /><br />
	
	<p>Détails :</p>
	
	<ul>
		{foreach from=$errors item=error}
		<li>{$error}</li>
		{/foreach}
	</ul>
	


{$error_end}
</div>
{literal}
<style>
	.error {
		color: #383838;
		font-weight: 700;
		margin: 0 0 10px 0;
		line-height: 20px;
		padding: 10px 15px;
		border: 1px solid #EC9B9B;
		background-color: #FAE2E3;
		border-radius: 20px;
	}
</style>
{/literal}
