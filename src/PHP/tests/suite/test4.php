<?php


/**
 * This class belongs to Cassiopeia :
 * @package Cassiopeia
 * 
 * "Universe" in turnsAround should be found since Universe is in the global ns
 * 
 */
class Planet 
{
	public $name = '';
	
	/**
	 *
	 * @param Universe $object
	 * @return Planet
	 */
	public function turnsAround(Universe $object) 
	{
	    $o = new Planet;
	    $o->name = 'Myself';
	    return $o;
	}
}


?>
