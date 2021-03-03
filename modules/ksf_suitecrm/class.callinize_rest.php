<?php
require_once( 'sugar_rest.php' );


class callinize_rest
{
	private $url;
	private $user;
	private $pass;
	private $sugar;
	function __construct( $url, $user, $pass )
	{
		$this->url = $url;
		$this->user = $user;
		$this->pass = $pass;
		$this->sugar = new Sugar_REST( $this->url, $this->user, $this->pass );
	}
/**
 *
 * @param $origPhoneNumber
 * @param bool $stopOnFind -Controls whether or not to keep searching down the list of modules to find a match...
 *                          For example, if a match in contacts is found... it will not try leads.
 * @param bool $returnMultipleMatches - when true it returns all matches (callinize push uses true, unattended will use false)
 *                                      think of this as attended vs. unattended mode... true it returns all the matches to the user so
 *                                      they can see all the results where false a computer has to make a decision and we dont ever want to make assumptions.
 * @return An|array|null
 */
	function findSugarBeanByPhoneNumber($origPhoneNumber,$stopOnFind=false, $returnMultipleMatches=false) 
	{
		$params = array();
		$params['phone_number'] = $origPhoneNumber;
		$params['stop_on_find'] = $stopOnFind;
		$beans = $sugar->custom_method("find_beans_with_phone_number", $params);
		$retVal = null;
		if (count($beans) == 1) 
		{
			$retVal = $beans; // Do not return beans[0]!
		} else if (count($beans) > 1) 
		{
			//below code is primarily used for mobile version
			if ($returnMultipleMatches === true) 
			{
				$retVal = $beans;
				//below code is primarily used for desktop version
			} else {
				// Check if all beans are from the same parent
				$firstParentId = $beans[0]['parent_id'];
				$moreThanOneParent = false;
				for ($i = 1; $i < count($beans); $i++) 
				{
					if ($beans[$i]['parent_id'] !== $firstParentId) 
					{
						$moreThanOneParent = true;
						break;
					}
				}
				if (!$moreThanOneParent && !empty($firstParentId)) 
				{
					$retVal = array();
					$retVal['bean_id'] = $beans[0]['parent_id'];
					$retVal['bean_name'] = $beans[0]['parent_name'];
					$retVal['bean_link'] = $beans[0]['parent_link'];
				}
			}
		}
		return $retVal;
	}
	function findCallByAsteriskId($asteriskId)
	{
		$result = $this->sugar->get( "Calls", "*", array( 'where' => 'id=' . $asteriskId ) );
		return $result[0];
	}
}//class

///TEST
/*
$url = $sugar_config["site_url"] . '/custom/service/callinize/rest.php';
$sugar = new callinize_rest($url, $sugar_config['asterisk_soapuser'], $sugar_config['asterisk_soappass']);
 */

?>
