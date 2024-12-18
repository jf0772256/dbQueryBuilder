<?php
	
	namespace Jesse\SimplifiedMVC\Database\queryBuilder\Dialect;
	
	class SQLiteDialect extends Dialect
	{
		public bool $usedTimestamps;
		public function autoIncrement() : self
		{
			$this->query .= ' AUTOINCREMENT';
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
			$this->dateTime('created_at')->notNull()->defaults('CURRENT_TIMESTAMP');
			$this->dateTime('updated_at')->defaults('NULL');
			return $this;
		}
		
		public function createTrigger() : string
		{
			$query = "";
			$tn = $this->tableName;
			$query = "CREATE TRIGGER IF NOT EXISTS {$tn}_update_timestamp_trigger
				AFTER UPDATE ON {$tn}
				FOR EACH ROW
				WHEN new.updated_at = old.updated_at
				BEGIN
					UPDATE {$tn} SET updated_at = CURRENT_TIMESTAMP WHERE id = OLD.id;
				END";
			return $query;
		}
		public function dropTrigger() : string
		{
			$query = "";
			$tn = $this->tableName;
			$query = "DROP TRIGGER IF EXISTS {$tn}_update_timestamp_trigger";
			return $query;
		}
	}