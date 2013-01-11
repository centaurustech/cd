{*Smarty *}

<div id="basket">
  <div id="icon"></div>
  <div id="post-icon">
    <div id="texts">
      Nombre(s) d'article(s): <span class="num">{$cart_qties}</span><br />
      Montant total: <span class="num">
	{assign var='blockuser_cart_flag' value='Cart::BOTH_WITHOUT_SHIPPING'|constant}
	{convertPrice price=$cart->getOrderTotal(false, $blockuser_cart_flag)}
      </span>
    </div>
    <div id="button">
      <a href="{$link->getPageLink("$order_process.php", true)}" id="label">VOIR MON PANIER</a>
    </div>
    <div id="pub">
      Les frais de port vous sont offerts dès <span class="num">100€</span> d'achat
    </div>
  </div>
</div>
