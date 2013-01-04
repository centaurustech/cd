<?php

class Link extends LinkCore
{
	/**
	  * Return the correct link for product/category/supplier/manufacturer
	  *
	  * @param mixed $id_product Can be either the object or the ID only
	  * @param string $alias Friendly URL (only if $id_OBJ is the object)
	  * @return string link
	  */
	
	/**
	  * getProductLink updated by 202 ecommerce from Prestashop 1.4.6.2
	  * Remove category within product URL
	  * Do not redistribute, please link to :
	  * http://www.202-ecommerce.com/2012/01/reecriture-url-prestashop-performances
	  * Tested on Prestashop 1.4
	*/
	public function getProductLink($id_product, $alias = NULL, $category = NULL, $ean13 = NULL, $id_lang = NULL)
	{
		global $cookie;
		if (is_object($id_product))
		{		
			$link = '';
			if ($this->allow == 1)
			{
				$link .= (_PS_BASE_URL_.__PS_BASE_URI__.$this->getLangLink((int)$id_lang));
				
				/*if (isset($id_product->category) AND !empty($id_product->category) AND $id_product->category != 'home')
					$link .= $id_product->category.'/';
				else
					$link .= '';*/

				$link .= (int)$id_product->id.'-';
				if (is_array($id_product->link_rewrite))
					$link.= $id_product->link_rewrite[(int)$cookie->id_lang];
				else 
				 	$link.= $id_product->link_rewrite;
				if ($id_product->ean13)
					$link .='-'.$id_product->ean13;
				else
					$link .= '';

				$link .= '.html';
			}
			else 
			{
				$link .= (_PS_BASE_URL_.__PS_BASE_URI__.'product.php?id_product='.(int)$id_product->id);
			}
			return $link;
		}
		
		else if ($alias)
		{
			$link = '';
			if ($this->allow == 1)
			{
				$link .= (_PS_BASE_URL_.__PS_BASE_URI__.$this->getLangLink((int)$id_lang));
				
				/*if ($category AND $category != 'home')
					$link .= $category.'/';
				else 
				 	$link .= '';*/
				 
				$link .= (int)$id_product.'-'.$alias;
				
				if ($ean13) 
					$link .='-'.$ean13;
				else 
					$link .= '';
			
				$link .= '.html';
			}
			else
				$link .=(_PS_BASE_URL_.__PS_BASE_URI__.'product.php?id_product='.(int)$id_product);
			return $link;
		}
		
		else
			return _PS_BASE_URL_.__PS_BASE_URI__.'product.php?id_product='.(int)$id_product;
	}

}
?>