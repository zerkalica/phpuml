<?php

class adodbExtract {

	function IfNull( $field, $ifNull ) 
	{
	}

	/**
	 * That one was messing the parser...
	 */
	function pg_insert_id($tablename,$fieldname)
	{
		$result=pg_exec($this->_connectionID, "SELECT last_value FROM ${tablename}_seq");
		if ($result) {
			$arr = @pg_fetch_row($result,0);
			pg_freeresult($result);
			if (isset($arr[0])) return $arr[0];
		}
		return false;
	}

  /**
   * and that one was not there
   */
	function _insertid($table,$column)
	{
	}

}

?>