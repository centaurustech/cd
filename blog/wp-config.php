<?php
/**
 * La configuration de base de votre installation WordPress.
 *
 * Ce fichier contient les réglages de configuration suivants : réglages MySQL,
 * préfixe de table, clefs secrètes, langue utilisée, et ABSPATH.
 * Vous pouvez en savoir plus à leur sujet en allant sur 
 * {@link http://codex.wordpress.org/Editing_wp-config.php Modifier
 * wp-config.php} (en anglais). C'est votre hébergeur qui doit vous donner vos
 * codes MySQL.
 *
 * Ce fichier est utilisé par le script de création de wp-config.php pendant
 * le processus d'installation. Vous n'avez pas à utiliser le site web, vous
 * pouvez simplement renommer ce fichier en "wp-config.php" et remplir les
 * valeurs.
 *
 * @package WordPress
 */

// ** Réglages MySQL - Votre hébergeur doit vous fournir ces informations. ** //
/** Nom de la base de données de WordPress. */
define('DB_NAME', 'p3328_1');

/** Utilisateur de la base de données MySQL. */
define('DB_USER', 'p3328_1');

/** Mot de passe de la base de données MySQL. */
define('DB_PASSWORD', 'GyIWofSKylVV');

/** Adresse de l'hébergement MySQL. */
define('DB_HOST', 'cl1-sql7');

/** Jeu de caractères à utiliser par la base de données lors de la création des tables. */
define('DB_CHARSET', 'utf8');

/** Type de collation de la base de données. 
  * N'y touchez que si vous savez ce que vous faites. 
  */
define('DB_COLLATE', '');

/**#@+
 * Clefs uniques d'authentification et salage.
 *
 * Remplacez les valeurs par défaut par des phrases uniques !
 * Vous pouvez générer des phrases aléatoires en utilisant 
 * {@link https://api.wordpress.org/secret-key/1.1/salt/ le service de clefs secrètes de WordPress.org}.
 * Vous pouvez modifier ces phrases à n'importe quel moment, afin d'invalider tous les cookies existants.
 * Cela forcera également tous les utilisateurs à se reconnecter.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         ';,Z8U?%ifn#bD--W<9_gYcnVCJ[w=Rm9/_3%]Fcby6x>knV[!Bk5~B_|<I#|VAy;');
define('SECURE_AUTH_KEY',  '2Zj59V]Z)[8k*yq$f?#Yc[b@)|;vFO*pAY{2{Y=e7>FhK:yP)zeAKC.,qGxP#85O');
define('LOGGED_IN_KEY',    '*9M jd(jQ]d+M-z+;$bt&IEj|eO)41z8-_z %-QW~VRCGb|c8D7?Ut]Ko<`edi_ ');
define('NONCE_KEY',        '7C+4Ec|!IX&,A6^E5t0, jI-)p{0fl4;6C1/X.Bax8v`ycE,2+-E O|[,+g;b12#');
define('AUTH_SALT',        '-NPm8Z9QasIX /]:e`Pe+GINW^$fR1xl0e|?x6`OgZ:=#ao:8P:`{uj-8{2A>x}R');
define('SECURE_AUTH_SALT', '^+_[u7#Pomg#cLfQxm0mKZTL{Lee.|n]1f]tSgpkc|3%:+h}p~chN<>g/yUy)`=L');
define('LOGGED_IN_SALT',   '4JFwgKA6!lj!-o1XOVt={={?f`:<UyBS6)- MaUlIc;sHyJs-]QkHsg&88x &Au_');
define('NONCE_SALT',       'gBoKQNFW[*avD*brY-K#;AcDu_%yd#5v@?+Fp2tcIq[j;u$|4*H|Lh|+~wQa{,k|');
/**#@-*/

/**
 * Préfixe de base de données pour les tables de WordPress.
 *
 * Vous pouvez installer plusieurs WordPress sur une seule base de données
 * si vous leur donnez chacune un préfixe unique. 
 * N'utilisez que des chiffres, des lettres non-accentuées, et des caractères soulignés!
 */
$table_prefix  = 'wp_';

/**
 * Langue de localisation de WordPress, par défaut en Anglais.
 *
 * Modifiez cette valeur pour localiser WordPress. Un fichier MO correspondant
 * au langage choisi doit être installé dans le dossier wp-content/languages.
 * Par exemple, pour mettre en place une traduction française, mettez le fichier
 * fr_FR.mo dans wp-content/languages, et réglez l'option ci-dessous à "fr_FR".
 */
define('WPLANG', 'fr_FR');

/** 
 * Pour les développeurs : le mode deboguage de WordPress.
 * 
 * En passant la valeur suivante à "true", vous activez l'affichage des
 * notifications d'erreurs pendant votre essais.
 * Il est fortemment recommandé que les développeurs d'extensions et
 * de thèmes se servent de WP_DEBUG dans leur environnement de 
 * développement.
 */ 
define('WP_DEBUG', false); 

/* C'est tout, ne touchez pas à ce qui suit ! Bon blogging ! */

/** Chemin absolu vers le dossier de WordPress. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Réglage des variables de WordPress et de ses fichiers inclus. */
require_once(ABSPATH . 'wp-settings.php');