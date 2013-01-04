Instructions d'installation
===============================================
1) D�zipper le module sendsms dans le r�pertoire module de Prestashop
2) Installer le module dans Prestashop, via l'onglet "Modules"
3) Si l'onglet SMS n'apparait pas sur la m�me ligne que les autres onglets, les renommer pour lui faire de la place. Par exemple Pr�f�rences -> Pr�f.
4) Cr�er un compte sur la plateforme d'envoi SMS http://www.smsworldsender.com et acheter des cr�dits
5) Renseigner les informations d'identification dans l'onglet SMS
6) Si vous souhaitez vendre le service � vos clients, cr�er un produit repr�sentant le service SMS (voir la video http://www.youtube.com/watch?v=4x7x7IDfWjE)
7) Choisir vos options, activer les �v�nements voulus dans l'onglet "Gestion des messages", et si besoin, personnaliser leurs textes.
8) Ajouter cron.php � votre crontab si vous souhaitez recevoir un rapport quotidien sur l'activit� de votre boutique

Optionnel (d�j� effectu� normalement par l'installation)
=========================================================
9) Faire les modifications suivantes dans les fichiers Prestashop
10) Supprimer du cache smarty le fichier correspondant � order-carrier.tpl (dans le r�pertoire /tools/smarty/compile/) ou opc-order.tpl si vous utilisez onepagecheckout


=======================================================================================================================
CES MODIFICATIONS SONT NORMALEMENT EFFECTUEES AUTOMATIQUEMENT LORS DE L'INSTALLATION
DANS L'ONGLET SMS, UN TABLEAU RECAPITULATIF VOUS INDIQUE LES FICHIERS CORRECTEMENTS MODIFIES, ET LES EVENTUELLES ERREURS
SI TOUTEFOIS VOUS AVEZ BESOIN DE (RE)FAIRE CES MODIFICATIONS, VEUILLEZ SUIVRE LES EXPLICATIONS CI-DESSOUS
VOUS POUVEZ VOIR L'EXEMPLE CORRESPONDANT AUX MODIFICATIONS EFFECTUEES POUR CHAQUE FICHIER DANS changes.zip
NE COPIEZ PAS LES FICHIERS EXEMPLES, ILS NE DOIVENT SERVIR QU'A VOUS INDIQUER L'ENDROIT OU FAIRE LA MODIFICATION
=======================================================================================================================

1. Pour permettre au client d'acheter l'option (si vous avez d�cid� de la vendre, et seulement si vous n'utilisez PAS le module onepagecheckout)
------------------------------------------------------------------------------------------------------------------------------------------------
- Ajouter ceci dans le fichier order.php situ� � la racine de votre Prestashop, au d�but de la fonction processCarrier (voir order.php dans changes.zip)
if (Module::isInstalled('sendsms')) {
	if (!Module::hookExec('sendsmsCustomerChoice', array('submit' => true, 'customerChoice' => $_POST['sendsms']))) {
		require_once(_PS_MODULE_DIR_.'/sendsms/classes/sendsmsManager.php');
		$manager = new sendsmsManager();
	    $errors[] = Tools::displayError($manager->getLabel('customer_phone_error', $cart->id_lang));
	}
}

- Ajouter ceci dans le fichier order.php situ� � la racine de votre Prestashop, dans la fonction displayCarrier (voir order.php dans changes.zip)
'HOOK_SENDSMS_CUSTOMER_CHOICE' => Module::hookExec('sendsmsCustomerChoice'),

- Ajouter ceci dans le fichier order.php situ� � la racine de votre Prestashop, au d�but de la fonction displaySummary (voir order.php dans changes.zip)
Module::hookExec('sendsmsCheckCartForSms');

- Ajouter ceci dans votre th�me, dans le fichier order-carrier.tpl (voir order-carrier.tpl dans changes.zip)
{$HOOK_SENDSMS_CUSTOMER_CHOICE}

- Personnaliser si n�cessaire le fichier sendsms.tpl dans le module sendsms, pour qu'il corresponde � votre th�me



1-BIS. Pour permettre au client d'acheter l'option (si vous avez d�cid� de la vendre, et seulement si vous utilisez le module onepagecheckout)
----------------------------------------------------------------------------------------------------------------------------------------------
- Ajouter ceci dans le fichier order.php situ� � la racine de votre Prestashop, � la fin de la fonction processCarrier (voir /onepagecheckout/order.php dans changes.zip)
if (Module::isInstalled('sendsms')) {
	if (!Module::hookExec('sendsmsCustomerChoice', array('submit' => true, 'customerChoice' => $_POST['sendsms']))) {
		require_once(_PS_MODULE_DIR_.'/sendsms/classes/sendsmsManager.php');
		$manager = new sendsmsManager();
	    $errors[] = Tools::displayError($manager->getLabel('customer_phone_error', $cart->id_lang));
	}
}

- Ajouter ceci dans le fichier order.php situ� � la racine de votre Prestashop, au d�but de la fonction displayCarrier (voir /onepagecheckout/order.php dans changes.zip)
Module::hookExec('sendsmsCheckCartForSms');

- Ajouter ceci dans le fichier order.php situ� � la racine de votre Prestashop, dans la fonction displayCarrier (voir /onepagecheckout/order.php dans changes.zip)
'HOOK_SENDSMS_CUSTOMER_CHOICE' => Module::hookExec('sendsmsCustomerChoice'),

- Ajouter ceci dans le fichier opc-order.tpl du module onepagecheckout (voir /onepagecheckout/opc-order.tpl dans changes.zip)
{$HOOK_SENDSMS_CUSTOMER_CHOICE}

- Personnaliser si n�cessaire le fichier sendsms.tpl dans le module sendsms, pour qu'il corresponde � votre th�me


2. POUR ETRE PREVENU QUAND UN CLIENT ENVOIT UN MESSAGE VIA LE FORMULAIRE DE CONTACT, OU LE REMERCIER
----------------------------------------------------------------------------------------------------
- Ajouter ceci dans le fichier contact-form.php situ� � la racine de votre Prestashop (voir contact-form.php dans changes.zip)
Module::hookExec('sendsmsContactForm', array('contact' => $contact, 'customer' => $customer, 'from' => $from, 'message' => eregi_replace('<br[[:space:]]*/?[[:space:]]*>',chr(13).chr(10),$message)));


3. POUR PREVENIR LE CLIENT QUAND UN PRODUIT EST DE NOUVEAU DISPONIBLE
---------------------------------------------------------------------
- Ajouter ceci dans le fichier mailalerts.php (module mailalerts), dans la fonction sendCustomerAlert (voir mailalerts.php dans changes.zip)
Module::hookExec('sendsmsCustomerAlert', array('customer' => $customer, 'product' => $product));


4. POUR ENVOYER UN SMS AU CLIENT AVEC SON NUMERO DE SUIVI COLIS
---------------------------------------------------------------
- Ajouter ceci dans la fonction postProcess du fichier AdminOrders.php du r�pertoire /votreadmin/tabs/ (voir AdminOrders.php dans changes.zip)
Module::hookExec('sendsmsShippingNumber', array('customer' => $customer, 'order' => $order, 'carrier' => $carrier));


5. POUR ENVOYER UN SMS AVEC SON NOUVEAU MOT DE PASSE A UN CLIENT QUI L'AURAIT PERDU
-----------------------------------------------------------------------------------
- Ajouter ceci dans le fichier password.php situ� � la racine de votre Prestashop (voir password.php dans changes.zip)
Module::hookExec('sendsmsLostPassword', array('customer' => $customer, 'password' => $password));