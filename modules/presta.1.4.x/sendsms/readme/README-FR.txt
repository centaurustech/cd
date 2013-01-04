Instructions d'installation
===============================================
1) D�zipper le module sendsms et copiez le r�pertoire sendsms (uniquement celui contenu dans presta.1.4.x) dans dans le r�pertoire module de Prestashop
2) Copiez le contenu du dossier /sendsms/override/controllers dans le r�pertoire override/controllers de Prestashop
3) Installer le module dans Prestashop, via l'onglet "Modules"
4) Si l'onglet SMS n'apparait pas sur la m�me ligne que les autres onglets, les renommer pour lui faire de la place. Par exemple Pr�f�rences -> Pr�f.
5) Cr�er un compte sur la plateforme d'envoi SMS http://www.smsworldsender.com et acheter des cr�dits
6) Renseigner les informations d'identification dans l'onglet SMS
7) Si vous souhaitez vendre le service � vos clients, cr�er un produit repr�sentant le service SMS (voir la video http://www.youtube.com/watch?v=4x7x7IDfWjE)
8) Choisir vos options, activer les �v�nements voulus dans l'onglet "Gestion des messages", et si besoin, personnaliser leurs textes.
9) Ajouter cron.php � votre crontab si vous souhaitez recevoir un rapport quotidien sur l'activit� de votre boutique

Optionnel (d�j� effectu� normalement par l'installation)
=========================================================
10) Faire les modifications suivantes dans les fichiers Prestashop
11) Supprimer du cache smarty le fichier correspondant � order-carrier.tpl (dans le r�pertoire /tools/smarty/compile/)



=======================================================================================================================
CES MODIFICATIONS SONT NORMALEMENT EFFECTUEES AUTOMATIQUEMENT LORS DE L'INSTALLATION
DANS L'ONGLET SMS, UN TABLEAU RECAPITULATIF VOUS INDIQUE LES FICHIERS CORRECTEMENTS MODIFIES, ET LES EVENTUELLES ERREURS
SI TOUTEFOIS VOUS AVEZ BESOIN DE (RE)FAIRE CES MODIFICATIONS, VEUILLEZ SUIVRE LES EXPLICATIONS CI-DESSOUS
VOUS POUVEZ VOIR L'EXEMPLE CORRESPONDANT AUX MODIFICATIONS EFFECTUEES POUR CHAQUE FICHIER DANS changes.zip
NE COPIEZ PAS LES FICHIERS EXEMPLES, ILS NE DOIVENT SERVIR QU'A VOUS INDIQUER L'ENDROIT OU FAIRE LA MODIFICATION
=======================================================================================================================

1. Pour permettre au client d'acheter l'option (si vous avez d�cid� de la vendre)
---------------------------------------------------------------------------------
- Ajouter ceci dans votre th�me, dans le fichier order-carrier.tpl (voir order-carrier.tpl dans changes.zip)
{$HOOK_SENDSMS_CUSTOMER_CHOICE}

- Personnaliser si n�cessaire le fichier sendsms.tpl dans le module sendsms, pour qu'il corresponde � votre th�me


2. POUR PREVENIR LE CLIENT QUAND UN PRODUIT EST DE NOUVEAU DISPONIBLE
---------------------------------------------------------------------
- Ajouter ceci dans le fichier mailalerts.php (module mailalerts), dans la fonction sendCustomerAlert (voir mailalerts.php dans changes.zip)
Module::hookExec('sendsmsCustomerAlert', array('customer' => $customer, 'product' => $product));


3. POUR ENVOYER UN SMS AU CLIENT AVEC SON NUMERO DE SUIVI COLIS
---------------------------------------------------------------
- Cette ligne est normalement ajout� automatiquement lors de l'installation du module dans le fichier AdminOrders.php
- Toutefois, si ce n'�tait pas le cas, il faut ajouter ceci dans la fonction postProcess du fichier AdminOrders.php du r�pertoire /votreadmin/tabs/ (voir AdminOrders.php dans changes.zip)
Module::hookExec('sendsmsShippingNumber', array('customer' => $customer, 'order' => $order, 'carrier' => $carrier));