<?php
###############################################################
#
#	This script is taken from phpclasses repository.
#
#	Originally written by Author : Hermawan Haryanto
#
# It has been enhanced by Bulent Tezcan, bulent@greenpepper.ca
#
# Description: This class handles the paging from a query to be print 
#              to the browser. 
#
###############################################################

include_once "securityConfig.inc.php";

/**
* This class handles the paging from a query to be print to the browser.
* @author Bulent Tezcan
*/

class Paging {
  
  
	/**
	* Constructor of the class Paging.
	* @public
	*/
  function Paging($nowstage,$startpage,$allpage,$nowpage,
									$pageperstage,$allstage,$extraArguments="")
	{
    $this->mNowstage = $nowstage;
    $this->mStartpage = $startpage;
    $this->mAllpage = $allpage;
    $this->mNowpage = $nowpage;
    $this->mPageperstage = $pageperstage;
    $this->mAllstage = $allstage;
		$this->mExtraArguments = urldecode( $extraArguments );
  }

  function printPagingNavigation()
	{
		return $this->CreateLinks($this->mNowstage,$this->mStartpage,$this->mAllpage,
											$this->mNowpage,$this->mPageperstage,
											$this->mAllstage,$this->mExtraArguments);
	}

	/**
	* This function creates the navigation and returns it as a string. So you can
	* place it anywhere you want.
	* @public
	* @returns array
	*/
	function CreateLinks($nowstage,$startpage,$allpage,$nowpage,
									$pageperstage,$allstage,$extraArguments="")
	{  
		$links="&nbsp;";

		if(trim($nowpage)>1)  
		{  
			$links.="&nbsp;<a class=\"".$_SESSION["CSS"]."LinkButton\" href='$PHP_SELF?nowstage=1&nowpage=1$extraArguments'>&lt;&lt;&lt;</a>&nbsp;\n";  
		}  
		if(trim($nowstage)>1)  
		{  
			$links.="&nbsp;<a class=\"".$_SESSION["CSS"]."LinkButton\" href='$PHP_SELF?nowstage=".($nowstage-1)."&nowpage=".((($nowstage-1)*$pageperstage)-($pageperstage-1))."$extraArguments'>&lt;&lt;</a>&nbsp;\n";  
		}  
		for($i=$startpage;$i<=$allpage;$i++)  
		{  
			if(trim($nowpage)=="")  
			{  
				$nowpage=$startpage;  
			}  
			$endpage=(($startpage+$pageperstage)-1);  
			if($i>=$startpage&&$i<=$endpage&&$i<=$allpage)  
			{  
				
				# Next page    >
				if($nowpage!=((($nowstage-1)*$pageperstage)+$i)&&$i==$startpage&&$nowpage>$startpage)  
				{  
					$links=$links."&nbsp;<a class=\"".$_SESSION["CSS"]."LinkButton\" href='$PHP_SELF?nowstage=$nowstage&nowpage=".($nowpage-1)."$extraArguments'>&lt;</a>&nbsp;\n";  
				}  
			
				# Current page, no link and Bold
				if(((($nowstage-1)*$pageperstage)+$i)==$nowpage&&((($nowstage-1)*$pageperstage)+$i)<=$allpage)  
				{  
					$links=$links."&nbsp;<font class=\"".$_SESSION["CSS"]."PagingCurrentPage\">".((($nowstage-1)*$pageperstage)+$i)."</font>&nbsp;";  
				}  
				if(((($nowstage-1)*$pageperstage)+$i)!=$nowpage&&((($nowstage-1)*$pageperstage)+$i)<=$allpage)  
				{  
					$links=$links."&nbsp;<a class=\"".$_SESSION["CSS"]."PagingLink\" href='$PHP_SELF?nowstage=$nowstage&nowpage=".((($nowstage-1)*$pageperstage)+$i)."$extraArguments'>".((($nowstage-1)*$pageperstage)+$i)."</a>&nbsp;\n";  
				}  
				if(($i==$endpage||$i==$allpage)&&$nowpage!=((($nowstage-1)*$pageperstage)+$i)&&$allpage>$nowpage)  
				{  
					$links=$links."&nbsp; <a class=\"".$_SESSION["CSS"]."LinkButton\" href='$PHP_SELF?nowstage=$nowstage&nowpage=".($nowpage+1)."$extraArguments'>&gt;</a>&nbsp;\n";  
				}  
			}  
		}  
		if($nowstage<$allstage)  
		{  
			$links=$links. "&nbsp;<a class=\"".$_SESSION["CSS"]."LinkButton\" href='$PHP_SELF?nowstage=".($nowstage+1)."&nowpage=".(($nowstage*$pageperstage)+1)."$extraArguments'>&gt;&gt;</a>&nbsp;\n";  
		}  
		if($nowpage<$allpage)  
		{  
			$links.="&nbsp;<a class=\"".$_SESSION["CSS"]."LinkButton\" href='$PHP_SELF?nowstage=".$allstage."&nowpage=".$allpage."$extraArguments'>&gt;&gt;&gt;</a>&nbsp;\n";  
		}  
	return $links;  
	}  


}; // End Class
?>