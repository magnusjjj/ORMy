<?php
	class MysqlDriver
	{
		function connect()
		{
			mysql_connect( "", "", "" );
			mysql_select_db( "" );
		}
		
		function safe( $var )
		{
			return mysql_real_escape_string( $var );
		}
		
		function query( $query )
		{
			return mysql_query( $query );
		}
		
		function row_number( $result )
		{
			return mysql_num_rows( $result );
		}
		
		function get_row( $result )
		{
			return mysql_fetch_assoc( $result );
		}
		
		function get_error()
		{
			return mysql_error();
		}
	}

	class ORM extends MysqlDriver
	{
		var $__table = "";
		var $__ans = null;
		var $__count = 0;
		var $__errors = array();
		var $__PK = 'id';
		
		function __construct( $table )
		{
			$this->__table = $table;
		//	$this->connect();
		}
		
		public static function getInstance( $table )
		{
			if(file_exists("models/$table.php"))
			{
				include_once("models/$table.php");
				$objname = $table."Model";
				return new $objname($table);
			} else {
				return new ORM($table);
			}
		}
		
		/* Model */
		
		function count()
		{
			return $this->__count;
		}
		
		/* Get data from Database.
		Split into two seperate functions, one for easy access by PK, one for more advanced constraints */
		
		
		function get( $by )
		{
			$by = $this->safe( $by );
			$this->__ans = $this->query( "SELECT * FROM ".$this->__table." WHERE ".$this->__PK." = '$by' LIMIT 1;" ); // TODO: Automatically find out primary key?
			if( !$this->__ans )
			{
				$this->__errors[] = $this->get_error();
				$this->__count = 0;
			} else {
				$this->__count = $this->row_number( $this->__ans );
			}
			return $this->get_row( $this->__ans );
		}
		
		function getBy( $by )
		{
			$sql = "SELECT * FROM ".$this->__table." WHERE ";
			end( $by );
			$end = key( $by );
			
			foreach($by as $key => $value)
			{
				$sql .= "`".$this->safe($key)."` = '". $this->safe( $value ) ."'";
				if($end != $key)
				{
					$sql .= ' AND ';
				}
			}
			
			$sql .= " AND 1=1;";
			
			$this->__ans = $this->query( $sql );
			
			if( !$this->__ans )
			{
				$this->__errors[] = $this->get_error();
			} else {
				return $this->getList();
			}
		}
		
		function getList($ans = null)
		{
			if(is_null($ans))
				$ans = $this->__ans;
			
			$arr = array();
			while( $row = $this->get_row( $this->__ans ) )
			{
				$arr[] = $row;
			}
			return $arr;
		}
		
		function save( $values )
		{
			$sql = "";
			if( empty( $values[$this->__PK] ) )
			{
				$sql = "INSERT INTO " . $this->__table . " SET ";
			} else {
				$sql = "UPDATE " . $this->__table . " SET ";
			}
			end( $values );
			$end = key( $values );
			foreach($values as $key => $value)
			{
				$sql .= "`".$this->safe($key)."` = '". $this->safe( $value ) ."'";
				if($end != $key)
				{
					$sql .= ', ';
				}
			}
			$this->__ans = $this->query( $sql );
			
			if( !$this->__ans )
			{
				$this->__errors[] = $this->get_error();
			}
		}
	}
?>
 

