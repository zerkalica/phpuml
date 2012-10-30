<?php

/**
 * This is a test case for the onlyApi docblock. Should be tested with onlyApi option set to true.
 * @package MilkyWay
 * @api
 *
 */
class Mars 
{
  /**
   * 
   * @var string
   * @api
   */
 	public $probe = 'Phoenix';
    
  /**
   *
   * @var string
   */
	public $martians = 'Green creature';

	/**
	 * Invisible function (if onlyApi was set to true)
	 * 
	 * @param mixed $object
	 * @return int
	 */
	public function attack($object) 
	{
	    return 1;
	}

	/**
	 * Visible function
	 * 
	 * @param mixed $object
	 * @return int
	 * @api
	 */
	public function hasWater($object) 
	{
	    return 0;
	}
}

/**
 * That class should not appear if parsing option "onlyApi" was set to true (since the class has no "api" docblock)
 * @author Admin
 * @package MilkyWay
 *
 */
Class Mars_Satellite
{
    function turnAroundMars()
    {
    }
}

?>
