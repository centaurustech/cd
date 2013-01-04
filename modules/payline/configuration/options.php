<?php
	// Durées du timeout d'appel des webservices
	DEFINE( 'PRIMARY_CALL_TIMEOUT', 15);
	DEFINE( 'SECONDARY_CALL_TIMEOUT', 15 );
	
	// Nombres de tentatives sur les chaines primaire et secondaire par transaction
	DEFINE( 'PRIMARY_MAX_FAIL_RETRY', 1 );
	DEFINE( 'SECONDARY_MAX_FAIL_RETRY', 2 );
	
	// Durées d'attente avant le rejoue de la transaction
	DEFINE( 'PRIMARY_REPLAY_TIMER', 15 );
	DEFINE( 'SECONDARY_REPLAY_TIMER', 15 );
		
	DEFINE( 'PAYLINE_ERR_CODE', '02101,02102,02103' ); // Codes erreurs payline qui signifie l'échec de la transaction
	DEFINE( 'PAYLINE_WS_SWITCH_ENABLE',  ''); // Nom des services web autorisés à basculer
	DEFINE( 'PAYLINE_SWITCH_BACK_TIMER', 600 ); // Durées d'attente pour rebasculer en mode nominal
	DEFINE( 'PRIMARY_TOKEN_PREFIX', '1' ); // Préfixe du token sur le site primaire
	DEFINE( 'SECONDARY_TOKEN_PREFIX', '2' ); // Préfixe du token sur le site secondaire
	DEFINE( 'INI_FILE' , _PS_MODULE_DIR_.'payline/configuration/HighDefinition.ini'); // Chemin du fichier ini
	DEFINE( 'PAYLINE_ERR_TOKEN', '02317,02318' ); // Préfixe du token sur le site primaire
?>