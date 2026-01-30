<?php
require_once("DatabaseConnect.class.php");

class ActiveRecord extends DatabaseConnect {
	public $table='',
			$condition='',
			$limit='',
			$order='';

	public function select($rows='*') {
		if($this->table==='') throw new DatabaseConnException("table name is empty","select method");

		$query = "SELECT ".trim($rows)." FROM ".TTH_DATA_PREFIX.trim($this->table);
		if(trim($this->condition)!=='') $query .= " WHERE ".trim($this->condition);
		if(trim($this->order)!=='') $query .= " ORDER BY ".trim($this->order);
		if(trim($this->limit)!=='') $query .= " LIMIT ".trim($this->limit);

		$result = $this->query($query);

		// Auto reset để tránh ảnh hưởng query tiếp theo
		$this->condition = '';
		$this->order = '';
		$this->limit = '';

		if($result!=='' or $result!==NULL) return $result;
	}

	public function sql_query($query='*') {
		$query = trim($query);
		$result = $this->query($query);
		if($result!=='' or $result!==NULL) return $result;
	}
	 
	public function insert($data) {
		if($this->table==='') throw new DatabaseConnException("table name is empty","select method");
		if(!is_array($data) or empty($data)) throw new DatabaseConnException("data must be an array, and it can not be empty","insert method");
		 
		$rows = '';
		$values = '';
		$index = 1;
		 
		foreach($data as $key => $value) {
			if($index == count($data)) {
				$rows .= "`".$key."`";
				$values .= "'".$value."'";
				continue;
			}
			$rows .= "`".$key."`,";
			$values .= "'".$value."'".',';
			$index++;
		}
		 
		$query = "INSERT INTO ".TTH_DATA_PREFIX.trim($this->table)."($rows) VALUES ($values)";
		$this->query($query);
		 
	}
	 
	public function update($data) {
		if($this->table==='') throw new DatabaseConnException("table name is empty","select method");
		if(!is_array($data) or empty($data))throw new DatabaseConnException("data must be an array, and it can not be empty","update method");
		 
		$rows = '';
		$index = 1;
		 
		foreach($data as $key => $value) {
			if($index == count($data))
			{
				$rows .= "`".$key. "` = '".$value."'";
				continue;
			}
			$rows .= "`".$key. "` = '".$value."'".',';
			$index++;
		}
		$query = "UPDATE ".TTH_DATA_PREFIX.trim($this->table)." SET $rows";

		if($this->condition!=='')
			$query .= " WHERE ".trim($this->condition);

		$this->query($query);

		// Auto reset để tránh ảnh hưởng query tiếp theo
		$this->condition = '';
		$this->order = '';
		$this->limit = '';
	}

	public function delete() {
		if($this->table==='')  throw new DatabaseConnException("table name is empty","select method");
		if($this->condition==='') throw new DatabaseConnException("delete a record without condition","delete method");
		 
		$query = "DELETE FROM ".TTH_DATA_PREFIX.trim($this->table)." WHERE ".trim($this->condition);
		$this->query($query);

		// Auto reset để tránh ảnh hưởng query tiếp theo
		$this->condition = '';
		$this->order = '';
		$this->limit = '';
	}

	public function optimize()
	{
		$query = "OPTIMIZE TABLE ".TTH_DATA_PREFIX.trim($this->table);
		$this->query($query);
	}

	public function showtables() {
		$query = "SHOW TABLES";
		$result = $this->query($query);
		if($result!=='' or $result!==NULL) return $result;
	}

	public function showtablestatus() {
		if($this->table==='') throw new DatabaseConnException("table name is empty","select method");

		$query = "SHOW TABLE STATUS LIKE '".TTH_DATA_PREFIX.trim($this->table)."'";
		$result = $this->query($query);
		if($result!=='' or $result!==NULL) return $result;
	}

	public function showcreatetable() {
		if($this->table==='') throw new DatabaseConnException("table name is empty","select method");

		$query = "SHOW CREATE TABLE ".trim($this->table);
		$result = $this->query($query);
		if($result!=='' or $result!==NULL) return $result;
	}

	public function showDbInfo(){
		$query = "SELECT IF(@@session.time_zone = 'SYSTEM', @@system_time_zone, @@session.time_zone) AS `db_time_zone`, @@session.character_set_database AS `db_charset`, @@session.collation_database AS `db_collation`";
		$result = $this->query($query);
		if($result!=='' or $result!==NULL) return $result;

	}

	/**
	 * Select one record
	 */
	public function selectOne($rows='*') {
		$this->limit = '1';
		$result = $this->select($rows);
		if(!empty($result) && is_array($result)) {
			return $result[0];
		}
		return null;
	}

	/**
	 * Count records
	 */
	public function count() {
		if($this->table==='') throw new DatabaseConnException("table name is empty","count method");

		$query = "SELECT COUNT(*) as total FROM ".TTH_DATA_PREFIX.trim($this->table);
		if(trim($this->condition)!=='') $query .= " WHERE ".trim($this->condition);

		$result = $this->query($query);

		// Auto reset
		$this->condition = '';
		$this->order = '';
		$this->limit = '';

		if(!empty($result) && isset($result[0]['total'])) {
			return (int)$result[0]['total'];
		}
		return 0;
	}

	/**
	 * Get last insert ID
	 */
	public function lastInsertId() {
		return $this->conn->insert_id;
	}
}