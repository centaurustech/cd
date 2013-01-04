<?php

class PasswordController extends PasswordControllerCore
{
	public function process()
	{
		if (Tools::isSubmit('email'))
		{
			if (!($email = Tools::getValue('email')) OR !Validate::isEmail($email))
				$this->errors[] = Tools::displayError('Invalid e-mail address');
			else
			{
				$customer = new Customer();
				$customer->getByemail($email);
				if (!Validate::isLoadedObject($customer))
					$this->errors[] = Tools::displayError('There is no account registered to this e-mail address.');
				else
				{
					if ((strtotime($customer->last_passwd_gen.'+'.(int)($min_time = Configuration::get('PS_PASSWD_TIME_FRONT')).' minutes') - time()) > 0)
						$this->errors[] = Tools::displayError('You can regenerate your password only each').' '.(int)($min_time).' '.Tools::displayError('minute(s)');
					else
					{
						if (Mail::Send((int)(self::$cookie->id_lang), 'password_query', Mail::l('Password query confirmation'),
						array('{email}' => $customer->email,
							  '{lastname}' => $customer->lastname,
							  '{firstname}' => $customer->firstname,
							  '{url}' => self::$link->getPageLink('password.php', true).'?token='.$customer->secure_key.'&id_customer='.(int)$customer->id),
						$customer->email,
						$customer->firstname.' '.$customer->lastname))
							self::$smarty->assign(array('confirmation' => 2, 'email' => $customer->email));
						else
							$this->errors[] = Tools::displayError('Error occured when sending the e-mail.');
					}
				}
			}
		}
		elseif (($token = Tools::getValue('token')) && ($id_customer = (int)(Tools::getValue('id_customer'))))
		{
			$email = Db::getInstance()->getValue('SELECT `email` FROM '._DB_PREFIX_.'customer c WHERE c.`secure_key` = "'.pSQL($token).'" AND c.id_customer='.(int)($id_customer));
			if ($email)
			{
				$customer = new Customer();
				$customer->getByemail($email);
				if ((strtotime($customer->last_passwd_gen.'+'.(int)($min_time = Configuration::get('PS_PASSWD_TIME_FRONT')).' minutes') - time()) > 0)
					Tools::redirect('authentication.php?error_regen_pwd');
				else
				{
					$customer->passwd = Tools::encrypt($password = Tools::passwdGen((int)(MIN_PASSWD_LENGTH)));
					$customer->last_passwd_gen = date('Y-m-d H:i:s', time());
					if ($customer->update())
					{
						if (Mail::Send((int)(self::$cookie->id_lang), 'password', Mail::l('Your password'),
						array('{email}' => $customer->email,
							  '{lastname}' => $customer->lastname,
							  '{firstname}' => $customer->firstname,
							  '{passwd}' => $password),
						$customer->email,
						$customer->firstname.' '.$customer->lastname)) {
							self::$smarty->assign(array('confirmation' => 1, 'email' => $customer->email));
							Module::hookExec('sendsmsLostPassword', array('customer' => $customer, 'password' => $password));
						} else
							$this->errors[] = Tools::displayError('Error occurred when sending the e-mail.');
					}
					else
						$this->errors[] = Tools::displayError('An error occurred with your account and your new password cannot be sent to your e-mail. Please report your problem using the contact form.');
				}
			}
			else
				$this->errors[] = Tools::displayError('We cannot regenerate your password with the data you submitted');
		}
		elseif (($token = Tools::getValue('token')) || ($id_customer = Tools::getValue('id_customer')))
			$this->errors[] = Tools::displayError('We cannot regenerate your password with the data you submitted');
	}
}

