<?php
	// environment type development or production
	defined( 'ENVIRONMENT' ) or define( 'ENVIRONMENT', 'development' );

	// get protocol
	defined( 'PROTOCOL' ) or define( 'PROTOCOL', isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] != 'off' ? 'https://' : 'http://' );

	// url path to root
	defined( 'BASE_URL' ) or define( 'BASE_URL', PROTOCOL . $_SERVER['HTTP_HOST'] . dirname( $_SERVER['PHP_SELF'] ) . '/' );

	// url path to asssets like js/css/files
	defined( 'BASE_URL_ASSETS' ) or define( 'BASE_URL_ASSETS', BASE_URL . 'app/assets/' );

	// boolean for using database or not
	defined( 'USE_DATABASE' ) or define( 'USE_DATABASE', false );

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
				'hostname' => '',
				'username' => '',
				'password' => '',
				'database' => ''
			)
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