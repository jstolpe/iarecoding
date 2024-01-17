<?php
	/**
	 * Session.
	 *
	 * Handles php session.
	 *
	 * @package		iarecoding
	 * @subpackage	core
	 * @author		Justin Stolpe
	 * @link		https://github.com/jstolpe/iarecoding
	 * @version     1.0.0
	 */
	class Session {
		/**
		 * Database.
		 *
		 * @var	array
		 */
		private $_database = array();

		/**
		 * Database table name.
		 *
		 * @var	string
		 */
		private $_databaseTableName = 'sessions';

		/**
		 * Session id after the session has been started.
		 *
		 * @var	string
		 */
		private $_initialSessId = '';

		/**
		 * Regenerated session id.
		 *
		 * @var	string
		 */
		private $_regeneratedSessId = '';

		/**
		 * Number of seconds until the session expires.
		 *
		 * @var	integer
		 */
		private $_secsTillExpire = 100;

		/**
		 * Number of seconds the session id should be regenerated.
		 *
		 * @var	integer
		 */
		private $_sessIdTimeToRegen = 10;

		/**
		 * Name of the session.
		 *
		 * @var	integer
		 */
		private $_sessName = 'iarecoding';

		/**
		 * Name of the key in the session that holds the time to regen.
		 *
		 * @var	integer
		 */
		private $_sessLastRegenKey = '';

		/**
		 * Session.
		 *
		 * @var	array
		 */
		private $_sessData = array();

		/**
		 * Class constructor.
		 *
		 * Setup session.
		 *
		 * @param array $params Router params for instantiation.
		 *		$params array (
		 * 			'sess_name' => string name of the session.
		 *			'sess_id_time_to_regen' => integer number of seconds before a new session id should be regenerated.
		 *			'secs_till_expire' => integer number of seconds user needs to be inactive expiration.
		 * 			'database' => array database to use for storing session data.
		 * 			'database_table_name' => string name of table in the database.
		 *		)
		 * @return void
		 */
		public function __construct( $params ) {
			// set session name
			$this->_sessName = isset( $params['sess_name'] ) ? $params['sess_name'] : $this->_sessName;

			// set regen session key name
			$this->_sessLastRegenKey = $this->_sessName . '_sess_id_last_regenerated';

			// set time to regen
			$this->_sessIdTimeToRegen = isset( $params['sess_id_time_to_regen'] ) ? $params['sess_id_time_to_regen'] : $this->_sessIdTimeToRegen;

			// set expire time
			$this->_secsTillExpire = isset( $params['secs_till_expire'] ) ? $params['secs_till_expire'] : $this->_secsTillExpire;

			// set database
			$this->_database = isset( $params['database'] ) ? $params['database'] : $this->_database;

			// set database table name
			$this->_databaseTableName = isset( $params['database_table_name'] ) ? $params['database_table_name'] : $this->_databaseTableName;

			// set session name
			ini_set( 'session.name', $this->_sessName );
			
			session_set_cookie_params( // set session params
				$this->_secsTillExpire, // lifetime of session in seconds
				'/', // path
				'', // domain
				FALSE, // will only set if https
				TRUE // if set to true then php will attempt to send the httponly flag when setting the session cookie
			);

			//set session max lifetime
			ini_set( 'session.gc_maxlifetime', $this->_secsTillExpire );

			//  don't allow session id to be passed in the url
			ini_set( 'session.use_trans_sid', 0 );

			// server creates new session id if one does not exist
			ini_set( 'session.use_strict_mode', 1 );

			// use cookies to store session id on client
			ini_set( 'session.use_cookies', 1 );

			// make sure we only use cookies
			ini_set( 'session.use_only_cookies', 1 );

			// set session name
			session_name( $this->_sessName );

			// start the session!
			session_start();

			// set session id
			$this->setSessId();

			// initialize database
			$this->initializeSessFromDb();
		}

		/**
		 * Delete session from the database.
		 *
		 * Delete a row in the database by sesssion id.
		 * 
		 * @param string $sessId ID of the session row to delete.
		 * @return void
		 */
		public function deleteSessFromDb( $sessId ) {
			// specify database table
			$this->_database->table( $this->_databaseTableName );

			// where id is session id
			$this->_database->where( 'id', $sessId );

			// return results
			return $this->_database->runDeleteQuery();
		}

		/**
		 * Get data from the session.
		 *
		 * Look up a key value pair in session and return it.
		 *
		 * @param string $key Key in the session data to get a value for.
		 * @return value for the key if it exists.
		 */
		public function getData( $key = '' ) {
			if ( $key ) { // return the value for the incoming key
				return isset( $this->_sessData[$key] ) ? $this->_sessData[$key] : '';
			} else { // return all data
				return $this->_sessData;
			}
		}

		/**
		 * Get session from the database.
		 *
		 * Get a session from the database for a specific session id.
		 * 
		 * @param string $sessId ID of the session row to get.
		 * @return array
		 */
		public function getSessFromDb( $sessId ) {
			//select table columns
			$select = '
				id,
				ip_address,
				timestamp,
				user_id,
				data
			';

			// specify database table
			$this->_database->table( $this->_databaseTableName );

			// where id is session id
			$this->_database->where( 'id', $sessId );

			// set fetch mode
			$this->_database->fetch( Database::PDO_FETCH_SINGLE );

			// return results
			return $this->_database->runSelectQuery( $select );
		}

		/**
		 * Get session data from database and set the php session.
		 *
		 * Determine if session exists in the database or not and then create or get current database data and setup the session.
		 * 
		 * @return void
		 */
		public function initializeSessFromDb() {
			// gloabl things
			$ipAddress = $_SERVER['REMOTE_ADDR'];
			$timestamp = time();

			if ( $this->_initialSessId == $this->_regeneratedSessId ) { // get or insert new session db record
				// get session from database
				$dbSessInfo = $this->getSessFromDb( $this->_regeneratedSessId );

				if ( !$dbSessInfo ) { // session exists
					// insert new row with regenerated id
					$dbSessInfo = $this->insertSessIntoDb( $this->_regeneratedSessId, $ipAddress, $timestamp );	
				}
			} else { // update db with new regenerated session id
				// get initial session from database
				$dbSessInfo = $this->getSessFromDb( $this->_initialSessId );

				//delete old row
				$this->deleteSessFromDb( $this->_initialSessId );

				// insert new row with regenerated id
				$dbSessInfo = $this->insertSessIntoDb( $this->_regeneratedSessId, $ipAddress, $timestamp, $dbSessInfo['data'] );		
			}

			// set the db info in the session
			$this->setSessFromDb( $dbSessInfo );
		}

		/**
		 * Insert session into the database.
		 *
		 * Create an array of session data and store it in the database.
		 * 
		 * @param string $sessId ID of the session row to get.
		 * @param string $data Session data to be stored in the database.
		 * @return array
		 */
		public function insertSessIntoDb( $sessId, $ipAddress, $timestamp, $data = '' ) {
			// get data from db
			$unserializedData = unserialize( $data );
			$unserializedData['id'] = $sessId;
			$unserializedData['ip_address'] = $ipAddress;
			$unserializedData['timestamp'] = $timestamp;

			$insertData = array( // data to insert with the array keys being the column names
				'id' => $sessId,
				'ip_address' => $ipAddress,
				'timestamp' => $timestamp,
				'user_id' => isset( $unserializedData['user_id'] ) ? $unserializedData['user_id'] : 0,
			);

			if ( $data ) { // inserting with data
				$insertData['data'] = serialize( $unserializedData );
			}

			// specify database table
			$this->_database->table( $this->_databaseTableName );
			
			// run the insert
			$this->_database->runInsertQuery( $insertData );

			// return session data
			return $insertData;
		}

		/**
		 * Set data array in the session.
		 *
		 * Add key value pair to the data array at store it in the db session.
		 *
		 * @param string $key Key in the session data.
		 * @param string $value Value for the key in the session data.
		 * @return void
		 */
		public function setData( $key, $value ) {
			// save key value to class var
			$this->_sessData[$key] = $value;

			// save to php session
			$_SESSION[$key] = $value;			

			// update db data
			$this->updateDbSessData();
		}

		/**
		 * Set session data.
		 *
		 * Add each column with its value to the php session.
		 *
		 * @param array $dbSessInfo Row from the sessions table in the database.
		 * @return void
		 */
		public function setSessFromDb( $dbSessInfo ) {
			foreach ( $dbSessInfo as $key => $value ) { // loop over columns from the db
				// set session key value
				$_SESSION[$key] = $value;

				// save key value to class var
				$this->_sessData[$key] = $value;
			}

			// get data from db
			$dbSessData = isset( $dbSessInfo['data'] ) ? unserialize( $dbSessInfo['data'] ) : '';

			if ( $dbSessData ) { // we have session data
				foreach ( $dbSessData as $key => $value ) { // loop over data
					// save key value to class var
					$this->_sessData[$key] = $value;
				}
			}
		}

		/**
		 * Set session id.
		 *
		 * Check and see if we need to regenerate session id if the specified time is up.
		 *
		 * @return void
		 */
		public function setSessId() {
			// save session id
			$this->_initialSessId = isset( $_COOKIE[$this->_sessName] ) ? $_COOKIE[$this->_sessName] : $this->_initialSessId;

			if ( ( empty( $_SERVER['HTTP_X_REQUESTED_WITH'] ) OR strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) !== 'xmlhttprequest' ) ) { // regen when not ajax
				if ( !isset( $_SESSION[$this->_sessLastRegenKey] ) ) { // no regenerated timestamp in the session
					// set session last regen time
					$_SESSION[$this->_sessLastRegenKey] = time();
				} elseif ( $_SESSION[$this->_sessLastRegenKey] < ( time() - $this->_sessIdTimeToRegen ) ) { // need to regenerate session id
					// set session last regen time
					$_SESSION[$this->_sessLastRegenKey] = time();

					// regenerate session id
					session_regenerate_id( FALSE );
				}
			} elseif ( isset( $_COOKIE[$this->_sessName] ) && $_COOKIE[$this->_sessName] === $this->_initialSessId ) {
				setcookie( // set cookie
					$this->_sessName, // name
					$this->_initialSessId, // id
					time() + $this->_secsTillExpire, // date as a timestamp when cookie expires
					'/', // path
					'', // domain
					FALSE, // will only set if https
					TRUE // accessible only with http protocal
				);
			}

			// store regenerated session id
			$this->_regeneratedSessId = session_id();
		}

		/**
		 * Set session user id.
		 *
		 * Store and update the user id in the database for the current session.
		 *
		 * @return void
		 */
		public function setSessUserId( $userId ) {
			// set in the session
			$_SESSION['user_id'] = $userId;

			// save key value to class var
			$this->_sessData['user_id'] = $userId;

			// update db
			$updateData = array( // data to update with the array keys being the column names
				'user_id' => $userId
			);

			// specify database table
			$this->_database->table( $this->_databaseTableName );

			// set where
			$this->_database->where( 'id', session_id() );

			// return results
			$this->_database->runUpdateQuery( $updateData );
		}

		/**
		 * Update session data in the dastabase.
		 *
		 * Store the session data in the data column for the the session.
		 * 
		 * @return void
		 */
		public function updateDbSessData() {
			$updateData = array( // data to update with the array keys being the column names
				'data' => serialize( $this->_sessData )
			);

			// specify database table
			$this->_database->table( $this->_databaseTableName );

			// set where
			$this->_database->where( 'id', session_id() );

			// return results
			$this->_database->runUpdateQuery( $updateData );	
		}

		/**
		 * Set data array in the session.
		 *
		 * Add key value pair to the data array at store it in the db session.
		 *
		 * @param string $key Key in the session data.
		 * @param string $value Value for the key in the session data.
		 * @return void
		 */
		public function unsetData( $key ) {
			// save to php session
			unset( $_SESSION[$key] );

			// save key value to class var
			unset( $this->_sessData[$key] );

			// update db data
			$this->updateDbSessData();
		}
	}