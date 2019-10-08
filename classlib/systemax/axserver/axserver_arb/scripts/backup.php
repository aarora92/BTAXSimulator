<?php 
  // --------------------------------------------------------------------
  // backup.php
  // --------------------------------------------------------------------
  //
  // Version 0.1  2005.11.17
  // Version 0.2 2005.11.27  rruthofer
  // 
  //---------------------------------------------------------------------
  
	$VERSION_INFO	= "V0.2";
	  
	header("Expires: -1");
	header("Pragma: no-cache");
	  
	set_time_limit(0);  
	  
	
	// --------------------------------------------------------------------
	// Init 
	// --------------------------------------------------------------------
	$DEBUG=0;
	$ERROR_LOGPATH			= "o:\\oracle\\dba\\aximst\\log";
	$ERROR_LOGFILEHANDLE; 
  
	// Backup (Yes=1/No=0)
	$BACKUP_FLAG=1;  
	// Check Backuplog (Yes=1/No=0)
	$BACKUPLOG_FLAG=1;
  
	// Backup logfile name
	$BACKUP_LOGFILE			= "o:\\oracle\\dba\\aximst\\log\\backup_".date("Ymd").".log";  
	// Backup array's - they have to be disjunct
	//$BACKUP_INC0 			= array ("Mon" => 0,"Tue" => 0,"Wed" => 0,"Thu" => 0,"Fri" => 0,"Sat" => 0,"Sun" => 1);
	//$BACKUP_INC1 			= array ("Mon" => 1,"Tue" => 1,"Wed" => 1,"Thu" => 1,"Fri" => 1,"Sat" => 1,"Sun" => 0);
	// Backup script's 
	$BACKUP_INC0_SCRIPTFILE	= "o:\\oracle\\dba\\aximst\\script\\backup_inc0.rcv";    
	$BACKUP_INC1_SCRIPTFILE	= "o:\\oracle\\dba\\aximst\\script\\backup_inc1.rcv";     
    $ERROR_BACKUP_LOGFILE 	= $ERROR_LOGPATH."\\err_backuplog_".date('Ymd').".log";
	
	$CLASS_PATH				= "classlib/systemax/axvarious/database/";
	 
	// --------------------------------------------------------------------
	// DB connect information
	// --------------------------------------------------------------------
	$SID="AXIMST";  
	$ODBC_DRIVER_NAME="AXIMST";
	$USER="AXUSER";
	$PWD="ax";
  
	dl ("axphpextension.dll");
	require 'general/classes.inc';
	require 'general/functions.inc';
	
	
	$dbg = new aXDebug("Database","BACKUP");
	$dbg->writeMessage("-------------------------------------------------");
	$dbg->writeMessage("$VERSION_INFO starting...");
	  
	// --------------------------------------------------------------------
	// get settings form ax class
	// --------------------------------------------------------------------
	$DBAInstanceName = $_SERVER['argv'][1];
	if ( empty($DBAInstanceName) )
	{	$dbg->writeMessage("no instance name for dba instance as arg[1] --> exit()");
		exit(0);
	}
	$dbg->writeMessage("DBA Instance -->   $DBAInstanceName");
	
	if ( !$instance=getAxDBAInstance ( $DBAInstanceName ) )
	{
		$dbg->writeMessage("DBA Instance $DBAInstanceName  not found !!!");
		exit(0);
	}
		
	ax_set(ax_query("backupError",$instance),0);
	ax_set(ax_query("backup_active",$instance),1);
	
	// --  Settings form aX Instance --
	$ERROR_LOGPATH 			= ax_get(ax_query("log_path",$instance));
	// Backup logfile name
	$BACKUP_LOGFILE			= $ERROR_LOGPATH."/backup_".date("Ymd").".log";
	$ERROR_BACKUP_LOGFILE 	= $ERROR_LOGPATH."/err_backuplog_".date('Ymd').".log";
	$working_part 			=  ax_get(ax_query("working_part",$instance));
	$project_name			=  ax_get(ax_query("project_name",$instance));
    $BACKUP_INC0_SCRIPTFILE = $working_part."/".$project_name."/".$CLASS_PATH."scripts/backup_inc0.rcv";    
	$BACKUP_INC1_SCRIPTFILE = $working_part."/".$project_name."/".$CLASS_PATH."scripts/backup_inc1.rcv";   
	
	$SID					= ax_get(ax_query("DSN",$instance));  
	$ODBC_DRIVER_NAME		= ax_get(ax_query("DSN",$instance));
	$USER					= ax_get(ax_query("user",$instance));
	$PWD					= ax_get(ax_query("password",$instance));
	
	$DEBUG					= ax_get(ax_query("debug",$instance));
	$BACKUPLOG_FLAG			= ax_get(ax_query("backup_settings",$instance));
	$settings				= ax_get(ax_query("backup_settings",$instance));
	$BACKUPLOG_FLAG			= ( $settings && 0x02 ? 1:0 );
	$backup_inc0_settings	= ax_get(ax_query("backup_inc0_settings",$instance));
	$backup_inc1_settings	= ax_get(ax_query("backup_inc1_settings",$instance));
	// Backup array's - they have to be disjunct
	$BACKUP_INC0 			= array (	"Mon" => $backup_inc0_settings & 0x01,
										"Tue" => $backup_inc0_settings & 0x02,
										"Wed" => $backup_inc0_settings & 0x04,
										"Thu" => $backup_inc0_settings & 0x08,
										"Fri" => $backup_inc0_settings & 0x10,
										"Sat" => $backup_inc0_settings & 0x20,
										"Sun" => $backup_inc0_settings & 0x40);
	$BACKUP_INC1 			= array (	"Mon" => $backup_inc1_settings & 0x01,
										"Tue" => $backup_inc1_settings & 0x02,
										"Wed" => $backup_inc1_settings & 0x04,
										"Thu" => $backup_inc1_settings & 0x08,
										"Fri" => $backup_inc1_settings & 0x10,
										"Sat" => $backup_inc1_settings & 0x20,
										"Sun" => $backup_inc2_settings & 0x40);
  
             
  // ----------------------------------------------------------------------
  // Main
  // ----------------------------------------------------------------------
     
  if (!empty($DEBUG)) 
	writelogmsg("main::start"); 

  if (!empty($BACKUP_FLAG))
  {
    $res = backup();
	if ( $res < 0 )
		ax_set(ax_query("backupError",$instance),1);
  }
     
  if (!empty($BACKUPLOG_FLAG))
  {
    $res = chk_backuplog();
	if ( $res < 0 )
		ax_set(ax_query("backupError",$instance),1);
  }      
      
  if (!empty($DEBUG)) writelogmsg("main::end");           
  
  closelogmsg();
  
  $dbg->writeMessage("finished");
  ax_set(ax_query("backup_active",$instance),0);
  exit(0); 	


  
  
  
  
