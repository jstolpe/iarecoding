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
		 * Where in things.
		 *
		 * @var	array
		 */
		private $_whereIn;

		/**
		 * Group things.
		 *
		 * @var	array
		 */
		private $_groups;

		/**
		 * Join things.
		 *
		 * @var	array
		 */
		private $_join;

		/**
		 * Limit things.
		 *
		 * @var	array
		 */
		private $_limit;

		/**
		 * Order things.
		 *
		 * @var	array
		 */
		private $_orderBy;

		/**
		 * Group things.
		 *
		 * @var	array
		 */
		private $_groupBy;

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
				$this->_conn = new PDO( 'mysql:host=' . $database['hostname'] . ';charset=utf8;dbname=' . $database['database'], $database['username'], $database['password'] );

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

			// empty array for the where in clause
			$this->_whereIn = array();

			// empty array for the order by clause
			$this->_orderBy = array();

			// empty array for the group by by clause
			$this->_groupBy = array();

			// empty array for the groups by clause
			$this->_groups = array();

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
			if ( $this->_where || $this->_whereIn ) { // we have where clauses to add
				// add where
				$this->_sql .= ' WHERE';

				// pdo var counter
				$varCounter = 0;

				foreach ( $this->_where as $where ) { // loop over where array
					// variable name
					$columnVariableName = ':' . str_replace( '.', '', $where['column'] ) . $varCounter;

					// operator
					$operator = isset( $where['operator'] ) ? $where['operator'] : '=';

					// add where column/value to sql statement
					$this->_sql .= ' ' . $where['type'] . ' ' . $where['column'] . ' ' . $operator . ' ' . $columnVariableName;

					// map where vars to values to be used on execution
					$this->_executeParams[$columnVariableName] = $where['value'];

					// increase counter
					$varCounter++;
				}

				foreach ( $this->_whereIn as $whereIn ) { // loop over where in array
					// variable name
					$columnVariableName = ':' . str_replace( '.', '', $whereIn['column'] ) . $varCounter;

					// add where column/value to sql statement
					$this->_sql .= ' ' . $whereIn['type'] . ' ' . $whereIn['column'] . ' ' . $whereIn['in_type'] . ' (';

					foreach ( $whereIn['value'] as $key => $value ) { // loop over array
						// variable name
						$inVariableName = $columnVariableName . 'win' . $key;
						
						// add where column/value to sql statement
						$this->_sql .= ( $key ? ',' : '' ) . $inVariableName;

						// map where vars to values to be used on execution
						$this->_executeParams[$inVariableName] = $value;
					}

					// finish in statement
					$this->_sql .= ')';

					// increase counter
					$varCounter++;
				}
			}
		}

		/**
		 * Build the groups part of sql.
		 *
		 * Loop over and add the groups column/values to the sql query.
		 *
		 * @return void
		 */
		private function _buildGroups() {
			if ( $this->_groups ) { // we have groups clauses to add
				foreach ( $this->_groups as $groupKey => $group ) { // loop over groups
					// pdo var counter
					$varCounter = 0;

					// add on the group concat type
					$this->_sql .= ' ' . $group['type'] . ' ( ';

					foreach ( $group['conditions'] as $condition ) { // loop over group conditions
						// variable name
						$columnVariableName = ':' . $groupKey . $varCounter;
						
						if ( 'IN' == $condition['operator'] || 'NOT IN' == $condition['operator'] ) { // in command is special
							// add where column/value to sql statement
							$this->_sql .= ' ' . $condition['type'] . ' ' . $condition['column'] . ' ' . $condition['operator'] . ' (';

							foreach ( $condition['value'] as $key => $value ) { // loop over array
								// variable name
								$inVariableName = $columnVariableName . 'gwin' . $key;
								
								// add where column/value to sql statement
								$this->_sql .= ( $key ? ',' : '' ) . $inVariableName;

								// map where vars to values to be used on execution
								$this->_executeParams[$inVariableName] = $value;
							}	

							// finish in statement
							$this->_sql .= ')';
						} else { // add command to sql statement
							$this->_sql .= ' ' . $condition['type'] . ' ' . $condition['column'] . ' ' . $condition['operator'] . ' ' . $columnVariableName;

							// map where vars to values to be used on execution
					 		$this->_executeParams[$columnVariableName] = $condition['value'];
					 	}

					 	// increase counter
						$varCounter++;
					}

					$this->_sql .= ' ) ';
				}
			}
		}

		/**
		 * Build the limit part of sql.
		 *
		 * Add the limit array to the sql query.
		 *
		 * @return void
		 */
		private function _buildLimit() {
			if ( $this->_limit ) { // we have limit to add
				// add to sql statement
				$this->_sql .= ' LIMIT ' . $this->_limit['limit'] . ' OFFSET ' .$this->_limit['start'];
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
		 * Build the group by part of sql.
		 *
		 * Loop over and add the group by columns and directions to the sql query.
		 *
		 * @return void
		 */
		private function _buildGroupBy() {
			if ( $this->_groupBy ) { // we have group bys
				// array to implode for sql string
				$groupBys = array();

				foreach ( $this->_groupBy as $group ) { // loop over group by array
					// add group by column to sql statement
					$groupBys[] = $group['column'];
				}

				// add group by to sql string
				$this->_sql .= ' GROUP BY ' . implode( ', ', $groupBys );
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
		 * Set group by.
		 *
		 * Set group by for use when running the query.
		 *
		 * @param string $column    Name of the column in for group.
		 * @return void
		 */
		public function groupBy( $column ) {
			$this->_groupBy[] = array(
				'column' => $column
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

			// build groups
			$this->_buildGroups();

			// order by
			$this->_buildOrderBy();

			// order by
			$this->_buildGroupBy();

			// build limit clause
			$this->_buildLimit();

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

		/**
		 * Set where custom.
		 *
		 * Set a where clause for use when running the query.
		 *
		 * @param string $column   Name of the column in the where clause.
		 * @param string $value    Value for the column in the where clause.
		 * @param string $operator Operator for the the where in type clause.
		 * @param string $type     Type of where clause. Accepts 'AND', or 'OR'. Default empty;
		 * @return void
		 */
		public function whereOperator( $column, $value, $operator, $type = '' ) {
			$this->_where[] = array(
				'column' => $column,
				'value' => $value,
				'type' => $type,
				'operator' => $operator
			);
		}

		/**
		 * Set where in.
		 *
		 * Set a where clause for use when running the query.
		 *
		 * @param string $column Name of the column in the where clause.
		 * @param string $value  Value for the column in the where clause.
		 * @param string $inType Value for the the where in type clause.
		 * @param string $type   Type of where clause. Accepts 'AND', or 'OR'. Default empty;
		 * @return void
		 */
		public function whereIn( $column, $value, $inType, $type = '' ) {
			$this->_whereIn[] = array(
				'column' => $column,
				'value' => $value,
				'in_type' => $inType,
				'type' => $type
			);
		}

		/**
		 * Set limit.
		 *
		 * Set a limit clause for use when running the query.
		 *
		 * @param integer $limit  limit number.
		 * @param integer $start  starting position.;
		 * @return void
		 */
		public function limit( $limit, $start = 0 ) {
			$this->_limit = array(
				'limit' => $limit,
				'start' => $start
			);
		}

		/**
		 * Set start.
		 *
		 * Set a group key in the array.
		 *
		 * @param string $key
		 * @param string $type
		 * @return void
		 */
		public function groupStart( $key, $type ) {
			$this->_groups[$key] = array(
				'type' => $type,
				'conditions' => array()
			);
		}

		/**
		 * Set group.
		 *
		 * Set a group clause for use when running the query.
		 *
		 * @param string $key
		 * @param string $command
		 * @param string $operator
		 * @param string $column
		 * @param string $value
		 * @return void
		 */
		public function groupAdd( $key, $column, $operator, $value, $type = '' ) {
			$this->_groups[$key]['conditions'][] = array(
				'operator' => $operator,
				'column' => $column,
				'value' => $value,
				'type' => $type
			);
		}
	}