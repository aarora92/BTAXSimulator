<?php 
  // --------------------------------------------------------------------
  // dba.php
  // --------------------------------------------------------------------
  //
  // Version 0.1  2005.11.17
  // 
  //---------------------------------------------------------------------
  
  header("Expires: -1");
  header("Pragma: no-cache");
  
  set_time_limit(0);

  $VERSION_INFO	= "V0.2";
  
  // --------------------------------------------------------------------
  // Init 
  // --------------------------------------------------------------------
  
  $DEBUG=1;
  $ERROR_LOGPATH="o:\\oracle\\dba\\aximst\\log";
  $ERROR_LOGFILEHANDLE; 
  
	
  // --------------------------------------------------------------------
  // Alert log
  // --------------------------------------------------------------------
   
  // Check Instanz alert log (Yes=1/No=0)
  $ALERTLOG_FLAG=1; 
  // Alert log reorganisation (Yes=1/No=0)
  $ALERTLOG_REORG_FLAG=1; 
  
  $ALERT_LOGFILE="o:\\oracle\\admin\\aximst\\bdump\\alert_aximst.log";
  $NEW_ALERT_LOGFILE="o:\\oracle\\admin\\aximst\\bdump\\alert_aximst_".date("Ymd").".log";
  
  $ERROR_ALERT_LOGFILE = $ERROR_LOGPATH."\\err_alertlog_".date('Ymd').".log";  
   
  // --------------------------------------------------------------------
  // Disk space
  // --------------------------------------------------------------------
  
  // Check disk memory (Yes=1/No=0)
  $DISKSPACE_FLAG=1;
  // Directory to check
  $DISKSPACE_DIR="o:\\";
  // Min free disk space in MB
  $DISKSPACE_MIN=5*1024; // 5GB
  
  $ERROR_DISKSPACE = $ERROR_LOGPATH."\\err_diskspace_".date('Ymd').".log";  
  
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
	
	
	$dbg = new aXDebug("Database","DBA");
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
		
	ax_set(ax_query("dba_active",$instance),1);
	// --  Settings form aX Instance --
	$ERROR_LOGPATH 			= ax_get(ax_query("log_path",$instance));
	$ERROR_ALERT_LOGFILE = $ERROR_LOGPATH."\\err_alertlog_".date('Ymd').".log"; 
	// Backup logfile name
	$BACKUP_LOGFILE			= $ERROR_LOGPATH."/backup_".date("Ymd").".log";
	$ERROR_BACKUP_LOGFILE 	= $ERROR_LOGPATH."/err_backuplog_".date('Ymd').".log";
	// settings 
	$settings 			= ax_get(ax_query("dba_settings",$instance));
	$ALERTLOG_FLAG		= ( $settings & 0x02 ? 1:0);
	$ALERTLOG_REORG_FLAG	= ( $settings & 0x04 ? 1:0);
	$DISKSPACE_FLAG		= ( $settings & 0x08 ? 1:0);
	$DISKSPACE_DIR		= ax_get(ax_query("database_dir",$instance));
	$DISKSPACE_MIN		= ax_get(ax_query("alarm_l_workpart",$instance));
	$ALERT_LOGPATH		= ax_get(ax_query("alert_logpath",$instance));
	$ALERT_LOGFILE		= $ALERT_LOGPATH."alert_aximst.log";
	$NEW_ALERT_LOGFILE	= $ALERT_LOGPATH."alert_aximst_".date("Ymd").".log";
	$DEBUG				= ax_get(ax_query("debug",$instance));
	
	$dbg->writeMessage("------  settings -------");
	$dbg->writeMessage("check alert log:\t\t$ALERTLOG_FLAG");
	$dbg->writeMessage("reorganize alert log:\t$ALERTLOG_REORG_FLAG");
	$dbg->writeMessage("check disk space:\t\t$DISKSPACE_FLAG");
	$dbg->writeMessage("------  end settings ---");
	
             
  // ----------------------------------------------------------------------
  // Main
  // ----------------------------------------------------------------------
     
  if (!empty($DEBUG)) writelogmsg("main::start");           
     
  if (!empty($ALERTLOG_FLAG))
  {
    chk_alertlog();
  }      

  if (!empty($DISKSPACE_FLAG))
  {
    chk_diskspace();
  }      
    
  if (!empty($DEBUG)) writelogmsg("main::end");           
  
  closelogmsg();
  $dbg->writeMessage("finished ... ");
  ax_set(ax_query("dba_active",$instance),0);
	exit(0); 	
	
	// ----------------------------------------------------------------------
	// Function chk_alertlog
	// ----------------------------------------------------------------------
	
  function chk_alertlog()
  {  		
    global $DEBUG;
    
    global $ALERT_LOGFILE;    
    global $NEW_ALERT_LOGFILE;
    global $ERROR_ALERT_LOGFILE;  
    global $ALERTLOG_REORG_FLAG;
 
	  if (!empty($DEBUG)) writelogmsg("chk_alertlog():start (file=$ALERT_LOGFILE)");           
                  
    if (!($fread = fopen($ALERT_LOGFILE,"r+")))
    {
      writelogmsg("DBA-007 Could not open alert log file (file=$ALERT_LOGFILE)",$ERROR_ALERT_LOGFILE);
      return(-1);
    }
       	    
    $data = fread($fread,filesize($ALERT_LOGFILE));
    fclose($fread);    
    
    if (!empty($data))
    {  	        
  	  $data_array = explode("\n",$data);	
      $err_rows = 0;
      $rows = 0;
      $dump_file_cnt = 0;
      $dump_file_row = 0;
                  
  	  foreach ($data_array as $data_row)
  	  { 
  	    if (!empty($data_row))
        {
          if (preg_match("/ORA-\d|PLS-\d|OSD-\d|-Error:/",$data_row))
          {    
            $err_rows++;
            writelogmsg(trim($data_row),$ERROR_ALERT_LOGFILE);
          }        
                    
          if (!empty($ALERTLOG_REORG_FLAG))
          {    
            $rows++;
          
            if (preg_match("/Dump file /",$data_row))
            {
              $dump_file_cnt++;
              $dump_file_row = $rows;
           }
          }
        }
	    }        	

      if (!empty($ALERTLOG_REORG_FLAG) && $dump_file_cnt > 1)
      {           
        if (!($fwrite = fopen($NEW_ALERT_LOGFILE,"w")))
        {
          writelogmsg("DBA-010 Could not create new alert log file (file=$NEW_ALERT_LOGFILE)",$ERROR_ALERT_LOGFILE);
          return(-1);
        }
                 
        if (!fwrite($fwrite,$data))
        {
          writelogmsg("DBA-011 Could not write to new alert log file (file=$NEW_ALERT_LOGFILE)",$ERROR_ALERT_LOGFILE);
          return(-1);
        }
                
        fclose($fwrite); 
        
        if (!($fwrite = fopen($ALERT_LOGFILE,"w")))
        {
          writelogmsg("DBA-012 Could not open alert log file (2) (file=$ALERT_LOGFILE)",$ERROR_ALERT_LOGFILE);
          return(-1);
        }
           	    
        for ($i = $dump_file_row-1;$i <= $rows-1;$i++)
        {
          if (!fwrite($fwrite,$data_array[$i]))
          {
            writelogmsg("DBA-013 Could not write to alert log file (file=$ALERT_LOGFILE)",$ERROR_ALERT_LOGFILE);
            return(-1);          
          }
        }
        
        fclose($fwrite);
      }
      
      if ($err_rows)
      {
        return(-1);
      }     	            
    } 
    else
    {
      writelogmsg("DBA-008 Empty alert log file (file=$logfile)",$ERROR_ALERT_LOGFILE);
      return(-1);
    }
    
	  if (!empty($DEBUG)) writelogmsg("chk_alertlog():end");           
    return(0);
  }

	// ----------------------------------------------------------------------
	// Function chk_diskspace
	// ----------------------------------------------------------------------
	
  function chk_diskspace()
  {  		
    global $DEBUG;
    
    global $DISKSPACE_FLAG;
    global $DISKSPACE_DIR;
    global $DISKSPACE_MIN;
	global $instance;
    
    global $ERROR_DISKSPACE;

	  if (!empty($DEBUG)) writelogmsg("chk_diskspace():start (directory=$DISKSPACE_DIR)");           

    $df = (disk_free_space($DISKSPACE_DIR)/(1024*1024));
	$dtotal = (disk_total_space($DISKSPACE_DIR)/(1024*1024));
	ax_set(ax_query("working_free",$instance),$df);
	ax_set(ax_query("working_total",$instance),$dtotal);

	  if (!empty($DEBUG)) writelogmsg("chk_diskspace():free=$df MB, min=$DISKSPACE_MIN MB");           
    
    if ($df < $DISKSPACE_MIN)
    {
       writelogmsg("DBA-012 Too less free space (directory=$DISKSPACE_DIR, free=$df MB, min=$DISKSPACE_MIN MB)",$ERROR_DISKSPACE);
    }

	  if (!empty($DEBUG)) writelogmsg("chk_diskspace():end");           
    return(0);    
  }    
  
	// ----------------------------------------------------------------------
	// Function stop
	// ----------------------------------------------------------------------
  
  function stop($message)
  {
    closelogmsg();    
	$dbg->writeMessage("finished ... ");
	ax_set(ax_query("dba_active",$instance),0);
    die($message."\nStop\n");
  }   
?>
