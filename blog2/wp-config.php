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
define('AUTH_KEY',         'kq^.Ej7wvm:3tv5HzM<f@ojdcCm @/0uI|OohAuMcxtiF@_f5^ruFStC3vl0m,!l');
define('SECURE_AUTH_KEY',  'uQVoC}Iky,,V[E[]z}-6tHSR+x,<R!y;BJ_j|^n>(qcLqlVK-`2 e+&1<HeFeYPK');
define('LOGGED_IN_KEY',    'U4),K}/VwR${DN+]>mM|7(fj!E^9PF?U}EFb+Cwm$j(EzFF#eT[xr-ry~Z1OYqHI');
define('NONCE_KEY',        'Q8)`ceBZBWY-pE=?+v4=VV[c}7i@NcJ7Q#~-2l|ACWC+C^z7Lh-+7$py.y[xtK|?');
define('AUTH_SALT',        'i;jt.W;hUd]k%}DKBS=z{Iu 2#9s`V3#EG,S45-j|(N|^p? #TGs9a}:bHl9>FkE');
define('SECURE_AUTH_SALT', 'X4~{Sqr~@p]iN9x/LET^2qX,Qc@ObVyAU-LFZ_B%**lWu;)+xv(p<7lXfm,oFj4d');
define('LOGGED_IN_SALT',   '|$-G$AhK>`80+t9su>SmIY5NWe?;u.-nDA@2-t-wA*xpA+{d?&wKelj1~}Tej2Vn');
define('NONCE_SALT',       'ap j=k)WknnV)I!%A*w,WE@9!(F^|.r1PA]ZS1BHy|%$4xA;|9GQ6{w;.qZnh++#');
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
