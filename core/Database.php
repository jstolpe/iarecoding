<?php
	/**
	 * Database.
	 *
	 * Handles communication with the database.
	 *
	 * @package		iarecoding
	 * @subpackage	core
	 * @author		Justin Stolpe
	 * @link		https://github.com/jstolpe/iarecoding
	 * @version     1.0.0
	 */
	class Database {
		/**
		 * Fetch multiple rows.
		 *
		 * @var	strgin
		 */
		const PDO_FETCH_MULTI = 'multi';

		/**
		 * Fetch a single associative array.
		 *
		 * @var	strgin
		 */
		const PDO_FETCH_SINGLE = 'single';

		/**
		 * Database connection.
		 *
		 * @var	object
		 */
		private $_conn;

		/**
		 * Execute parameters.
		 *
		 * @var	array
		 */
		private $_executeParams;

		/**
		 * Fetch type.
		 *
		 * @var	array
		 */
		private $_fetchType;

		/**
		 * SQL string.
		 *
		 * @var	string
		 */
		private $_sql;

		/**
		 * Table name.
		 *
		 * @var	string
		 */
		private $_tableName;

		/**
		 * Where things.
		 *
		 * @var	array
		 */
		private $_where;				

		/**
		 * Join things.
		 *
		 * @var	array
		 */
		private $_join;

		/**
		 * Order things.
		 *
		 * @var	array
		 */
		private $_orderBy;

		/**
		 * Class constructor.
		 *
		 * Setup connection to database and initialize.
		 *
		 * @param array $database Database credentials.
		 * @return void
		 */
		public function __construct( $database ) {
			try { // open pdo connection to the database
				$this->_conn = new PDO( 'mysql:host=' . $database['hostname'] . ';dbname=' . $database['database'], $database['username'], $database['password'] );

				// set pdo to return errors if they happen
				$this->_conn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

				// initialize class varibales
				$this->_initialize();
			} catch ( PDOException $e ) { // connection to database failed, die with the error message
				die( $e->getMessage() );
			}
		}

		/**
		 * Initialize class variables.
		 *
		 * Set class variables to defaults so they are ready for a new query to be setup and ran.
		 *
		 * @return void
		 */
		private function _initialize() {
			// set sql string to emtpy
			$this->_sql = '';

			// set table name to empty
			$this->_tableName = '';

			// empty array for execute params
			$this->_executeParams = array();

			// empty array for the join
			$this->_join = array();

			// empty array for the where clause
			$this->_where = array();

			// empty array for the order by clause
			$this->_orderBy = array();

			// default fetch type to empty string
			$this->_fetchType = '';
		}

		/**
		 * Build the join part of sql.
		 *
		 * Loop over and add the join array and add them to the sql query.
		 *
		 * @return void
		 */
		private function _buildJoin() {
			if ( $this->_join ) { // we have join to add
				foreach ( $this->_join as $join ) { // loop over joins array
					// add join to sql statement
					$this->_sql .= ' ' . strtoupper( $join['type'] ) . ' JOIN ' . $join['table'] . ' ON ( ' . $join['on'] . ' )';
				}
			}
		}

		/**
		 * Build the where part of sql.
		 *
		 * Loop over and add the where column/values to the sql query.
		 *
		 * @return void
		 */
		private function _buildWhere() {
			if ( $this->_where ) { // we have where clauses to add
				// add where
				$this->_sql .= ' WHERE';

				foreach ( $this->_where as $where ) { // loop over where array
					// variable name
					$columnVariableName = ':' . str_replace( '.', '', $where['column'] );

					// add where column/value to sql statement
					$this->_sql .= ' ' . $where['type'] . ' ' . $where['column'] . ' = ' . $columnVariableName;

					// map where vars to values to be used on execution
					$this->_executeParams[$columnVariableName] = $where['value'];
				}
			}
		}

		/**
		 * Build the order by part of sql.
		 *
		 * Loop over and add the order by columns and directions to the sql query.
		 *
		 * @return void
		 */
		private function _buildOrderBy() {
			if ( $this->_orderBy ) { // we have order
				// array to implode for sql string
				$orderBys = array();

				foreach ( $this->_orderBy as $order ) { // loop over order array
					// add order by column/direction to sql statement
					$orderBys[] = $order['column'] . ' ' . $order['direction'];
				}

				// add order by to sql string
				$this->_sql .= ' ORDER BY ' . implode( ', ', $orderBys );
			}
		}

		/**
		 * Run a query.
		 *
		 * Run the query on the database and return the result.
		 *
		 * @return array $result Data from the database query.
		 */
		private function _runQuery() {
			// pepare for launch
			$pdo = $this->_conn->prepare( $this->_sql );

			// set to fetch a nice array :)
			$pdo->setFetchMode( PDO::FETCH_ASSOC );

			// do the query
			$pdo->execute( $this->_executeParams );

			if ( Database::PDO_FETCH_SINGLE == $this->_fetchType ) { // fetching single array 
				$result = $pdo->fetch();
			} elseif ( Database::PDO_FETCH_MULTI == $this->_fetchType ) { // fetch multidimensional array
				$result = $pdo->fetchAll();
			} else { // return last row id
				$result = $this->_conn->lastInsertId();
			}

			// initialize class varibales
			$this->_initialize();

			// return
			return $result;
		}

		/**
		 * Set fetch.
		 *
		 * Set the fetch type the pdo result with use.
		 *
		 * @param string $fetchType Accepts 'multi', or 'single'.
		 * @return void
		 */
		public function fetch( $fetchType ) {
			$this->_fetchType = $fetchType;
		}

		/**
		 * Set join.
		 *
		 * Set a join for use when running the query.
		 *
		 * @param string $type        Type of join (left, right, etc).
		 * @param string $tableString Name of the table for joining.
		 * @param string $on          ON string for the join.
		 * @return void
		 */
		public function join( $type, $tableString, $onString ) {
			$this->_join[] = array(
				'type' => $type,
				'table' => $tableString,
				'on' => $onString
			);
		}

		/**
		 * Set order by.
		 *
		 * Set order by for use when running the query.
		 *
		 * @param string $column    Name of the column in for order.
		 * @param string $direction Value for the direction of the column for order.
		 * @return void
		 */
		public function orderBy( $column, $direction ) {
			$this->_orderBy[] = array(
				'column' => $column,
				'direction' => $direction
			);
		}

		/**
		 * Run custom query.
		 *
		 * Run a custom query on the database.
		 *
		 * @param string $sql    String of the sql to run. This should contain variables in place of the values.
		 * @param array  $params Array of the params. The keys should be the variables in the $sql. The values should be the actual values.
		 * @return void
		 */
		public function runCustomQuery( $sql, $params = array() ) {
			// store the sql
			$this->_sql = $sql;

			// store the params
			$this->_executeParams = $params;

			// run the query
			return $this->_runQuery();
		}

		/**
		 * Run delete query.
		 *
		 * Run a delete query on the database.
		 *
		 * @param array $deleteData Array keys must be the name of the columns in the database table.
		 * @return void
		 */
		public function runDeleteQuery() {
			// initialize sql
			$this->_sql = '
				DELETE FROM
					' . $this->_tableName;

			// build where clause
			$this->_buildWhere();

			// run query
			return $this->_runQuery();
		}

		/**
		 * Run insert query.
		 *
		 * Run an insert query on the database.
		 *
		 * @param array $insertData Array keys must be the name of the columns in the database table.
		 * @return integer ID of the row inserted.
		 */
		public function runInsertQuery( $insertData ) {
			// initialize sql
			$this->_sql = '
				INSERT INTO ' .
					$this->_tableName . '(' .
						implode( ',', array_keys( $insertData ) ) .
					') 
				VALUES (' .
					':' . implode( ',:', array_keys( $insertData ) ) .
				')';

			foreach ( $insertData as $column => $value ) { // loop over insert data and map vars to values to be used on execution
				$this->_executeParams[':' . $column] = $value;
			}

			// run query
			return $this->_runQuery();		
		}

		/**
		 * Run select query.
		 *
		 * Run a select query on the database.
		 *
		 * @param string $select Comma separated column names to select.
		 * @return array Data returned from the database.
		 */
		public function runSelectQuery( $select = '*' ) {
			// initialize sql
			$this->_sql = '
				SELECT
					' . $select . '
				FROM
					' . $this->_tableName;

			// build where clause
			$this->_buildJoin();

			// build where clause
			$this->_buildWhere();

			// order by
			$this->_buildOrderBy();

			// run query
			return $this->_runQuery();
		}

		/**
		 * Run update query.
		 *
		 * Run an update query on the database.
		 *
		 * @param array $updateData Array keys must be the name of the columns in the database table.
		 * @return void
		 */
		public function runUpdateQuery( $updateData ) {
			// SET part of the sql query
			$setSql = '';
			
			// row counterer
			$count = 1;

			foreach ( $updateData as $column => $value ) { // loop over update data
				// add column and column variable to set sql
				$setSql .= $column . ' = :' . $column . ( $count != count( $updateData ) ? ', ' : '' );
				
				// assign column variable to the value in the execute params
				$this->_executeParams[':' . $column] = $value;

				// increment row counter
				$count++;
			}

			// initialize sql
			$this->_sql = '
				UPDATE 
					' . $this->_tableName . '
				SET 
					' .$setSql;

			// build where clause
			$this->_buildWhere();

			// run query
			$this->_runQuery();
		}

		/**
		 * Set tablename.
		 *
		 * Set the tableName class var.
		 *
		 * @param string $tableName Database table to use with the query.
		 * @return void
		 */
		public function table( $tableName ) {
			$this->_tableName = $tableName;
		}

		/**
		 * Set where.
		 *
		 * Set a where clause for use when running the query.
		 *
		 * @param string $column Name of the column in the where clause.
		 * @param string $value  Value for the column in the where clause.
		 * @param string $type   Type of where clause. Accepts 'AND', or 'OR'. Default empty;
		 * @return void
		 */
		public function where( $column, $value, $type = '' ) {
			$this->_where[] = array(
				'column' => $column,
				'value' => $value,
				'type' => $type
			);
		}
	}