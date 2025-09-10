<?php
	
	namespace Jesse\SimplifiedMVC\Database\queryBuilder\Dialect;
	
	class SQLServerDialect extends Dialect
	{
		public bool $usedTimestamps;
		
		public function createTable(string $table) : self
		{
			$this->createNewTable = true;
			$this->returnsData = false;
			$this->hasParams = false;
			$this->tableName = $table;
			$this->query = "CREATE TABLE `{$table}` (";
			return $this;
		}
		public function dropTable(string $table) : self
		{
			$this->createNewTable = false;
			$this->hasParams = false;
			$this->returnsData = false;
			$this->query = "DROP TABLE `{$table}`";
			return $this;
		}
		public function autoIncrement() : self
		{
			$this->query .= ' IDENTITY(1,1)';
			return $this;
		}
		public function primaryKey() : self
		{
			$this->pkInteger('id');
			$this->query = rtrim($this->query, ',');
			$this->query .= ' PRIMARY KEY';
			$this->autoIncrement()->notNull();
			return $this;
		}
		public function primary() : self
		{
			return $this->primaryKey();
		}
		public function pkInteger(string $name) : self
		{
			$this->query .= " {$name} INTEGER,";
			return $this;
		}
		/**
		 * Run createTimeStampUpdateTrigger(string tableName) after table generation when doing this with sqlite
		 */
		public function addTimestamps(): self
		{
			$this->usedTimestamps = true;
			$this->dateTime2('created_at')->notNull()->defaults('SYSUTCDATETIME()');
			$this->dateTime2('updated_at')->defaults('NULL');
			return $this;
		}
		
		public function createTrigger() : string
		{
			$query = "";
			$tn = $this->tableName;
			$query = "CREATE TRIGGER {$tn}_update_timestamp_trigger
				ON {$tn}
				FOR UPDATE AS UPDATE T
				SET updated_at = SYSUTCDATETIME()
				FROM {$tn} AS T
				JOIN inserted AS I ON T.id = I.id;";
			return $query;
		}
		public function dropTrigger() : string
		{
			$query = "";
			$tn = $this->tableName;
			$query = "DROP TRIGGER {$tn}_update_timestamp_trigger";
			return $query;
		}
		
		public function datetime2(string $name, int $precision = 7) : self
		{
			$this->query .= " {$name} DATETIME2({$precision}),";
			return $this;
		}
		
		public function float(string $name, int $n = 53) : self
		{
			$this->query .= " {$name} FLOAT({$n}),";
			return $this;
		}
		
		public function real(string $name) : self
		{
			$this->query .= " REAL({$name}),";
			return $this;
		}
		
		public function json(string $name) : self
		{
			$this->query .= " {$name} JSON,";
			return $this;
		}
		public function varbinary(string $name, int $length, ?bool $max = false) : self
		{
			$length = $length > 0 ? (int) $length : 1;
			if ($max)
			{
				$this->query .= " {$name} VARBINARY(max),";
			}
			else
			{
				$this->query .= " {$name} VARBINARY({$length}),";
			}
			return $this;
		}
		public function text (string $name) : self
		{
			$this->query .= " {$name} VARCHAR(max),";
			return $this;
		}
		
		//
		//   SQL Server doesnt have these, but we want to be able to use text field without error
		//
		
		public function longText(string $name) : self { $this->text($name); return $this; }
		public function mediumText(string $name) : self { $this->text($name); return $this; }
	}