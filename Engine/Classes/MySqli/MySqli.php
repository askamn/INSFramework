<?php

class Database {

   /**
    * 
	* @string to store Hostname
	*
	*/
	
   protected $hostname = 'localhost';
   
    /**
    * 
	* @string to store Database Name
	*
	*/
	
   protected $database  = 'source_up2ga514';
   
   /**
    * 
	* @string to store Database Username
	*
	*/
	
   protected $username = 'source_3468';
   
   /**
    * 
	* @string to store Database Password
	*
	*/
	
   protected $password = '9307526024';
   
   /**
    * 
	* @array to store Errors
	*
	*/
	
   protected $errors = array();
   
   /**
    * 
	* @string to store SQL Statement
	*
	*/
	
   protected $statement;
   
   /**
    * 
	* @resource to store Object
	*
	*/
	
   protected $db = "";
   
   /**
    * 
	* Constructor of the class
	*
	*/
	
   public function __construct()
   {
        if ($this->username == '' || $this->password == '' || $this->hostname == '' || $this->database == '')
	     {
		   trigger_error(DATABASE_INFO_MISSING, E_USER_WARNING);
		 }
		
		$this->db = new mysqli("$this->hostname", "$this->username", "$this->password", "$this->database");
   }

   /**
    * 
	* Binds Paramaters & Values
	*
	*/
   
   public function bind($array, $type = null)
	{ 
     // Extract Array & Bind	 
     foreach($array AS $param => $value)
	  {
        $this->statement->bind_param($this->detect_type($value), $param);	  
	  }
    } 
	
   /**
    * 
	* Detects Type Of Value Supplied
	*
	*/	

   public function detect_type($value)
	{
	 // Detect Type
     switch(true) 
		 {
            case is_int($value):
                $type = 'i';
                break;
            case is_double($value):
                $type = 'd';
                break;
            default:
                $type = 's';
         }
	 return $type;  
	}

   /**
    * 
	* Smart Query -> Executing SQL Queries Using MySQLi
	*
	*/
   
   public function smart_query($query, $options = null, $bindoptions = null)
    {  
	 
	  $this->statement = $this->db->prepare($query);
		
	  if($bindoptions != null)
	    {
		  $this->bind($bindoptions);
		}
		
      $this->execute();
	  
	  if($options != null)
	   {
			$result = $this->statement->result_metadata();
			while($field = $result->fetch_field())
			{
				$params[] = &$row[$field->name]; 
			}
			call_user_func_array(array($this->statement, 'bind_result'), $params);
			echo "<pre>$field->name";
			print_r($params);
            echo "</pre>"; 
			while ($this->statement->fetch()) 
			{
				foreach($row as $key => $val)
				{
					$c[$key] = $val;
				}
				$result[] = $c;
			} 
			echo "<pre>";
			print_r($result);
            echo "</pre>"; 			
	   }
	}
	
   /**
    * 
	* Executes a query
	*
	*/
   public function execute()
    {
      return $this->statement->execute();
    }
	
   /**
    * 
	* Returns Number Of Rows
	*
	*/
   public function num_rows()
    {
      return $this->db->affected_rows;
    }
   
   public function __destruct()
    {
	  unset($this->db);
	}
} 

?>