<?php
	
	namespace Jesse\SimplifiedMVC\Database\queryBuilder\Dialect;
	
	class MySQLDialect extends Dialect
	{
		public function autoIncrement() : self
		{
			$this->query = rtrim($this->query, ',');
			$this->query .= ' AUTO_INCREMENT';
			return $this;
		}
		public function primaryKey() : self
		{
			$this->bigInteger('id')->unsigned()->autoIncrement()->notNull();
			$this->query = rtrim($this->query, ',');
			$this->query .= ' PRIMARY KEY,';
			return $this;
		}
		public function primary() : self
		{
			return $this->primaryKey();
		}
		/**
		 * MySQL and MariaDB do this automatically with some commands
		 */
		public function addTimestamps(): self
		{
			
			$this->dateTime('created_at')->notNull()->defaults('UTC_TIMESTAMP');
			$this->dateTime('updated_at')->defaults('NULL ON UPDATE UTC_TIMESTAMP');
			return $this;
		}
		
		
		/**
		 * Not used in MySQL
		 **/
		public function createTrigger() : self
		{
			return $this;
		}
		/**
		 * Not used in MySQL
		 **/
		public function dropTrigger() : self
		{
			return $this;
		}
	}