<?php 
  header("Expires: -1");
  header("Pragma: no-cache");
  
	set_time_limit(0);
  
  $DEBUG=0;
  $DEBUG_LOGACTION=0;
  
  // DB
  
  $ODBC_DRIVER_NAME="AXIMST";
  $USER="AXUSER";
  $PWD="ax";
  $SID="AXNET";
  
  // import
 
  $IMP_PATH="c:\work\axexp";
  $IMP_FILENAME="exp_";  
  $IMP_LOGFILENAME="imp_";
  $IMP_MONTH="200507";
  $IMP_TABLES="(AXALARMMSGS_A12LAN,AXALARMMSGS_A12MH, AXALARMMSGS_A12MIL,AXALARMMSGS_A12ROP,AXALARMMSGS_A12SW, ".
              " AXALARMMSGS_A12TWI,AXALARMMSGS_A12WIL,AXALARMMSGS_A13BGI,AXALARMMSGS_A13SBG,AXALARMMSGS_A13SBH)";  

  // Flags
  
  $IMPORT_FLAG=1; 
  $STATISTICS_FLAG=1; 
  
  if ($db_connection = odbc_connect($ODBC_DRIVER_NAME,$USER,$PWD))
  {  
    logmsg("*** start axalarmmsgs_imp",0,"SYS",2,0);
    
    // --------------------------------------------------------------------
    // import
    // --------------------------------------------------------------------
    if (!empty($IMPORT_FLAG))
    {     
      if (import() != 0)
      {     
        logmsg("import()::failed",99,"SYS",2,1);
      }
    }
    else
    {
      logmsg("import()::disabled",0,"SYS",2,0);      
    }
   
    // --------------------------------------------------------------------
    // Gather Statistics
    // --------------------------------------------------------------------
    if (!empty($STATISTICS_FLAG))
    {
      if (statistics() != 0)
      {      
        logmsg("statistics()::failed",99,"SYS",2,1);
      }
    }
    else
    {
      logmsg("statistics()::disabled",0,"SYS",2,0);      
    }
    
    logmsg("*** end axalarmmsgs_imp",0,"SYS",2,0);
    
    odbc_close($db_connection);     
  }      
  else
  {
    die("Could not connect to database (SID=$SID) - Stop!");
  }

	exit(0); 	
	
	// ----------------------------------------------------------------------
	// Function import
	// ----------------------------------------------------------------------
	
  function import()
  {  		
    global $DEBUG;
    global $DEBUG_LOGACTION;
    
    global $USER;
    global $PWD;
    global $SID;
    
    global $IMP_PATH;
    global $IMP_FILENAME;  
    global $IMP_LOGFILENAME;  
    global $IMP_MONTH;  
    global $IMP_TABLES;

    global $db_connection;
    
    logmsg("import()::start",0,"SYS",2,0);

/*
    $sql_query =  " select count(*) from axuser.axalarmmsgs_a12lan ".
                  " where time >= to_date($IMP_MONTH,'YYYYMM') ".
                  "   and time <  add_months(to_date($IMP_MONTH,'YYYYMM'),1)";
*/
    $sql_query = " select cnt_a12lan+cnt_a12mh+cnt_a12mil+cnt_a12rop+cnt_a12sw+ ".
                 "        cnt_a12twi+cnt_a12wil+cnt_a13bgi+cnt_a13sbg+cnt_a13sbh ". 
                 "   from (select count(*) cnt_a12lan from axuser.axalarmmsgs_a12lan ".
                 "          where time >= to_date($IMP_MONTH,'YYYYMM') ".
                 "            and time <  add_months(to_date($IMP_MONTH,'YYYYMM'),1)),".
	               "        (select count(*) cnt_a12mh from axuser.axalarmmsgs_a12mh ".
                 "          where time >= to_date($IMP_MONTH,'YYYYMM') ".
                 "            and time <  add_months(to_date($IMP_MONTH,'YYYYMM'),1)),".
	               "        (select count(*) cnt_a12mil from AXUSER.axalarmmsgs_a12mil".
                 "          where time >= to_date($IMP_MONTH,'YYYYMM') ".
                 "            and time <  add_months(to_date($IMP_MONTH,'YYYYMM'),1)),".
	               "        (select count(*) cnt_a12rop from AXUSER.axalarmmsgs_a12rop ".
                 "          where time >= to_date($IMP_MONTH,'YYYYMM') ".
                 "            and time <  add_months(to_date($IMP_MONTH,'YYYYMM'),1)),".
	               "        (select count(*) cnt_a12sw from AXUSER.axalarmmsgs_a12sw ".
                 "          where time >= to_date($IMP_MONTH,'YYYYMM') ".
                 "            and time <  add_months(to_date($IMP_MONTH,'YYYYMM'),1)),".
	               "        (select count(*) cnt_a12twi from AXUSER.axalarmmsgs_a12twi ".
                 "          where time >= to_date($IMP_MONTH,'YYYYMM') ".
                 "            and time <  add_months(to_date($IMP_MONTH,'YYYYMM'),1)),".
	               "        (select count(*) cnt_a12wil from AXUSER.axalarmmsgs_a12wil ".
                 "          where time >= to_date($IMP_MONTH,'YYYYMM') ".
                 "            and time <  add_months(to_date($IMP_MONTH,'YYYYMM'),1)),".
	               "        (select count(*) cnt_a13bgi from AXUSER.axalarmmsgs_a13bgi ".
                 "          where time >= to_date($IMP_MONTH,'YYYYMM') ".
                 "            and time <  add_months(to_date($IMP_MONTH,'YYYYMM'),1)),".
	               "        (select count(*) cnt_a13sbg from AXUSER.axalarmmsgs_a13sbg ".
                 "          where time >= to_date($IMP_MONTH,'YYYYMM') ".
                 "            and time <  add_months(to_date($IMP_MONTH,'YYYYMM'),1)),".
	               "        (select count(*) cnt_a13sbh from AXUSER.axalarmmsgs_a13sbh ".
                 "          where time >= to_date($IMP_MONTH,'YYYYMM') ".
                 "            and time <  add_months(to_date($IMP_MONTH,'YYYYMM'),1))";
	            
    if (!empty($DEBUG)) logmsg("import()::CMD $sql_query",0,"SYS",$DEBUG_LOGACTION,0);
	                   	               
    if ($odbc_exec_id = odbc_exec($db_connection,$sql_query))
    {    
      if (odbc_fetch_row($odbc_exec_id))
      {
        $cnt = odbc_result($odbc_exec_id,1);
        
        if (!empty($cnt))
        {
          $errmsg = odbc_errormsg($db_connection);
          logmsg("import()::ERROR Records found",99,"SYS",2,0);
          return(-1);               
        }
      }    
    }
    else
    {
      $errmsg = odbc_errormsg($db_connection);
      logmsg("import()::ERROR Execute failed MSG=$errmsg QUERY=$sql_query",99,"SYS",2,0);
      return(-1);      
    }

    $filename = "$IMP_PATH\\$IMP_FILENAME$IMP_MONTH.dmp";
    $logfile = "$IMP_PATH\\$IMP_LOGFILENAME$IMP_MONTH.log";
    
    $cmd = "imp userid=$USER/$PWD@$SID ".
           " full=N rows=Y ignore=Y statistics=NONE ".
           " file=$filename log=$logfile ".
           " fromuser=AXUSER touser=AXUSER ".
           " tables=$IMP_TABLES ".
           " feedback=1000 ";

    if (!empty($DEBUG)) logmsg("import()::CMD $cmd",0,"SYS",$DEBUG_LOGACTION,0);
          
    $rc_exec = exec("start /SEPARATE ".$cmd,$data,$rc);                              

    if (!empty($DEBUG)) logmsg("import()::LOGFILE check start",0,"SYS",$DEBUG_LOGACTION,0);
            
    if (!($fread = fopen($logfile,"r+")))
    {
      logmsg("import()::ERROR Could not open log file (file=$logfile)",99,"SYS",2,0);
      return(-1);   	                         	      
    }
       	    
    $data = fread($fread,filesize($logfile));
    fclose($fread);    
   	        
    if (!empty($data))
    {  	        
  	  $data_array = explode("\n",$data);	
      $err_rows = 0;
                  
  	  foreach ($data_array as $data_row)
  	  { 
  	    if (!empty($data_row))
        {
          if (preg_match("/IMP-|ORA-/",$data_row))
          {
            $err_rows++;
            logmsg("import()::ERROR $data_row",99,"SYS",2,0);                        	            
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
      logmsg("import()::ERROR Empty log file",99,"SYS",2,0);
      return(-1);   	          
    }
    
    logmsg("import()::end",0,"SYS",2,0);
    return(0);
  }
          
	// ----------------------------------------------------------------------
	// Function statistics
	// ----------------------------------------------------------------------
  
  function statistics()
  { 
    global $DEBUG;
    global $DEBUG_LOGACTION;
     	
    global $IMP_PATH;
    global $IMP_FILENAME;  
    global $IMP_MONTH;

    global $db_connection;
  
    logmsg("statistics()::start",0,"SYS",2,0);

    $sql_query = " select table_name, ".
                 "        table_name || '_P' || $IMP_MONTH ".
                 "   from user_tables ".
                 "  where table_name like 'AXALARMMSGS_A%' "; 
	            
    if (!empty($DEBUG)) logmsg("statistics()::CMD $sql_query",0,"SYS",$DEBUG_LOGACTION,0);
	                   	               
    if ($odbc_exec_id = odbc_exec($db_connection,$sql_query))
    {
      while(odbc_fetch_row($odbc_exec_id))
      {
        $table_name = odbc_result($odbc_exec_id,1);
        $part_name = odbc_result($odbc_exec_id,2);
                
        if (!empty($table_name) && !empty($part_name)) 
        {          
      	  $sql_query = "begin ".
                       "  dbms_stats.gather_table_stats(ownname=>'AXUSER',tabname=>'$table_name',partname=>'$part_name',granularity=>'PARTITION',cascade=>TRUE,estimate_percent=>10); ".
                       "end; ";

          if (!empty($DEBUG)) logmsg("statistics()::CMD $sql_query",0,"SYS",$DEBUG_LOGACTION,0);
                       	               
          if (!(odbc_exec($db_connection,$sql_query)))
          {
            $errmsg = odbc_errormsg($db_connection);
            logmsg("statistics()::ERROR Execute failed MSG=$errmsg QUERY=$sql_query",99,"SYS",2,0);
            return(-1);       
          }	          
        }
        else
        {  
          logmsg("statistics()::ERROR Empty table/partition name TABLE=$table_name PARTITION=$part_name",99,"SYS",2,0);      
          return(-1);
        }
      }
    }	  
    else
    {
      $errmsg = odbc_errormsg($db_connection);
      logmsg("statistics()::ERROR Execute failed MSG=$errmsgQUERY=$sql_query",99,"SYS",2,0);      
      return(-1); 
    }  

    logmsg("statistics()::end",0,"SYS",2,0);
    return(0);    
  }    	  
	// ----------------------------------------------------------------------
	// Function logtab
	// ----------------------------------------------------------------------
  
  function logmsg($message,$status=0,$responsible="SYS",$action=0,$stop=0)
  { 
    global $DEBUG;
    global $DEBUG_LOGACTION;
    
    global $db_connection;
    
    if ($action != 1)
    {
      echo "MSG=$message *** RESPONSIBLE=$responsible\n";       
    }
    
    if ($action == 1 || $action == 2)
    {    
      $message = substr(str_replace("'","''",$message),0,3998);
      
  	  $sql_query = "begin ".
  	               " insert into axuser.axlogmsg (status,message,responsible)".
  	               "  values($status,'$message','$responsible'); ".
  	               " commit; ".
  	               "end; ";
               	 
      if (!empty($DEBUG)) echo "logmsg()::CMD $sql_query\n"; 
               	               
      if (!(odbc_exec($db_connection,$sql_query)))
      {
        $errmsg = odbc_errormsg($db_connection);
        echo "logmsg()::ERROR Execute failed\n$errmsg\n$sql_query\n";            
        $stop = 1;
      }	                
    }
        
    if ($stop)
    {
      odbc_close($db_connection);         
      die("\nlogmsg()::STOP\n\n");      
    }  
  }	    
?>
