<?php


/**
 * This is a test case for the internal docblock.
 * @package MilkyWay
 * 
 */
class Moon 
{
    /**
     * 
     * @var string
     */
 	public $visibleSide = '';
    
  /**
   * @internal
   * @var string
   */
	public $darkSide = '';

	/**
	 * Visible function
	 * 
	 * @param mixed $object
	 * @return int
	 */
	public function lighten($object) 
	{
	    return 1;
	}

	/**
	 * Invisible function
	 * 
	 * @param mixed $object
	 * @return int
	 * @internal
	 */
	public function shadow($object) 
	{
	    return 0;
	}
}

/**
 * That class should not appear at all
 * @author Admin
 * @internal
 * @package MilkyWay
 *
 */
Class Apophis
{
    /**
     * Bouh!!
     */
    function threaten()
    {
    }
}

?>
