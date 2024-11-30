<?php
	
	namespace Jesse\SimplifiedMVC\Database\Database;
	
	use AllowDynamicProperties;
	use Jesse\SimplifiedMVC\Database\Database\DatabaseBase;
	use PDO;
	use PDOStatement;
	
	#[AllowDynamicProperties] class Connection extends DatabaseBase
	{
		/**
		 * @param string $connectionType must be passed and must be either 'pdo' [default] or 'sqlite'
		 */
		public function __construct(string $connectionType = "pdo")
		{
			$this->connectionType = $connectionType;
		}
		
		/**
		 * Runs a query off of the database connection interface used, returning the statement object
		 *
		 * @param string      $sql    SQL statement to be prepared and ran
		 * @param array|null  $params Parameters array to bind to the query through the statement object
		 *
		 * @see https://www.php.net/manual/en/pdostatement.fetchall.php
		 *
		 * ```php
		 *  // after creating connection instance use query() on the Connection instance
		 *  // pdo uses by default the associative arrays for array returns
		 *  $db->query("SELECT * FROM table")->fetch_all();
		 *  // sqlite interface is technically a wrapped pdo connection, it uses a different sql syntax.
		 * ```
		 *
		 * All queries are parametrised, and you can pass parameters to the query method using the params array:
		 * ```php
		 *  $id = 1;
		 *  $db->query("SELECT * FROM table WHERE id=?", [$id])->fetch();
		 * ```
		 *
		 * The final parameter is not required and will be auto generated if not passed
		 *
		 * @return PDOStatement|bool Either a PDO or mysqli statement object or a boolean if there was an error
		 * @author Jesse Fender
		 */
		public function ExecuteQuery (string $sql, ?array $params = []) : PDOStatement|bool
		{
				// run prepared query and return the statement object to caller;
				$statement = $this->connection->prepare($sql);
				$statement->execute($params);
				return $statement;
		}
		
		/**
		 * @throws DatabaseBaseConnectionError
		 */
		public function connect () : void
		{
			try
			{
				//echo '<pre>';
				//print_r([$this->buildDSN(), $this->connectionType, $this->type]);
				//echo '</pre>';
				//die();
				// create connection using pdo
				$this->connection = new PDO($this->buildDSN(), $this->user, $this->pass, $this->pdoOptions);
				$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			} catch (\Throwable $th)
			{
				throw new DatabaseBaseConnectionError("Could not connect to the given database with credentials provided.", $th->getCode(), $th);
			}
		}
		
		/**
		 * Creates the DSN st ring that is used to connect
		 * @return string DSN connection string to use when generating a new connection.
		 * @author Jesse Fender
		 */
		private function buildDSN (): string
		{
			if ($this->type === "sqlite")
			{
				return "{$this->type}:{$this->name}";
			} else
			{
				return "{$this->type}:host={$this->host};port={$this->port};dbname={$this->name}";
			}
		}
	}