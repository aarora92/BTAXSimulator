<?php 
  // --------------------------------------------------------------------
  // exp.php
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
  $ERROR_LOGPATH="o:\\oracle\\dba\\aximst\\log";
  $ERROR_LOGFILEHANDLE; 
  
  // --------------------------------------------------------------------
  // Export
  // --------------------------------------------------------------------

  // Export (Yes=1/No=0)
  $EXPORT_FLAG=1; 
  // Dump file destination
  $EXP_PATH="Z:\_BACKUP\datenbank\AXIMST\exp";
  // Export file praefix
  $EXP_FILENAME="exp_"; 
  // Export month (x months + current stay in db, ==> EXPORT_DELETE_FLAG=1) 
  $EXP_MONTH=-2; 
  // Export month should be deleted (Yes=1/No=0)
  $EXPORT_DELETE_FLAG=0; 
  // Gather statisctics on tables for export month (Yes=1/No=0)
  $EXPORT_STATISTICS_FLAG=1; 
  
  $EXPORT_LOGFILE="o:\\oracle\\dba\\aximst\\log\\export_".date("Ymd").".log";  
  $ERROR_EXPORT_LOGFILE = $ERROR_LOGPATH."\\err_exportlog_".date('Ymd').".log";  
        
  // --------------------------------------------------------------------
  // DB connect information
  // --------------------------------------------------------------------
  
  $ODBC_DRIVER_NAME="AXIMST";
  $USER="AXUSER";
  $PWD="ax";
  $SID="AXIMST";
  
  dl ("axphpextension.dll");
  require 'general/classes.inc';
  require 'general/functions.inc';
  
  $dbg = new aXDebug("Database Admin PHP","EXPORT");
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
  
  // ----------------------------------------------------------------------
  // Main
  // ----------------------------------------------------------------------
        
  writelogmsg("main::start",$EXPORT_LOGFILE);           
        
  if ($db_connection = odbc_connect($ODBC_DRIVER_NAME,$USER,$PWD))
  {  
    if (!empty($EXPORT_FLAG))
    { 
      if (export() == 0)
      {
        if (chk_exportlog() == 0)
        {
          if (!empty($EXPORT_DELETE_FLAG))
          {      
            if (delete_exports() != 0)
            {
              writelogmsg("EXPORT-001 Delete export month failed",$EXPORT_LOGFILE);
            }
            else
            {
              if (!empty($EXPORT_STATISTICS_FLAG))
              {
                if (statistics() != 0)
                {      
                  writelogmsg("EXPORT-002 Update statistics for export month failed",$EXPORT_LOGFILE);
                }
              }
              else
              {
                writelogmsg("Update statistics for export month disabled",$EXPORT_LOGFILE);                        
              }            
            }
          }      
          else
          {
            writelogmsg("Delete export month disabled",$EXPORT_LOGFILE);            
          }
          
          chk_exportlog();       
        }          
      } 
      else
      {      
        writelogmsg("EXPORT-003 Export failed",$EXPORT_LOGFILE);          
		chk_exportlog();
      }
    }
    else
    {
      writelogmsg("Export disabled",$EXPORT_LOGFILE);                        
    }
    
    odbc_close($db_connection);     
  }      
  else
  {
    writelogmsg("EXPORT-004 Could not connect to database (SID=$SID)",$EXPORT_LOGFILE);                        
  }

  writelogmsg("main::end",$EXPORT_LOGFILE);    
         
  closelogmsg();
  
  $dbg->writeMessage("finished ...");

	exit(0); 	
	
	// ----------------------------------------------------------------------
	// Function export
	// ----------------------------------------------------------------------
	
  function export()
  {  		
    global $DEBUG;
    
    global $USER;
    global $PWD;
    global $SID;
    
    global $EXPORT_LOGFILE;
    global $EXP_PATH;
    global $EXP_FILENAME;  
    global $EXP_MONTH;

    global $db_connection;
    
	  writelogmsg("export():start",$EXPORT_LOGFILE);           
    
    $sql_query = "select to_char(add_months(sysdate,$EXP_MONTH-1),'YYYYMM') || '01 00:00:00', ".
                 "       to_char(add_months(sysdate,$EXP_MONTH),'YYYYMM') || '01 00:00:00', ".
                 "       'exp userid=$USER/$PWD@$SID' || ".
                 "       ' direct=N indexes=N triggers=N constraints=N grants=N statistics=NONE ' || ".
                 "       ' tables=axuser.axalarmmsgs_a12lan,axuser.axalarmmsgs_a12mh, '  || ".
                 "       '  axuser.axalarmmsgs_a12mil,axuser.axalarmmsgs_a12rop, ' || ".
                 "       '  axuser.axalarmmsgs_a12sw,axuser.axalarmmsgs_a12twi, '  || ".
                 "       '  axuser.axalarmmsgs_a12wil,axuser.axalarmmsgs_a13bgi, ' || ".
                 "       '  axuser.axalarmmsgs_a13sbg,axuser.axalarmmsgs_a13sbh '  || ".
                 "       ' query=\\\"where time >= to_date(''' || to_char(add_months(sysdate,$EXP_MONTH-1),'YYYYMM') || '01 00:00:00'',''YYYYMMDD HH24:MI:SS'')' || ".
                 "       '  and time <  to_date(''' || to_char(add_months(sysdate,$EXP_MONTH),'YYYYMM') || '01 00:00:00'',''YYYYMMDD HH24:MI:SS'')' || ". 
                 "       '  \\\"' || ".
                 "       ' file=$EXP_PATH\\$EXP_FILENAME' || to_char(add_months(sysdate,$EXP_MONTH-1),'YYYYMM') || '.dmp ' || ".
                 "       ' log=$EXP_PATH\\$EXP_FILENAME'  || to_char(add_months(sysdate,$EXP_MONTH-1),'YYYYMM') || '.log ' ,".
                 "       '$EXP_PATH\\$EXP_FILENAME' || to_char(add_months(sysdate,$EXP_MONTH-1),'YYYYMM') || '.dmp',".
                 "       '$EXP_PATH\\$EXP_FILENAME' || to_char(add_months(sysdate,$EXP_MONTH-1),'YYYYMM') || '.log' ".
                 "  from dual ";
                 
    if (!empty($DEBUG)) writelogmsg("export()::CMD $sql_query");
                 
    if ($odbc_exec_id = odbc_exec($db_connection,$sql_query))
    {        
      if (odbc_fetch_row($odbc_exec_id))
      {
        $sdate = odbc_result($odbc_exec_id,1);
        $edate = odbc_result($odbc_exec_id,2);
        
        if (empty($sdate) || empty($edate))
        {
          $errmsg = odbc_errormsg($db_connection);      
          writelogmsg("EXPORT-101 Could not get start and end date ($errmsg)",$EXPORT_LOGFILE);
          return(-1);
        }  

        writelogmsg("export()::$sdate >= time < $edate",$EXPORT_LOGFILE);
        
        $cmd = odbc_result($odbc_exec_id,3);
        $dmpfile = odbc_result($odbc_exec_id,4);
        $logfile = odbc_result($odbc_exec_id,5);
        
        if (!empty($cmd) && !empty($logfile) && !empty($dmpfile)) 
        {
          if (is_readable($dmpfile) || is_readable($logfile))
          {
            writelogmsg("EXPORT-110 Dump or log file already exists (dmpfile=$dmpfile, logfile=$logfile)",$EXPORT_LOGFILE);
            return(-1);            
          }
          
          if (!empty($DEBUG)) writelogmsg("export()::CMD $cmd");
          
          $rc_exec = exec("start /SEPARATE ".$cmd,$data,$rc);                              

          if (!empty($DEBUG)) writelogmsg("export()::LOGFILE $logfile");
            
     	    if (!($fread = fopen($logfile,"r+")))
     	    {
            writelogmsg("EXPORT-102 Could not open log file (file=$logfile)",$EXPORT_LOGFILE);
            return(-1);
     	    }
     	    
    	    $data = fread($fread,filesize($logfile));
 	        fclose($fread);    
 	        
          writelogmsg($data,$EXPORT_LOGFILE);
          
          if (!empty($data))
          {  	        
        	  $data_array = explode("\n",$data);	
            $err_rows = 0;
                        
        	  foreach ($data_array as $data_row)
        	  { 
        	    if (!empty($data_row))
              {         
                if (preg_match("/EXP-\d|ORA-\d|PLS-\d|OSD-\d|-Error:/",$data_row))
                {          
                  $err_rows++;
                }   
                
                if (preg_match("/(\d+) Zeilen exportiert/",$data_row, $matcher) && $matcher[1] > 0)
                {          
                  $rows += $matcher[1];
                  writelogmsg($data_row,$EXPORT_LOGFILE);                    
                }   
              }
      	    }        	

            if ($err_rows == 0)
            {  
              if (!empty($rows))
              {
                writelogmsg("\n\nGesamtanzahl Zeilen=$rows\n",$EXPORT_LOGFILE);                                        
                
                $dmpfilesize = filesize($dmpfile)." Bytes";
                
                $sql_query = "insert into axuser.axexport(status,exp_dmpfile,exp_logfile,exp_size,start_date,end_date) ".
                             " values(0,'".$dmpfile."','".
                                           $logfile."','".
                                           $dmpfilesize."',".
                                           "to_date('".$sdate."','YYYYMMDD HH24:MI:SS'),".
                                           "to_date('".$edate."','YYYYMMDD HH24:MI:SS')) ";
                                   
                if (!empty($DEBUG)) writelogmsg("export()::CMD $sql_query");
                                           
                if (!odbc_exec($db_connection,$sql_query))
                {        
                  writelogmsg("EXPORT-103 Insert into table axeport failed",$EXPORT_LOGFILE);
                  return(-1);                  
                }
              }
            }     	            
            else
            {
              writelogmsg("EXPORT-104 No insert into axeport - error",$EXPORT_LOGFILE);                
            }
          }  
        }
        else
        {
          writelogmsg("EXPORT-106 Empty cmd=$cmd / logfile=$logfile / dmpfile=$dmpfile",$EXPORT_LOGFILE);
          return(-1);
        }
      }
      else
      {
        $errmsg = odbc_errormsg($db_connection);     
        writelogmsg("EXPORT-107 Now row selected MSG=$errmsg QUERY=$sql_query",$EXPORT_LOGFILE);
        return(-1);
      }  
    }      
    else
    {
      $errmsg = odbc_errormsg($db_connection);
      writelogmsg("EXPORT-108 Execute failed MSG=$errmsg QUERY=$sql_query",$EXPORT_LOGFILE);
      return(-1);
    }

	  writelogmsg("export():end",$EXPORT_LOGFILE);           
    return(0);
  }
       
	// ----------------------------------------------------------------------
	// Function delete_exports
	// ----------------------------------------------------------------------
          	
  function delete_exports()
  {  	
    global $DEBUG;
    
    global $EXPORT_LOGFILE;    
    global $EXP_PATH;
    global $EXP_FILENAME;  
    global $EXP_MONTH;

    global $db_connection;

	  writelogmsg("delete_exports():start",$EXPORT_LOGFILE);           

    $sql_query = " select to_date(to_char(add_months(sysdate,$EXP_MONTH),'YYYYMM') || '01 00:00:00','YYYYMMDD HH24:MI:SS'), ".
                 "        'AXUSER.' || table_name ".
                 "   from user_tables ".
                 "  where table_name like 'AXALARMMSGS_A%' "; 
	            
    if (!empty($DEBUG)) writelogmsg("delete_exports()::CMD $sql_query");
	                   	               
    if ($odbc_exec_id = odbc_exec($db_connection,$sql_query))
    {
      while(odbc_fetch_row($odbc_exec_id))
      {
        $edate = odbc_result($odbc_exec_id,1);
        $table_name = odbc_result($odbc_exec_id,2);
        
        if (!empty($table_name)) 
        {
          writelogmsg("delete()::TABLE=$table_name WHERE=time < $edate",$EXPORT_LOGFILE);

      	  $cmd = "begin ".
                 "  loop ".
                 "    delete from $table_name ".
                 "      where time < to_date(to_char(add_months(sysdate,$EXP_MONTH),'YYYYMM') || '01 00:00:00','YYYYMMDD HH24:MI:SS') ".
                 "        and rownum <= 5000; ".
                 "    exit when SQL%rowcount < 5000; ".
                 "    commit; ".
                 "  end loop; ".
                 "  commit; ".
                 "  exception ".
                 "    when others then ".
                 "      rollback; ".
                 "end; ";  
                  
          if (!empty($DEBUG)) writelogmsg("delete_exports()::CMD $cmd");
          
          if(!(odbc_exec($db_connection,$cmd)))
          {
            $errmsg = odbc_errormsg($db_connection);
            writelogmsg("EXPORT-201 Execute failed MSG=$errmsg QUERY=$cmd",$EXPORT_LOGFILE);          
            return(-1);
          }
        }
        else
        {
          writelogmsg("EXPORT-202 Empty table name",$EXPORT_LOGFILE);                    
          return(-1);
        }
      }
    }	  
    else
    {
      $errmsg = odbc_errormsg($db_connection);
      writelogmsg("EXPORT-203 Execute failed MSG=$errmsg QUERY=$sql_query",$EXPORT_LOGFILE);                    
      return(-1);
    }  
    
	  if (!empty($DEBUG)) writelogmsg("delete_exports():end");           
    return(0);
  }
  
	// ----------------------------------------------------------------------
	// Function statistics
	// ----------------------------------------------------------------------
  
  function statistics()
  { 
    global $DEBUG;
         	
    global $EXPORT_LOGFILE;             	
    global $EXP_MONTH;

    global $db_connection;
  
	  writelogmsg("statistics():start",$EXPORT_LOGFILE);           

    $sql_query = " select table_name, ".
                 "        table_name || '_P' || to_char(add_months(sysdate,$EXP_MONTH-1),'YYYYMM') ".
                 "   from user_tables ".
                 "  where table_name like 'AXALARMMSGS_A%' "; 
	            
    if (!empty($DEBUG)) writelogmsg("statistics()::CMD $sql_query");
	                   	               
    if ($odbc_exec_id = odbc_exec($db_connection,$sql_query))
    {
      while(odbc_fetch_row($odbc_exec_id))
      {
        $table_name = odbc_result($odbc_exec_id,1);
        $part_name = odbc_result($odbc_exec_id,2);
                
        if (!empty($table_name) && !empty($part_name)) 
        {          
          writelogmsg("statistics():table=$table_name partition=$part_name",$EXPORT_LOGFILE);
          
      	  $sql_query = "begin ".
                       "  dbms_stats.gather_table_stats(ownname=>'AXUSER',tabname=>'$table_name',partname=>'$part_name',granularity=>'PARTITION',cascade=>TRUE,estimate_percent=>10); ".
                       "end; ";

          if (!empty($DEBUG)) writelogmsg("statistics()::CMD $sql_query");
                       	               
          if (!(odbc_exec($db_connection,$sql_query)))
          {
            $errmsg = odbc_errormsg($db_connection);
            writelogmsg("EXPORT-301 Execute failed MSG=$errmsg QUERY=$sql_query",$EXPORT_LOGFILE);
            return(-1);
          }	          
        }
        else
        {  
          writelogmsg("EXPORT-302 Empty table/partition name TABLE=$table_name PARTITION=$part_name",$EXPORT_LOGFILE);                 
          return(-1);
        }
      }
    }	  
    else
    {
      $errmsg = odbc_errormsg($db_connection);
      writelogmsg("EXPORT-303 Execute failed MSG=$errmsg QUERY=$sql_query",$EXPORT_LOGFILE);
      return(-1);
    }  

	  writelogmsg("statistics():end",$EXPORT_LOGFILE);           
    return(0);
  }    	  
    
	// ----------------------------------------------------------------------
	// Function chk_exportlog
	// ----------------------------------------------------------------------
    
  function chk_exportlog()
  {  		
    global $DEBUG;
    
    global $EXPORT_LOGFILE;    
    global $ERROR_EXPORT_LOGFILE;  
       
	  writelogmsg("chk_exportlog():start",$EXPORT_LOGFILE);           
                  
    if (!($fread = fopen($EXPORT_LOGFILE,"r+")))
    {
      writelogmsg("EXPORT-401 Could not open export log file (file=$EXPORT_LOGFILE)",$ERROR_EXPORT_LOGFILE);
      return(-1);
    }
       	    
    $data = fread($fread,filesize($EXPORT_LOGFILE));
    fclose($fread);    
    
    if (!empty($data))
    {  	        
  	  $data_array = explode("\n",$data);	
      $err_rows = 0;
                  
  	  foreach ($data_array as $data_row)
  	  { 
  	    if (!empty($data_row))
        {         
          if (preg_match("/EXP-\d|ORA-\d|PLS-\d|OSD-\d|-Error:|EXPORT-\d/",$data_row))
          {          
            $err_rows++;
            writelogmsg(trim($data_row),$ERROR_EXPORT_LOGFILE);
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
      writelogmsg("EXPORT-402 Empty export log file (file=$EXPORT_LOGFILE)",$ERROR_EXPORT_LOGFILE);
      return(-1);
    }

	  writelogmsg("chk_exportlog():end",$EXPORT_LOGFILE);           
    return(0);
  }
              
     
  
	// ----------------------------------------------------------------------
	// Function stop
	// ----------------------------------------------------------------------
  
  function stop($message)
  {
    global $db_connection;
     
    if (!empty($db_connection)) odbc_close($db_connection);     
    closelogmsg();    
    die($message."\nStop\n");
  }     
?>
