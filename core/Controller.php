<?php
	/**
	 * Controller.
	 *
	 * Global controller that other controllers extend.
	 *
	 * @package		iarecoding
	 * @subpackage	core
	 * @author		Justin Stolpe
	 * @link		https://github.com/jstolpe/iarecoding
	 * @version     1.0.0
	 */
	class Controller {
		/**
		 * Class constructor.
		 *
		 * Main constructor for all controllers.
		 *
		 * @param array $autoloader An instance of the Autoloader class.
		 * @return void
		 */
		public function __construct( $autoloader ) {
			foreach ( $autoloader->getLoadedModels() as $loadedModel ) { // loop over loaded models
				// get the name for the model
				$modelName = $loadedModel['model_name'];

				// instantiate model and save it to class variable with name of the model
				$this->$modelName = new $modelName( $autoloader );
			}
		}

		/**
		 * Loads a view file in the views folder.
		 *
		 * @param string $viewFile Name of the view file. Path starts from the views folder.
		 * @return void
		 */
		public function loadView( $viewFile, $data ) {
			// convert data array key/values to individual variables for the view
			extract( $data );

			// load the specified view
			require_once __DIR__ . '/../app/views/' . $viewFile . '.php';
		}

		/**
		 * Escape html correctly for output in the browser.
		 *
		 * @param string $string string to be escaped for output.
		 *
		 * @return string that is ready for output to the browser.
		 */
		function escapeHtml( $string ) {
			// call the model fucntion
			return $this->Model->escapeHtml( $string );
		}
	}
?>