/*-------------------------------------------------------------------------------------------------------------------------------------------------*/  
/*---------------------------------	FUNCTIONS						-------------------------------------------*/  
/*-------------------------------------------------------------------------------------------------------------------------------------------------*/  


	// ----------------------------------------------------------------------
	// Function backup
	// ----------------------------------------------------------------------
	
  function backup()
  {  		
    global $DEBUG;
	global $dbg;
    global $BACKUP_LOGFILE; 
    global $BACKUP_INC0;   
    global $BACKUP_INC0_SCRIPTFILE; 
    global $BACKUP_INC1;
    global $BACKUP_INC1_SCRIPTFILE; 
    
    $BACKUP_SCRIPTFILE = "";

	$dbg->writeMessage("backup():start");
	
	  if (!empty($DEBUG)) writelogmsg("backup():start");           

	  $date = date("D");
    
    if (count($BACKUP_INC0) != 7 || count($BACKUP_INC1) != 7) 
    {
      writelogmsg("BACKUP-108 Wrong backup configuration (count array < 7)",$BACKUP_LOGFILE);
      return(-1);      
    }

    if ($date != "Mon" && $date != "Tue" && $date != "Wed" && 
        $date != "Thu" && $date != "Fri" && $date != "Sat" && 
        $date != "Sun")
    {
      writelogmsg("BACKUP-109 Date strings not correct",$BACKUP_LOGFILE);
      return(-1);            
    }
    
    if (!empty($BACKUP_INC0[$date]) && !empty($BACKUP_INC1[$date])) 
    {
      writelogmsg("BACKUP-110 Wrong backup configuration (array's)",$BACKUP_LOGFILE);
      return(-1);      
    }

    if (empty($BACKUP_INC0["Mon"]) && empty($BACKUP_INC0["Tue"]) && empty($BACKUP_INC0["Wed"]) &&
        empty($BACKUP_INC0["Thu"]) && empty($BACKUP_INC0["Fri"]) && empty($BACKUP_INC0["Sat"]) &&
        empty($BACKUP_INC0["Sun"])) 
    {
      writelogmsg("BACKUP-111 Wrong backup configuration (no weekly full database backup)",$BACKUP_LOGFILE);
      return(-1);      
    }
        
    if (!empty($BACKUP_INC0[$date]))
    {
      $BACKUP_SCRIPTFILE = $BACKUP_INC0_SCRIPTFILE; 
      writelogmsg("backup():date=$date => full database backup (rcv=$BACKUP_SCRIPTFILE)",$BACKUP_LOGFILE);
    }
    else
    {
      if (!empty($BACKUP_INC1[$date]))
      {
        $BACKUP_SCRIPTFILE = $BACKUP_INC1_SCRIPTFILE;
        writelogmsg("backup():date=$date => full database backup (rcv=$BACKUP_SCRIPTFILE)",$BACKUP_LOGFILE);
      }
    }
      
    if (!empty($BACKUP_SCRIPTFILE))
    {
      $descriptorspec = array(0 => array("pipe","r"), 
                              1 => array("pipe","w"), 
                              2 => array("file",$BACKUP_LOGFILE,"a"));                             
                              
      $process = proc_open("rman",$descriptorspec,$pipes);
      
      if (is_resource($process)) 
      {
        if (!empty($DEBUG)) 
		{	writelogmsg("backup():scriptfile=$BACKUP_SCRIPTFILE");  
		}
        
        if (!($fread = fopen($BACKUP_SCRIPTFILE,"r+")))
        {
          writelogmsg("BACKUP-101 Could not open rman script file (file=$BACKUP_SCRIPTFILE)",$BACKUP_LOGFILE);
          return(-1);
        }
         	    
        $data = fread($fread,filesize($BACKUP_SCRIPTFILE));
  	    $data = preg_replace("/\r/","",$data);
  	    
        fclose($fread);    
      
        if (!empty($data))
        {  	    
      	  $data_array = explode("\n",$data);	
  
      	  foreach ($data_array as $data_row)
      	  { 
  		    $data_row .= "\n";
  			
  			if (!empty($DEBUG)) 
			{	writelogmsg("backup():scriptfile (row=".trim($data_row).")"); 
                  	    
				if (!empty($data_row))
				{
				  if (!fwrite($pipes[0],$data_row))
				  {
					writelogmsg("BACKUP-102 Process fwrite failed (row=".trim($data_row).")",$BACKUP_LOGFILE);
				  }
				}
    	    }        	
		  } 
		}
        else
        {
          writelogmsg("BACKUP-103 Empty rman script file (file=$scriptfile)",$BACKUP_LOGFILE);
          return(-1);
        }      
  
        fclose($pipes[0]);
  
        while (!feof($pipes[1])) 
        {
          $message=fgets($pipes[1], 1024);
          writelogmsg($message,$BACKUP_LOGFILE);
        }
        
        fclose($pipes[1]);    
        $return_value = proc_close($process);
  
        if ($return_value)
        {
          writelogmsg("BACKUP-104 RMAN return value $return_value",$BACKUP_LOGFILE);
        }
      } 
    }
    else
    {
  	  if (!empty($DEBUG)) writelogmsg("backup():no entry in array's - no backup");                   
    }
    
	  if (!empty($DEBUG)) writelogmsg("backup():end");               
  }  
    
	// ----------------------------------------------------------------------
	// Function chk_backuplog
	// ----------------------------------------------------------------------
    
  function chk_backuplog()
  {  		
    global $DEBUG;
    
    global $BACKUP_LOGFILE;    
    global $ERROR_BACKUP_LOGFILE;  
       
	if (!empty($DEBUG)) 
	{	writelogmsg("");
		writelogmsg("chk_backuplog():start  ##############################################################"); 
		writelogmsg("chk_backuplog():start  -------------------- logfile follows -------------------"); 
		writelogmsg("");
	}
                  
    if (!($fread = fopen($BACKUP_LOGFILE,"r+")))
    {
      writelogmsg("BACKUP-105 Could not open backup log file (file=$BACKUP_LOGFILE)",$ERROR_BACKUP_LOGFILE);
      return(-1);
    }
       	    
    $data = fread($fread,filesize($BACKUP_LOGFILE));
    fclose($fread);    
    
    if (!empty($data))
    {  	        
  	  $data_array = explode("\n",$data);	
      $err_rows = 0;
                  
  	  foreach ($data_array as $data_row)
  	  { 
  	    if (!empty($data_row))
        {         
          if (preg_match("/RMAN-\d|ORA-\d|PLS-\d|OSD-\d|-Error:|BACKUP-\d/",$data_row))
          {          
            $err_rows++;
            writelogmsg(trim($data_row),$ERROR_BACKUP_LOGFILE);
          }        
        }
	    }        	

      if ($err_rows)
      {
        return(-1);
      }     	            
    } 
    else
    {
      writelogmsg("BACKUP-106 Empty backup log file (file=$BACKUP_LOGFILE)",$ERROR_BACKUP_LOGFILE);
      return(-1);
    }

	if (!empty($DEBUG)) writelogmsg("chk_backuplog():end");           
		return(0);
  }    
  
	// ----------------------------------------------------------------------
	// Function stop
	// ----------------------------------------------------------------------
  
  function stop($message)
  {
    closelogmsg();    
    die($message."\nStop\n");
  }   
?>
