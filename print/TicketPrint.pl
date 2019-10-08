#!/usr/bin/perl

if ($#ARGV != 1) {
	die("Usage:  TicketPrint.pl <print/copy 0|1> <filename> \n");
}

# Get print/copy flag from 1st command line argument
$print = ($ARGV[0]>0);


# Get raw ticket data filename from 2nd command line argument
$filename = $ARGV[1];



# Open the data file:
#	1st line = parameter names
#	2nd line = data values
open(FILE, $filename) or die("Error opening file $filename");
@data = <FILE>;
close(FILE);

# Split up parameter and data values
if ($#data < 1) {
	die("Error reading raw data file");
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
$TICKET_DIR = "C:\\automationX\\projects\\BTAX\\print\\ticketdata\\";
$DATA_DIR = $TICKET_DIR . $year;
$RAW_DIR  = $TICKET_DIR . "raw";
if (! -e $DATA_DIR) {
	mkdir($DATA_DIR);
}

# Create formatted ticket data file
$outfile = sprintf("%s\\Meter%s_%04d.doc", $DATA_DIR, $MeterID, $TicketRef);
open(FILE, ">$outfile") or die("Error creating output file");


# Ticket Header
printf FILE "\n\n\n                             PETRO-CANADA PRODUCTS\n";
printf FILE "                             METER DELIVERY TICKET\n\n";
printf FILE "  DELIVERY TO      :  ESSO LOUGHEED TERMINAL\n\n";
printf FILE "  ACCOUNT OF       :  PETRO-CANADA\n\n";
printf FILE "  PRODUCT ID       :  %.2d                     TANK ID     : %.2d\n\n", $ProdID, $TankID;
printf FILE "  LOCATION         :  BURRARD PRODUCTS TERMINAL\n\n";
printf FILE "  BATCH ID NUMBER  :  %.2d-%.4d                TICKET REF# : %.3d\n\n", $BatchID_A, $BatchID_B, $TicketRef;
printf FILE "  ============================================================================\n\n";


# Ticket Meter Data		($sec,$min,$hour,$mday,$mon,$year,$wday,$yday,$isdst) = localtime(time);
@timeclose = localtime($CloseTime);
$close_date = sprintf("%.4d/%.2d/%.2d", $timeclose[5]+1900, $timeclose[4]+1, $timeclose[3]);
$close_time = sprintf("%.2d:%.2d:%.2d", $timeclose[2], $timeclose[1], $timeclose[0]);
@timeopen = localtime($OpenTime);
$open_date = sprintf("%.4d/%.2d/%.2d", $timeopen[5]+1900, $timeopen[4]+1, $timeopen[3]);
$open_time = sprintf("%.2d:%.2d:%.2d", $timeopen[2], $timeopen[1], $timeopen[0]);
printf FILE "  FLOW COMPUTER ID : %s     METER ID : %.2d\n\n", $MeterName, $MeterID;
printf FILE "                        CLOSE DATE : %s       CLOSE TIME : %s\n", $close_date, $close_time;
printf FILE "                        OPEN  DATE : %s       OPEN  TIME : %s\n\n", $open_date, $open_time;
printf FILE "     METER K FACTOR (PULSES/m3) ___:  %10.3f\n", $K_Factor;
printf FILE "     METER PROVE FACTOR ___________:  %10.4f\n\n", $Prove_Factor;
printf FILE "     CLOSING GROSS UNCAL. (m3) ____:  %10.1f\n", $Closing_Gross;
printf FILE "     OPENING GROSS UNCAL. (m3) ____:  %10.1f\n", $Opening_Gross;
printf FILE "     GROSS UNCAL VOLUME (m3) ______:  %10.1f\n\n", $Gross_Volume;
printf FILE "  FLOW WEIGHTED AVERAGES\n\n";
printf FILE "     TEMPERATURE (Celsius) ________:  %10.2f\n", $Temp;
printf FILE "     PRESSURE (Kilo Pascals) ______:  %10.1f\n", $Press;
printf FILE "     DENSITY (Kg/m3 @ 15 C) _______:  %10.1f\n", $Dens;
printf FILE "     VCF __________________________:  %10.5f\n", $VCF;
printf FILE "     CPL __________________________:  %10.5f\n\n", $CPL;
printf FILE "     NET WET VOLUME (m3) __________:  %10.1f\n\n", $Net_Volume;
printf FILE "     BAD PULSES ___________________:  %10.1d     (1 = Bad Pulses > 100) \n\n", $Bad_Pulses;


# Ticket Footer
printf FILE "  ============================================================================\n\n\n";
printf FILE "  WITNESSED BY ________________________________  FOR ESSO\n\n\n";
printf FILE "  WITNESSED BY ________________________________  FOR PETRO-CANADA PRODUCTS\n\n\n";
printf FILE "  PRODUCT ID's:  [00-RBOB   ] [01-91 SUPER] [02-92 SUPER] [03-    ] [04-STOVE]\n"; 
printf FILE "                 [05-LSSD   ] [06-B5 ULSD ] [07-        ] [08-    ] [09-     ]\n";

close(FILE);


# print ticket and copy to PIN server
if ($print) {
    system ("print", $outfile) == 0 or die 'lpr-printing failed';

	$COPY_DIR = "Z:\\LNJ\\LheedTickets\\" . $year;
	if (! -e $COPY_DIR) {
		mkdir($COPY_DIR);
	}
	system("copy", $outfile, $COPY_DIR);
}

# move original file to holding directory
system("move", $filename, $RAW_DIR);

