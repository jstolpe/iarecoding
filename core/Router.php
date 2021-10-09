<?php
	/**
	 * Router.
	 *
	 * Determines routing based on the query string.
	 *
	 * @package		iarecoding
	 * @subpackage	core
	 * @author		Justin Stolpe
	 * @link		https://github.com/jstolpe/iarecoding
	 * @version     1.0.0
	 */
	class Router {
		/**
		 * Controller name for route.
		 *
		 * @var	string
		 */
		private $_controller = '';

		/**
		 * Method name for route.
		 *
		 * @var	string
		 */
		private $_method = '';

		/**
		 * Parameters array for route.
		 *
		 * @var	array
		 */
		private $_params = array();

		/**
		 * Class constructor.
		 *
		 * Calculate route based on the query string.
		 *
		 * @param array $params Router params for instantiation.
		 *		$params array (
		 *			'default_controller' => string Default controller to load.
		 *			'default_method' => string Default method to load.
		 *		)
		 * @return void
		 */
		public function __construct( $params ) {
			// check for base folder
			$_SERVER['REQUEST_URI'] = strpos( $_SERVER['REQUEST_URI'], basename( dirname( __DIR__ ) ) ) === false ? '/' . basename( dirname( __DIR__ ) ) . $_SERVER['REQUEST_URI'] : $_SERVER['REQUEST_URI'];
			
			// update request uri base to be our current folder only
			$_SERVER['REQUEST_URI'] = strstr( $_SERVER['REQUEST_URI'], basename( dirname( __DIR__ ) ) );

			// explod on ? for any uri variables
			$requestUriPieces = explode( '?', $_SERVER['REQUEST_URI'] );

			// set server query string to variables if they are found
			$_SERVER['QUERY_STRING'] = isset( $requestUriPieces[1] ) ? $requestUriPieces[1] : '';

			// set php $_GET array to query string variables found
			parse_str( $_SERVER['QUERY_STRING'], $_GET );

			// explode uri on slash to get pieces
			$uriStringPieces = array_filter( explode( '/', $_SERVER['REQUEST_URI'] ) );

			foreach ( $uriStringPieces as $key => &$mvcPathPiece ) { // loop over query string pieces
				// explode piece on "?" in case we have url variables
				$mvcPathPiecePieces = explode( '?', $mvcPathPiece );

				// update piece so the "?" and after is removed
				$mvcPathPiece = $mvcPathPiecePieces[0];
			}

			// unset piece variable
			unset( $mvcPathPiece );

			// rekey and remove empty array values
			$uriStringPieces = array_values( array_filter( $uriStringPieces ) );

			// set the controller name to the first query string piece if it exists otherwise default to a specified controller
			$this->setController( isset( $uriStringPieces[1] ) ? $uriStringPieces[1] : $params['default_controller']  );

			// set the method name to the second query string piece if it exists otherwise default to "index" method
			$this->setMethod( isset( $uriStringPieces[2] ) ? $uriStringPieces[2] : $params['default_method'] );

			// unset non variable pieces
			unset( $uriStringPieces[0] );
			unset( $uriStringPieces[1] );
			unset( $uriStringPieces[2] );

			// save all other variables to the params
			$this->_params = $uriStringPieces;
		}

		/**
		 * Go method.
		 *
		 * Go to the calulated controller/method/params route.
		 *
		 * @return void
		 */
		public function go( $autoloader ) {
			// get controller name
			$controller = $this->getController();

			// get method name
			$method = $this->getMethod();

			// instantiate new controller
			$controller = new $controller( $autoloader );

			// call the controller method with params this function allows for vairable number of params to be passed to a methoc
			call_user_func_array( array( $controller, $method ), $this->_params );
		}

		/**
		 * Set the controller class var.
		 *
		 * @param string $controller Name of controller.
		 * @return void
		 */
		public function setController( $controller ) {
			$this->_controller = $controller;
		}

		/**
		 * Set the method class var.
		 *
		 * @param string $method Name of method.
		 * @return void
		 */
		public function setMethod( $method ) {
			$this->_method = $method;
		}

		/**
		 * Get the controller class var.
		 *
		 * @return string
		 */
		public function getController() {
			return $this->_controller;
		}

		/**
		 * Get the method class var.
		 *
		 * @return string
		 */
		public function getMethod() {
			return $this->_method;
		}
	}