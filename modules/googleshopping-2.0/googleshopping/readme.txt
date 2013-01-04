/** @copyright igwane.com 2010-2012 **/
/*************************************/
/     Google Shopping Module          /
/*************************************/

Ce module est offert à la communauté par la société Igwane.com. (http://www.igwane.com)
N'hésitez pas à nous envoyer vos commentaires sur ce module.
Si vous avez des besoins particuliers, nous pouvons aussi développer des modules sur demande.

INFORMATION SUR LA LICENCE D'UTILISATION
L'utilisation de ce fichier source est soumise à une licence d'utilisation concédée par la société Igwane.com.
Toute utilisation, reproduction, modification ou distribution du présent fichier source sans contrat 
de licence écrit de la part de Igwane.com est expressément interdite.
Pour obtenir une licence, veuillez contacter Igwane.com à l'adresse: http://www.igwane.com/fr/license
 
Nom du module: googleshopping
Compatibilité: 1.4.6.2
Testé: 1.4.6.2 & 1.4.4.0 & 1.3.1.1
Base de donnée: oui
Lead developer: Matthieu Fradcourt <matthieu@igwane.com>
Others developers : David Desquiens <david@igwane.com>, Ambroise Roquette

Spécifications techniques du flux:
	http://www.google.com/support/merchants/bin/answer.py?hl=fr&answer=188494
	
Balises google shopping implémentées :
	<title> : Récupération automatique du nom du magasin
	<link> : Génération automatique de l'url du magasin
	<g:id> : L'id des produits
	<title> : Est actuellement le nom du produit
	<link> : Lien vers la fiche produit
	<g:price> : Le prix du produit avec taxes
	<g:description> : Description longue du produit
	<g:condition> : Est toujours à "new"
	<g:mpn> : Référence fabricant (*optionnel)
	<g:image_link> : Liste des images pour un produit
	<g:quantity> : Quantité disponible pour un produit
	<g:gtin> : Code EAN13 pour l'europe
	<g:brand> : Marque
	<g:availability> : Disponibilité du produit
	<g:featured_product> : Indique si le produit est en solde

v2.0 - Multilingues
Il n'est plus nécessaire de choisir la langue - Ambroise Roquette
Un champs input permet de définir la catégorie Google pour chaque langue - Ambroise Roquette
L'interface a été adaptée pour afficher les multiples fichiers générés - Ambroise Roquette
correction sur l'interface Back Office du lien "Lire les dernières mises à jour (readme.txt)" (balise "<a>" non fermée) - Ambroise Roquette
correction des URL dans les fichiers générés pour contenir le code ENA (compatibles avec les versions Prestashop inférieur à 1.4 ?) - Ambroise Roquette
désactivation d'un bout de code générant des url d'images sous la forme http://nomdedomaine.fr/nomdedomaine.fr/image.jpg (marqué en commentaire "BUG" dans le code) - Ambroise Roquette
lorsqu'une image n'est pas disponible dans la langue dans laquelle le fichier est généré on regarde si l'image est disponible dans une autre langue - Ambroise Roquette

v1.9
Génération des images avec $link->getImageLink
Ajout champ image permettant de choisir le nom du type de l'image à envoyer (par défaut large)

v1.82
Ajout balise g:google_product_category
Ajout des informations sur les balises recommandées par Google

v1.81
Correction orthographique - Merci à Anthony

v1.8
Correction Catégorie Google (product_type) et lien vers site de Google
Ajout des informations de licence via formulaire
Correction frais de port

v1.71
Suppression test d'affichage des catégories

v1.7
Déplacement des _POST dans la méthode appelant la méthode statique generateFile
Création fichier cron.php pour permettre la génération via une tâche CRON (sous Linux)
Correction de la fonction myTools::f_convert_text

v1.63
Ajout commentaires
Refactoring
Ajout traductions

v1.62
Ajout de la balise <g:featured_product>
Les majuscules sont conservées dans la description
Correction bug sur l'option quantité à afficher ou non
Modification sur l'affichage du Back Office

v1.61
Limitation du nombre d'images à 10
	
v1.6
Mise à jour de la présentation du module dans le back-office
Ajout de la balise <g:availability>
Nettoyage du champ description

v1.5
Correction de bugs

v1.4
Choix de description longue ou courte
Mise en petits caractères pour les titres en gras
Coupe le titre si plus de 70 caractères
Coupe la description si plus de 10000 caractères
	
v1.3
Ajout de traductions
Ajout d'un test pour ne pas ajouter de balise vide pour les produits n'ayant pas certaines propriétés
Ajout de l'option "Marque"
ajout de l'option "Code EAN13"

v1.2
Ajout de l'option "Générer le fichier à la racine du site" qui permet de générer le fichier à la racine du site (sinon dans le dossier du module)
Ajout de l'option "Références fabricants" qui permet d'ajouter les refs fabricants au fichier
Création d'un fichier myTools perso qui permet de garder la compatibilité avec les versions précédente de Prestashop

v1.1
Suppression de quelques lignes qui n'avaient rien à faire dans le code pour le moment :)

V1.0
Les balises disponibles:
	<title> : Récupération automatique du nom du magasin
	<link> : Génération automatique de l'url du magasin
	<g:id> : L'id des produits
	<title> : Est actuellement le nom du produit
	<link> : Lien vers la fiche produit
	<g:price> : Le prix du produit avec taxes
	<g:description> : Description longue du produit
	<g:condition> : Est toujours à "new"
	<g:mpn> : Référence fabricant
	<g:image_link> : Liste des images pour un produit
	<g:quantity> : Quantité disponible pour un produit