<?php
	/**
	 * Autoloader.
	 *
	 * Autoload files from various places so they are ready to use.
	 *
	 * @package		iarecoding
	 * @subpackage	core
	 * @author		Justin Stolpe
	 * @link		https://github.com/jstolpe/iarecoding
	 * @version     1.0.0
	 */
	class Autoloader {
		/**
		 * Autoload files in these folders.
		 *
		 * @var	array
		 */
		private $_folders = array(
			array( 
				'name' => 'core'
			),
			array(
				'name' => 'app/controllers'
			),
			array(
				'name' => 'app/models'
			)
		);

		/**
		 * Store what models have been loaded.
		 *
		 * @var	array
		 */
		private $_loadedModels = array(
			array(
				'model_name' => 'Model'
			)
		);

		/**
		 * Database info.
		 *
		 * @var	array
		 */
		private $_database = array();

		/**
		 * Class constructor.
		 *
		 * Autoload files for use.
		 *
		 * @param array $params Autoloader params for instantiation.
		 *		$params array (
		 *			'database' => array ( Required if using a database. Holds info on the database connection.
		 *				'load' => boolean true/false if database connection should be established
		 *				'creds' => array( Database credentials required if loading the database
		 *					'hostname' => string database host name.
		 *					'username' => string database username.
		 *					'password' => string database password.
		 *					'database' => string database name.
		 *				)
		 * 			)
		 *		)
		 * @return void
		 */
		public function __construct( $params = array() ) {
			foreach ( $this->_folders as $folder ) { // loop over folders and autoload files in the folder
				$this->autoloadFolderFiles( $folder );
			}

			if ( isset( $params['database']['load'] ) && $params['database']['load'] ) { // load database
				$this->_database = new Database( $params['database']['creds'] );
			}
		}

		/**
		 * Autoload files in the folder.
		 *
		 * @param array $folder Folder information
		 *		$folder (
		 *			'name' => string Name of folder.
		 *		)
		 * @return void
		 */
		public function autoloadFolderFiles( $folder ) {
			// path to the folder
			$pathToFolder = __DIR__ . '/../' . $folder['name'] . '/';

			// get folder files
			$folderFiles = scandir( $pathToFolder );

			foreach ( $folderFiles as $folderFile ) { // loop over folder files
				if ( '.' != $folderFile && '..' != $folderFile ) { // only load if not dots
					if ( 'app/models' == $folder['name'] ) { // add models to the loadedModles array
						$this->_loadedModels[] = array(
							'model_name' => pathinfo( $folderFile, PATHINFO_FILENAME )
						);
					}

					// load the file
					require_once $pathToFolder . $folderFile;
				}
			}
		}

		/**
		 * Get loaded models.
		 *
		 * @return string
		 */
		public function getLoadedModels() {
			return $this->_loadedModels;
		}

		/**
		 * Get database.
		 *
		 * @return string
		 */
		public function getDatabase() {
			return $this->_database;
		}
	}