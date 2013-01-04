{* Smarty *}

<div id="menu_bar">
  <div id="menus">
    
    {* ACCUEIL *}
    <a href="http://{ConfigurationCore::get('PS_SHOP_DOMAIN')}" class="menu">Accueil</a>

    {* CATEGORIES *}
    {foreach from=Category::getHomeCategories(1) item=categ name=categs}
    
    {if $categ.name == 'Direct fournisseur'}
    <a href="{$link->getCategoryLink($categ.id_category, Category::getLinkRewrite($categ.id_category, 2), 2)}" class="menu2">&nbsp;</a>
    {else}
	<div class="sep"></div>
    <a href="{$link->getCategoryLink($categ.id_category, Category::getLinkRewrite($categ.id_category, 2), 2)}" class="menu">{$categ.name}</a>
    {/if}
    {/foreach}
  </div>
</div>
