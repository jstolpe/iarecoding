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
			// get loaded models
			$autoloaderModels = $autoloader->getLoadedModels();

			foreach ( $autoloaderModels as $model ) { // loop over loaded models
				// get the name for the model
				$modelName = $model['model_name'];

				// instantiate model and save it to class variable with name of the model
				$this->$modelName = new $modelName( $autoloader );

				foreach ( $autoloaderModels as $modelVariable ) { // loop over models adding them all to the parent foreach model so they have access
					// get model variable name to add to the target model
					$modelVariableName = $modelVariable['model_name'];

					if ( 'Model' != $modelName && 'Model' != $modelVariableName && $modelName != $modelVariableName ) { // ignore core Model and target model matching the model valiable name
						// target model and add on the new model with the variable name as its class name
						$this->$modelName->$modelVariableName = new $modelVariableName( $autoloader );
					}
				}

				$this->$modelName->allModelsLoaded = true;
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