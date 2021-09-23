<?php
	/**
	 * Model
	 *
	 * Global model that other controllers extend
	 *
	 * @package		iarecoding
	 * @subpackage	core
	 * @author		Justin Stolpe
	 * @link		https://github.com/jstolpe/iarecoding
	 * @version     1.0.0
	 */
	class Model {
		/**
		 * Autoloader array.
		 *
		 * @var	object
		 */
 		public $autoloader;

		/**
		 * Database object.
		 *
		 * @var	object
		 */
 		public $database; 		

 		/**
		 * Class constructor.
		 *
		 * Main model for all models.
		 *
		 * @param array $autoloader Instance of the autoloader class.
		 * @return void
		 */
		public function __construct( $autoloader ) {
			// save autoloader to our class
			$this->autoloader = $autoloader;

			// get the database object from autoloader and save it to our class
			$this->database = $this->autoloader->getDatabase();
		}

		/**
		 * Load model.
		 *
		 * Instantiate the model and create a class variable for it by model name.
		 *
		 * @param string $modelName Name of the model to instantiate.
		 * @return void
		 */
		public function loadModel( $modelName ) {
			// instantiate the model under a class variable with the name of the model
			$this->$modelName = new $modelName( $this->autoloader );
		}

		/**
		 * Load other model to specified model for use.
		 *
		 * Check the model ane make sure it has access to all other models
		 *
		 * @param array  $autoloader Instance of the autoloader class.
		 * @param string $loadToModelName Name of the model to load all other modes onto.
		 * @return void
		 */
		public function loadAllModels( $autoloader, $loadToModelName ) {
			if ( !property_exists( $loadToModelName, 'allModelsLoaded' ) ) { // all models have not been loaded for use
				foreach ( $autoloader->getLoadedModels() as $model ) { // loop over loaded models
					// get the name for the model
					$modelName = $model['model_name'];

					if ( $loadToModelName != $modelName && 'Model' != $modelName && 'Model' != $modelName ) { // ignore core Model and target model matching the model valiable name
						// target model and add on the new model with the variable name as its class name
						$this->loadModel( $modelName );
					}
				}
			}
		}
		
		/**
		 * Escape html correctly for output in the browser.
		 *
		 * @param string $string string to be escaped for output.
		 *
		 * @return string that is ready for output to the browser.
		 */
		function escapeHtml( $string ) {
			return htmlentities( $string, ENT_QUOTES, 'UTF-8' );
		}

		/**
		 * Get the html for a view file.
		 *
		 * @param string $viewFile Name of the view file. Path starts from the views folder.
		 * @return strgin
		 */
		public function getViewHtml( $viewFile, $data ) {
			// convert data array key/values to individual variables for the view
			extract( $data );

			// start output buffer
			ob_start();

			// load the specified view
			require_once __DIR__ . '/../app/views/' . $viewFile . '.php';

			// return output buffer contents and clear the output buffer
			return ob_get_clean();
		}
	}
?>