<?php
	
	namespace Jesse\SimplifiedMVC\Database\queryBuilder\Dialect;
	
	class SQLiteDialect extends Dialect
	{
		public boolean $usedTimestamps;
		public function autoIncrement() : self
		{
			$this->query .= ' AUTOINCREMENT';
			return $this;
		}
		public function primaryKey() : self
		{
			$this->integer('id')->autoIncrement()->notNull();
			$this->query .= ' PRIMARY KEY';
			return $this;
		}
		public function primary() : self
		{
			return $this->primaryKey();
		}
		/**
		 * Run createTimeStampUpdateTrigger(string tableName) after table generation when doing this with sqlite
		 */
		public function addTimestamps(): self
		{
			$usedTimestamps = true;
			$this->query .= $this->dateTime('created_at')->notNull()->defaults('CURRENT_TIMESTAMP');
			$this->query .= $this->dateTime('updated_at')->defaults('NULL');
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
					UPDATE {$tn} SET updated_at = CURRENT_TIMESTAMP WHERE id = OLD.id
				END";
			return $query;
		}
		public function dropTrigger() : string
		{
			$query = "";
			$tn = $this->tableName;
			$query = "DROP TRIGGER IF NOT EXISTS {$tn}_update_timestamp_trigger";
			return $query;
		}
	}