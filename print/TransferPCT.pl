#!/usr/bin/perl
use Net::FTP;

if ($#ARGV != 1) {
	die("Usage:  TransferPCT.pl <transfer 0|1> <filename> \n");
}

# Get transfer flag from 1st command line argument
$transfer = ($ARGV[0]>0);


# Get PCT data filename from 2nd command line argument
$filename = $ARGV[1];


# TEMPORARY LOGGING
open(LOG, ">>C:\\automationX\\projects\\BTAX\\print\\transfer.log") or die("Error creating log file");
printf LOG "transfer=%s filename=%s\n", $transfer, $filename;
close(LOG);


# Open the data file:
#	1st line = parameter names
#	2nd line = data values
open(FILE, $filename) or die("Error opening file $filename");
@data = <FILE>;
close(FILE);

# Split up parameter and data values
if ($#data < 1) {
	die("Error reading PCT data file");
}
chomp($data[0]);
chomp($data[1]);
@params = split("\t", $data[0]);
@values = split(/\t/, $data[1]);

# Create a variable for each param/value pair
for ($i=0; $i<=$#params; $i++) {
	$paramname = $params[$i];
	$$paramname = $values[$i];
}

# Create directory to save file if doesn't exist
@timedata = localtime();
$year = $timedata[5] + 1900;
$PCT_DIR = "C:\\automationX\\projects\\BTAX\\print\\pctdata\\";
$DATA_DIR = $PCT_DIR . $year;
$RAW_DIR  = $PCT_DIR . "raw";
if (! -e $DATA_DIR) {
	mkdir($DATA_DIR);
}

# Create formatted PCT data file
$outfile = sprintf("%s\\PCT_%s_%04d.txt", $DATA_DIR, $TicketName, $TicketRef);
open(FILE, ">$outfile") or die("Error creating output file");


# PCT Header Record
#TySourc     Referencenumber_____     Driverorbatchno     Customerbadge__     Finishda  EndtIntiStrtStop Loadnumber  Carrno  Truck Term
$pct_source = "08022";
$pct_ticket = sprintf("IOL %03d", $TicketRef);
$pct_batch  = sprintf("%02d%04d", $BatchID_A, $BatchID_B);
$pct_custnr = "5025702";
$pct_header = sprintf("%s     %s                   %s             %s", $pct_source, $pct_batch, $pct_ticket, $pct_custnr);
printf FILE "01%s             %s                                       \r\n", $pct_header, $OpenTime;

# PCT Product Record
#TySourc     Referencenumber_____     Productcode_        Temper  Density  Ambientqty  Correctqty     Mr                           Term
#$pct_totals = sprintf("%010.1f  %010.1f", $GrossVol, $NetVol);
#$pct_totals = sprintf("%010.1f  %010.1f", $GrossInt*100, $NetInt*100);
$pct_totals = sprintf("%06u00.0  %06u00.0", $GrossInt, $NetInt);
printf FILE "05%s     %s                   %03d                 ", $pct_source, $pct_batch, $ProdSAP;
printf FILE "%+06.1f  %07.5f  %s                                  \r\n", $Temp, $Dens, $pct_totals;

# PCT Summary Record
#TySourc     Referencenumber_____     Driverorbatchno     Customerbadge__  Ttlambient  Ttlcorrect   Nrec                           Term
printf FILE "20%s          %s   0003                           \r\n", $pct_header, $pct_totals;
close(FILE);

# FTP file to PCT server
if ($transfer) {
#	$FTP_HOST = "pcvm0106";
#	$FTP_HOST = "pc0115";
        $FTP_HOST = "iprci";
	$FTP_USER = "iprpct";
	$FTP_PASS = "pctload";
	$FTP_DIR  = "/APPL/sp/tmsinter";
	
	$ftp = Net::FTP->new($FTP_HOST)
		or die "Cannot open connection to $FTP_HOST";
	$ftp->login($FTP_USER, $FTP_PASS)
		or die "Cannot login to $FTP_HOST as user $FTP_USER";
	$ftp->cwd($FTP_DIR)
		or die "Cannot change to FTP directory $FTP_DIR";
	$ftp->put($outfile)
		or die "Cannot upload file $outfile";
	$ftp->quit;
	
        $FTP_HOST = "pca449";
	$FTP_PORT = 22;
	$FTP_USER = "WebMethods";
	$FTP_PASS = "WebMethods";
	$FTP_DIR  = "OSPtoTDS/Burrard";
	
	$ftp = Net::FTP->new($FTP_HOST, Port => $FTP_PORT)
		or die "Cannot open connection to $FTP_HOST";
	$ftp->login($FTP_USER, $FTP_PASS)
		or die "Cannot login to $FTP_HOST as user $FTP_USER";
	$ftp->cwd($FTP_DIR)
		or die "Cannot change to FTP directory $FTP_DIR";
	$ftp->put($outfile)
		or die "Cannot upload file $outfile";
	$ftp->quit;
}

# move original file to holding directory
system("move", $filename, $RAW_DIR);

