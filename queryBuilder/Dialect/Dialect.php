<?php
	
	namespace Jesse\SimplifiedMVC\Database\queryBuilder\Dialect;
	
	use Exception;
	
	abstract class Dialect
	{
		protected string $query;
		public string $tableName;
		public bool $createNewTable;
		public bool $returnsData;
		public bool $hasParams;
		private array $bindableValues = [];
		
		/**
		 * check if the query holds the key word 'WHERE' cause i dont want to copy pasta the code to check all over the place
		 * @return bool true if found false is not
		 */
		private function checkWhere() : bool
		{
			return str_contains($this->query, 'WHERE');
		}
		
		public function createTable(string $table) : self
		{
			$this->createNewTable = true;
			$this->returnsData = false;
			$this->hasParams = false;
			$this->tableName = $table;
			$this->query = "CREATE TABLE IF NOT EXISTS `{$table}` (";
			return $this;
		}
		public function dropTable(string $table) : self
		{
			$this->createNewTable = false;
			$this->hasParams = false;
			$this->returnsData = false;
			$this->query = "DROP TABLE IF EXISTS `{$table}`";
			return $this;
		}
		public function renameTable(string $table, string $newName) : self
		{
			$this->createNewTable = false;
			$this->hasParams = false;
			$this->returnsData = false;
			$this->query = "ALTER TABLE `{$table}` RENAME TO `{$newName}`";
			return $this;
		}
		public function renameColumn(string $table, string $column, string $newName) : self
		{
			$this->createNewTable = false;
			$this->hasParams = false;
			$this->returnsData = false;
			$this->query .= "ALTER TABLE `{$table}` RENAME COLUMN `{$column}` TO `{$newName}`";
			return $this;
		}
		public function dropColumn(string $table, string $column) : self
		{
			$this->returnsData = false;
			$this->hasParams = false;
			$this->createNewTable = false;
			$this->query .= "ALTER TABLE `{$table}` DROP COLUMN `{$column}`";
			return $this;
		}
		
		/**
		 * this method requires that you use the other dialect columns to complete. failing to do this will throw errors.
		 *
		 * ```
		 *     Dialect->addColumn('myTableName')->tinyInteger('active')->notNull()->defaults(1);
		 * ```
		 *
		 * @param string $table table name to add column to
		 *
		 * @return $this
		 */
		public function addColumn(string $table) : self
		{
			$this->returnsData = false;
			$this->createNewTable = false;
			$this->query .= "ALTER TABLE `{$table}` ADD COLUMN";
			return $this;
		}
		public function select(string $table, array $columns) : self
		{
			$this->returnsData = true;
			$this->hasParams = false;
			$this->createNewTable = false;
			$this->bindableValues = [];
			$this->query = "SELECT ";
			foreach ($columns as $column) {
				if ($column == '*') {
					$this->query .= "{$column},";
					continue;
				}
				$this->query .= "`{$column}`,";
			}
			$this->query = rtrim($this->query, ",") . " FROM `{$table}`";
			return $this;
		}
		public function insert(string $table, array $columnsAndValues) : self {
			$this->returnsData = true;
			$this->hasParams = true;
			$this->bindableValues = [];
			$this->createNewTable = false;
			$this->query = "INSERT INTO `{$table}` (";
			foreach ($columnsAndValues as $column => $value)
			{
				$this->query .= "`{$column}`,";
				$this->bindableValues[] = $value;
			}
			$this->query = rtrim($this->query, ",") . ") VALUES (";
			foreach ($columnsAndValues as $column => $value)
			{
				$this->query .= "?,";
			}
			$this->query = rtrim($this->query, ",") . ")";
			return $this;
		}
		public function update(string $table, array $columnsAndValues) : self
		{
			$this->returnsData = true;
			$this->hasParams = true;
			$this->bindableValues = [];
			$this->createNewTable = false;
			$this->query = "UPDATE `{$table}` SET ";
			foreach ($columnsAndValues as $column => $value) {
				$this->query .= "`{$column}` = ?, ";
				$this->bindableValues[] = $value;
			}
			$this->query = rtrim($this->query, ",");
			return $this;
		}
		public function delete(string $table) : self
		{
			$this->returnsData = true;
			$this->hasParams = false;
			$this->createNewTable = false;
			$this->query = "DELETE FROM `{$table}`";
			return $this;
		}
		public function where(string $column, string $operator, string $value) : self
		{
			$this->bindableValues[] = $value;
			$this->hasParams = true;
			if (!$this->checkWhere()) { $this->query .= " WHERE"; }
			$this->query .= " `{$column}` {$operator} ?";
			return $this;
		}
		public function orWhere(string $column, string $operator, string $value) : self
		{
			$this->query .= " OR ";
			$this->where($column, $operator, $value);
			return $this;
		}
		public function andWhere(string $column, string $operator, string $value) : self
		{
			$this->query .= " AND ";
			$this->where($column, $operator, $value);
			return $this;
		}
		public function between(string $column, string $start, string $end) : self
		{
			$this->hasParams = true;
			$this->bindableValues[] = $start;
			$this->bindableValues[] = $end;
			if (!$this->checkWhere()) { $this->query .= " WHERE"; }
			$this->query .= " `{$column}` BETWEEN ? AND ?";
			return $this;
		}
		public function orBetween(string $column, string $start, string $end) : self
		{
			$this->query .= " OR ";
			$this->between($column, $start, $end);
			return $this;
		}
		public function andBetween(string $column, string $start, string $end) : self
		{
			$this->query .= " AND ";
			$this->between($column, $start, $end);
			return $this;
		}
		public function like(string $column, string $value) : self
		{
			$this->bindableValues[] = "%{$value}%";
			$this->hasParams = true;
			if (!$this->checkWhere()) { $this->query .= " WHERE"; }
			$this->query .= " `{$column}` LIKE ?";
			return $this;
		}
		public function orLike(string $column, string $value) : self
		{
			$this->query .= " OR ";
			$this->like($column, $value);
			return $this;
		}
		public function andLike(string $column, string $value) : self
		{
			$this->query .= " AND ";
			$this->like($column, $value);
			return $this;
		}
		public function join(string $controlTable, string $foreignTable, string $controlColumn, string $operator, string $foreignKeyColumn, ?string $joinModifyer = "") : self
		{
			if (strlen($joinModifyer) > 0) { $joinModifyer = " {$joinModifyer}"; }
			$this->query .= "{$joinModifyer} JOIN {$foreignTable} ON {$foreignTable}.{$foreignKeyColumn} {$operator} {$controlTable}.{$controlColumn}";
			return $this;
		}
		
		public function integer(string $name): self
		{
			$this->query .= " {$name} INT,";
			return $this;
		}
		public function tinyInteger(string $name, ?int $length = 255): self
		{
			if ($length > 255) {$length = 255;}
			if ($length < 1) {$length = 1;}
			$this->query .= " {$name} TINYINT({$length}),";
			return $this;
		}
		public function bigInteger(string $name): self
		{
			$this->query .= " {$name} BIGINT,";
			return $this;
		}
		public function mediumInteger(string $name): self
		{
			$this->query .= " {$name} MEDIUMINT,";
			return $this;
		}
		public function boolean(string $name): self
		{
			$this->tinyInteger($name, 1);
			return $this;
		}
		public function decimal(string $name, ?int $length = 10, ?int $decimals = 2): self
		{
			$this->query .= " {$name} DECIMAL({$length},{$decimals}),";
			return $this;
		}
		public function string(string $name, ?int $length = 255): self
		{
			$this->query .= " {$name} VARCHAR({$length}),";
			return $this;
		}
		public function text(string $name): self
		{
			$this->query .= " {$name} TEXT,";
			return $this;
		}
		public function longText(string $name): self
		{
			$this->query .= " {$name} LONGTEXT,";
			return $this;
		}
		public function mediumText(string $name): self
		{
			$this->query .= " {$name} MEDIUMTEXT,";
			return $this;
		}
		public function date(string $name): self
		{
			$this->query .= " {$name} DATE,";
			return $this;
		}
		public function time(string $name): self
		{
			$this->query .= " {$name} TIME,";
			return $this;
		}
		public function dateTime(string $name): self
		{
			$this->query .= " {$name} DATETIME,";
			return $this;
		}
		
		public function addTimestamps(): self
		{
			$this->dateTime('created_at')->notNull()->defaults('CURRENT_TIMESTAMP');
			$this->dateTime('updated_at')->defaults('NULL');
			return $this;
		}
		
		public function unique() : self
		{
			$this->query = rtrim($this->query, ",");
			$this->query .= " UNIQUE,";
			return $this;
		}
		
		/**
		 * @param mixed $default This value MUST NOT be null or ''. Can be 'null', 'NULL', or any other values.
		 *
		 * @throws Exception $default must not be empty value or actual null. May be 'NULL' but not null or ''
		 */
		public function defaults(mixed $default = null) : self
		{
			if ($default === null || is_string($default) && strlen($default) === 0) { throw new Exception("default value is null, but shouldn't be empty"); }
			$this->query = rtrim($this->query, ",");
			if ($default) { $this->query .= " DEFAULT {$default},"; }
			if (!$default) { $this->query .= " DEFAULT NULL,"; }
			return $this;
		}
		public function unsigned(): self
		{
			$this->query = rtrim($this->query, ",");
			$this->query .= " UNSIGNED,";
			return $this;
		}
		public function notNull(): self
		{
			$this->query = rtrim($this->query, ",");
			$this->query .= " NOT NULL,";
			return $this;
		}
		
		/**
		 * Takes raw query and params and overwrites the values to force the query to run, note please use the params to bind values rather than include them directly as part of the query
		 * @param string     $rawQuery SQL query string
		 * @param array|null $params Parameters array to bind to the raw sql statement - you should always bind values provided in a query to make sure to reduce the chance of injection
		 *
		 * @return $this dialog object to be run build on
		 */
		public function raw(string $rawQuery, ?array $params = []): self
		{
			$this->query = $rawQuery;
			if (!empty($params))
			{
				$this->hasParams = true;
				$this->bindableValues = $params;
			}
			return $this;
		}
		
		public function getQuery(): string
		{
			if ($this->createNewTable) // if create new table is used we need to terminate the query with a ) . this should do that
			{
				$this->query = rtrim($this->query, ",") . " )";
			}
			$x = rtrim($this->query, ',');
			$this->query = "";
			$this->createNewTable = false;
			return $x;
		}
		public function getSavedParams(): array
		{
			return $this->bindableValues;
		}
	}