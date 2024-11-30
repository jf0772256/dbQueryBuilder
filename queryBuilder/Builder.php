<?php
	
	namespace Jesse\SimplifiedMVC\Database\queryBuilder;
	
	use Exception;
	use Jesse\SimplifiedMVC\Database\Database\Connection;
	use Jesse\SimplifiedMVC\Database\queryBuilder\Dialect\Dialect;
	use Jesse\SimplifiedMVC\Database\queryBuilder\Dialect\MySQLDialect;
	use Jesse\SimplifiedMVC\Database\queryBuilder\Dialect\SQLiteDialect;
	use PDOStatement;
	
	class Builder
	{
		private static Connection $connection;
		private string $dbDialect;
		private Dialect $dialect;
		
		/**
		 * Important Use the primary_key or primary methods to set the primary key, this will set the primary key field as 'id' and will not break the program unlike if you change the key name!
		 * @throws Exception
		 */
		public function __construct(Connection $connection, string $dbDialect)
		{
			static::$connection = $connection;
			$this->dbDialect = $dbDialect;
			switch ($dbDialect) {
				case 'mysql':
					$this->dialect = new MySQLDialect();
					break;
				case 'sqlite':
					$this->dialect = new SQLiteDialect();
					break;
				default:
					throw new Exception("Unsupported db dialect", 5000);
			}
		}
		
		/**
		 * Important Use the primary_key or primary methods to set the primary key, this will set the primary key field as 'id' and will not break the program unlike if you change the key name!
		 * @return Dialect
		 */
		public function builder(): Dialect
		{
			return $this->dialect;
		}
		
		/**
		 * Important Use the primary_key or primary methods to set the primary key, this will set the primary key field as 'id' and will not break the program unlike if you change the key name!
		 * @param Dialect $dialect Dialect object to be used.
		 *
		 * @return $this
		 */
		public function build(Dialect $dialect): Builder|PDOStatement
		{
			$query = $dialect->getQuery();
			//echo "<pre>";
			//print_r(["dialect" => $dialect, "query" => $query]);
			//echo "</pre>";
			//die();
			$data = $dialect->hasParams ? static::$connection->ExecuteQuery($query, $dialect->getSavedParams()) : static::$connection->ExecuteQuery($query);
			if (!($dialect instanceof MySQLDialect) && $dialect->createNewTable && $dialect->usedTimestamps)
			{
				$query = $dialect->createTrigger();
				//echo "<pre>";
				//print_r($query);
				//echo "</pre>";
				//die();
				static::$connection->ExecuteQuery($query, $dialect->getSavedParams());
			}
			
			return $dialect->returnsData ? $data : $this;
		}
	}