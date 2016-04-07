<?php
dl("ccnuticket.so");
echo "start call ccnu_chk() </br>";
echo ccnu_chk();
echo "return!</br>";


	function ticket_chk(){
		dl("libthroam.so");
		//$rz = new (5);
		$iipp=$_SERVER["REMOTE_ADDR"];
		echo "ip:" . $iipp . "</br>";
		if($_GET["ticket"] === "") echo "a is an empty string\n";
			$ticket=$_GET["ticket"];
			echo "get ticket:" . $ticket . "</br>";
			//thauth_chkticket2string($ticket,"CGYD",$iipp);
			//thauth_check_ticket($ticket,"CGYD",$iipp);;
			echo "ffff</br>";
		//
		echo "chkticket return:" . $ret . "</br>";
	}
	
	//echo phpinfo();
?>