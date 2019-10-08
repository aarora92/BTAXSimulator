<?php

function getAxDBAInstance( $DBAInstanceName )
{	
	$instance = 0;
	while( $instance = ax_get_instance($instance,"") )
	{	
		$InstanceName = ax_get_instance_name($instance);
		if ( $InstanceName == $DBAInstanceName )
			break;
	}
	return $instance;
}

// ----------------------------------------------------------------------
// Function writelogmsg
// ----------------------------------------------------------------------
  
function writelogmsg($message,$logfile="")
{ 
	global $DEBUG;
	
	global $ERROR_LOGFILEHANDLE;
	
	$message = ereg_replace("\r","",$message);
	$message = ereg_replace("\n","",$message);
	
	if (!empty($logfile))
	{
	  if (!isset($ERROR_LOGFILEHANDLE[$logfile]))
	  {  
		if (!( $ERROR_LOGFILEHANDLE[$logfile] = fopen($logfile,"a")))
		{
		  stop("Could not open logfile (file=$logfile)");
		}           
	  }  
	  
	  if (!fwrite($ERROR_LOGFILEHANDLE[$logfile],$message."\r\n"))
	  {
		stop("Could not write to logfile (file=$logfile)");      
	  }    
	}
	else
	{
	  echo "$message\n";
	}
}	    
  
function closelogmsg()
{ 
	global $DEBUG;
	
	global $ERROR_LOGFILEHANDLE;
	
	if (!empty($ERROR_LOGFILEHANDLE))
	{
	  foreach($ERROR_LOGFILEHANDLE as $logfile => $fh_logfile)
	  {
		fclose($fh_logfile);
	  }        
	}
}	

?>