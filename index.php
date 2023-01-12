<?php
	// environment type development or production
	defined( 'ENVIRONMENT' ) or define( 'ENVIRONMENT', 'development' );

	// get protocol
	defined( 'PROTOCOL' ) or define( 'PROTOCOL', isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] != 'off' ? 'https://' : 'http://' );

	// host path
	defined( 'PATH_TO_HOST' ) or define( 'PATH_TO_HOST', PROTOCOL . $_SERVER['HTTP_HOST'] . '/' );

	// url path to root
	defined( 'BASE_URL' ) or define( 'BASE_URL', PATH_TO_HOST . dirname( $_SERVER['PHP_SELF'] ) . '/' );

	// url path to asssets like js/css/files
	defined( 'BASE_URL_ASSETS' ) or define( 'BASE_URL_ASSETS', BASE_URL . 'app/assets/' );

	// boolean for using database or not
	defined( 'USE_DATABASE' ) or define( 'USE_DATABASE', false );

	// boolean for using session or not
	defined( 'USE_SESSION' ) or define( 'USE_SESSION', false );

	if ( ENVIRONMENT == 'development' ) { // development env specific things
		// display all errors
		error_reporting( -1 );
		ini_set( 'display_errors', 1 );
	} else { // production env specific things
		// hide all errors
		error_reporting( 0 );
		ini_set( 'display_errors', 0 );
	}

	// require autoloader
	require_once 'core/Autoloader.php';

	$autoloaderParmas = array( // autoloader parmas
		'database' => array( // database info
			'load' => USE_DATABASE, // should we load the database
			'creds' => array( // database creds
				'hostname' => 'localhost',
				'username' => 'root',
				'password' => '',
				'database' => 'iarecoding'
			)
		),
		'session' => array( // use session helper and save in database
			'load' => USE_SESSION, // load session
			'sess_name' => 'iarecoding', // name of the session
			'sess_id_time_to_regen' => 300, // number of seconds before a new session id should be regenerated
			'secs_till_expire' => 86400 * 60, // number of seconds user needs to be inactive expiration
			'database_table_name' => 'sessions' // table name in the database
		)
	);

	// run the autoloader so our files get loaded
	$autoloader = new Autoloader( $autoloaderParmas );

	$routerParams = array( // params for rounter instantiation
		'default_controller' => 'IAreCoding',
		'default_method' => 'index'
	);

	// calculate route based on the query string
	$router = new Router( $routerParams );

	// load the calculated route
	$router->go( $autoloader );
?>