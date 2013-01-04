<?php
class		PaylineClass extends ObjectModel
{
	/** @var integer editorial id*/
	public		$id = 1;
	
	/** @var string recurring_title*/
	public		$recurring_title;

	/** @var string recurring_subtitle*/
	public		$recurring_subtitle;

	/** @var string direct_title*/
	public		$direct_title;

	/** @var string wallet_title*/
	public		$wallet_title;
	
	protected 	$table = 'payline';
	protected 	$identifier = 'id_payline';
	
	protected 	$fieldsValidateLang = array(
		'recurring_title' => 'isGenericName',
		'recurring_subtitle' => 'isGenericName',
		'direct_title' => 'isGenericName',
		'wallet_title' => 'isGenericName');
	
	/**
	  * Check then return multilingual fields for database interaction
	  *
	  * @return array Multilingual fields
	  */
	public function getTranslationsFieldsChild()
	{
		parent::validateFieldsLang();

		$fieldsArray = array('recurring_title', 'recurring_subtitle', 'direct_title', 'wallet_title');
		$fields = array();
		$languages = Language::getLanguages(false);
		$defaultLanguage = (int)(Configuration::get('PS_LANG_DEFAULT'));
		foreach ($languages as $language)
		{
			$fields[$language['id_lang']]['id_lang'] = (int)($language['id_lang']);
			$fields[$language['id_lang']][$this->identifier] = (int)($this->id);
			foreach ($fieldsArray as $field)
			{
				if (!Validate::isTableOrIdentifier($field))
					die(Tools::displayError());
				if (isset($this->{$field}[$language['id_lang']]) AND !empty($this->{$field}[$language['id_lang']]))
					$fields[$language['id_lang']][$field] = pSQL($this->{$field}[$language['id_lang']], true);
				elseif (in_array($field, $this->fieldsRequiredLang))
					$fields[$language['id_lang']][$field] = pSQL($this->{$field}[$defaultLanguage], true);
				else
					$fields[$language['id_lang']][$field] = '';
			}
		}
		return $fields;
	}
	
	public function copyFromPost()
	{
		/* Classical fields */
		foreach ($_POST AS $key => $value)
			if (key_exists($key, $this) AND $key != 'id_'.$this->table)
				$this->{$key} = $value;

		/* Multilingual fields */
		if (sizeof($this->fieldsValidateLang))
		{
			$languages = Language::getLanguages(false);
			foreach ($languages AS $language)
				foreach ($this->fieldsValidateLang AS $field => $validation)
					if (isset($_POST[$field.'_'.(int)($language['id_lang'])]))
						$this->{$field}[(int)($language['id_lang'])] = $_POST[$field.'_'.(int)($language['id_lang'])];
		}
	}
	
	public function getFields()
	{
		parent::validateFields();
		$fields['id_payline'] = (int)($this->id);
		return $fields;
	}
}
