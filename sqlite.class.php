<?php
/**
 * Handles SQLite
 *
 * Revision Description: 
 * <pre>
 *  	v1.0.0 - Initial Creation
 * </pre>
 *
 * @Name   SQLite
 * @Version    v1.0.0
 */
class SQLite extends PDO {
	private $sql;
	
	private $bind;
	/**
	*Class Construct
	*
	*Revision Description:
	*<pre>
	*  v1.0.0 - Intitial Creation
	*</pre>
	*
	*@Name  __construct
	*@param String $database_type Type of database. Can be left blank for sqlite
	*@param String $user User for database
	*@param String $password Password for database
	*/
	public function __construct($database_type = 'sqlite:database.sqlite', $user='', $password='')
	{
		//Set default options for connection
		$options = array(
			PDO::ATTR_PERSISTENT => true,
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
			);
		//Connects to database using PDO class.
		parent::__construct($database_type,$user,$password,$options);
	}
	/**
	*Deletes rows from a table.
	*
	*Example:
	*<pre>
	*	$db = new SQL;
	*	$table = "DATABASE_TABLE";
	*	$where = " column1=:column1 AND column2=:column2";
	*	$bind = array(":column1"=>'1',":column2"=>'2');
	*	$db->delete($table,$where,$bind);
	*</pre>
	*
	*Revision Description:
	*<pre>
	*  v1.0.0 - Intitial Creation
	*</pre>
	*
	*@Name delete
	*@param String $table Table to pull from
	*@param String $where Where statement for SQL
	*@param Array $bind Parameters to bind to. 
	*/
	public function delete($table, $where, $bind="")
	{
		//Build SQL Statement
		$sql = "DELETE FROM ".$table." WHERE ".$where.";";
		//Run Query
		$this->run($sql,$bind);
	}
	/**
	*Cleans up bind parameters. Makes sure they are in an array.
	*
	*Example:
	*<pre>
	*	$bind = $this->cleanup($bind);
	*</pre>
	*
	*Revision Description:
	*<pre>
	*  v1.0.0 - Intitial Creation
	*</pre>
	*
	*@Name cleanup
	*@param Array/String $bind Bind string
	*@return Array Returns bind array.
	*/
	private function cleanup($bind)
	{
		//Check if bind is in array. 
		if(!is_array($bind))
		{
			//check if bind string is empty
			if(!empty($bind))
			{
				//Put the bind parameter into an array
				$bind = array($bind);
			}else{
				//Make the bind parameter an array.
				$bind = array();
			}
		}
		//Return the bind variable. 
		return $bind;
	}
	/**
	*Inserts into database. 
	*
	*Example:
	*<pre>
	*	$db = new SQL;
	*	$table = "DATABASE_TABLE";
	*	$data = array("column1"=>"5",
	*				  "column2"=>"6");
	*	$db->insert($table,$data);
	*</pre>
	*
	*Revision Description:
	*<pre>
	*  v1.0.0 - Intitial Creation
	*</pre>
	*
	*@Name insert
	*@param String $table Table you wish to insert into
	*@param Array $data Array of data to insert.
	*/
	public function insert($table,$data)
	{
		//Define $fields array.
		$fields = array();
		//Loop through the data array.
		foreach($data as $key=>$value)
		{
			//Fill fields array with keys from $data.
			$fields[] = $key;
		}
		//Build SQL statement
		$sql = "INSERT INTO ".$table." (".implode($fields,", ").") VALUES (:".implode($fields,", :").");";
		//Define $bind array.
		$bind = array();
		//Loop through fields and put them into the bind array.
		foreach($fields as $rs)
		{
			//Build bind array
			$bind[":".$rs] = $data[$rs];
		}
		//Run query.
		$this->run($sql,$bind);
	}
	/**
	*Runs a query. 
	*
	*Example:
	*<pre>
	*	$sql = "SELECT * FROM TABLE WHERE a=:a";
	*	$bind = array(":a","5");
	*	$results = $this->run($sql,$bind);
	*	var_dump($results);
	*</pre>
	*
	*Revision Description:
	*<pre>
	*  v1.0.0 - Intitial Creation
	*</pre>
	*
	*@Name run
	*@param String $sql Sql statement to run
	*@param Array $bind Array of bind elements
	*@return Array/Boolean If it requires results it will return array of results, otherwise it will return false or nothing. 
	*/
	private function run($sql,$bind="")
	{
		//Define class variable sql
		$this->sql = trim($sql);
		//Define class array bind
		$this->bind=$this->cleanup($bind);
		//Pull smtm object from $this->prepare.
		$stmt = $this->prepare($this->sql);
		//Exectue query
		if($stmt->execute($this->bind) !== false)
		{
			//return the results of query.
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		}else{
			//if query fails return false.
			return false;
		}
	}
	/**
	*Selects from a database
	*
	*Example:
	*<pre>
	*	$db = new SQL;
	*	$table = "DATEBASE_TABLE";
	*	$where = "ID=:id";
	*	$bind = array(':id'=>'5');
	*	$fields = "ID, name";
	*	$results = $db->select($table,$where,$bind,$fields);
	*	var_dump($results);
	*</pre>
	*
	*Revision Description:
	*<pre>
	*  v1.0.0 - Intitial Creation
	*</pre>
	*
	*@Name select
	*@param String $table Table to pull from
	*@param String $where Where statement
	*@param Array $bind Array of elemends to bind to. 
	*@param String $fields Fields to pull comma delemeted. 
	*@return Array Returns array of results. 
	*/
	public function select($table, $where="", $bind="", $fields="*")
	{
		//Build SQL statement.
		$sql = "SELECT ".$fields." FROM ".$table;
		//Check if the where clause is empty. 
		if(!empty($where))
		{
			//If not add it to the sql statement.
			$sql .= " WHERE ".$where;
		}
		//Finish appending the sql statement.
		$sql .=";";
		//Run the query and return results.
		return $this->run($sql,$bind);
	}
	/**
	*Updates a table in the database.
	*
	*Example:
	*<pre>
	*	$db = new SQL;
	*	$table = "DATABASE_TABLE";
	*	$where = " ID=:id";
	*	$bind = array(":id"=>'5');
	*	$data = array('column1'=>'1','column2'=>'2');
	*	$db->update($table,$data,$where,$bind);
	*</pre>
	*
	*Revision Description:
	*<pre>
	*  v1.0.0 - Intitial Creation
	*</pre>
	*
	*@Name update
	*@param String $table Table you are going to update
	*@param Array $data Data to update in an array column as the key data as the data
	*@param String $where Where statement to update. 
	*@param Array $bind Array of bound parameters for the WHERE statement.
	*/
	public function update($table, $data, $where, $bind="")
	{
		//Define the fields array.
		$fields = array();
		//Loop through the data array.
		foreach($data as $key=>$value)
		{
			//add the keys to the $fields array,
			$fields[] = $key;
		}
		//Pull the size of the array.
		$size = sizeof($fields);
		//Start building the SQL statement,
		$sql = "UPDATE ".$table." SET ";
		//for loop the fields to build the sql statement.
		for($i=0;$i<$size;$i++)
		{
			//Check if its past the first element,
			if($i>0)
			{
				//If so add a comma to the sql. 
				$sql .= ", ";
			}
			//Add the fields to the SQL statement.
			$sql .= $fields[$i]." = :u_".$fields[$i];
		}
		//Add the where clause to the sql;
		$sql .= " WHERE ".$where.";";
		//Clean up the bind.
		$bind = $this->cleanup($bind);
		//Loop through the fields array.
		foreach($fields as $rs)
		{
			//Fill the bind array with actual data.
			$bind[':u_'.$rs] = $data[$rs];
		}
		//Execute the query.
		$this->run($sql,$bind);
	}
	/**
	*Runs a straight query. 
	*
	*Example:
	*<pre>
	*	$db = new SQL;
	*	$sql = "SELECT * FROM {TABLE} WHERE id=:id";
	*	$bind = array('id'='5');
	*	$results = $db->query($sql,$bind);
	*	var_dump($results);
	*</pre>
	*
	*Revision Description:
	*<pre>
	*  v1.0.0 - Intitial Creation
	*</pre>
	*
	*@Name query
	*@param String $sql SQL to run
	*@param Array $bind Parameters to bind. 
	*@Author Steve Marsh
	*@return Array returns results of query.
	*/
	public function query($sql,$bind="")
	{
		//Execute the query.
		return $this->run($sql,$bind);
	}
}


?>