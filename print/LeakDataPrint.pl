#!/usr/bin/perl

# Get leak report data filename from 1st command line argument
if ($#ARGV < 0) {
	die("No leak report data file specified");
}
$filename = $ARGV[0];

# Open the data file:
#	1st line = parameter names
#	2nd line = data values
open(FILE, $filename) or die("Error opening file $filename");
@data = <FILE>;
close(FILE);

# Split up parameter and data values
if ($#data < 1) {
	die("Error reading leak report data file");
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

# Overwrite data file with formatted leak report
open(FILE, ">$filename") or die("Error creating output file");
$spacer = "                    ";
print FILE "\n\n\n";
print FILE "$spacer   **********************************\n";
print FILE "$spacer   *                                *\n";
print FILE "$spacer   *     PETRO-CANADA PRODUCTS      *\n";
print FILE "$spacer   *          LEAK REPORT           *\n";
print FILE "$spacer   *                                *\n";
print FILE "$spacer   *    LEAK ALARM ACTIVATED ON     *\n";
print FILE "$spacer   *     $LeakReportTime      *\n";
print FILE "$spacer   *                                *\n";
print FILE "$spacer   **********************************\n";
print FILE "\n\n\n";
print FILE "$spacer -------------------------------------- \n";
print FILE "$spacer|  Leak Values for Meter 9513 to Esso  |\n";
print FILE "$spacer|                                      |\n";
printf FILE "$spacer|        Leak Rate:  %10.2f        |\n", $LeakRate9513;
print FILE "$spacer|                                      |\n";
printf FILE "$spacer|  1 Minute Leak Volume:  %10.3f   |\n", $Leak9513_1MVol;
printf FILE "$spacer|  5 Minute Leak Volume:  %10.3f   |\n", $Leak9513_5MVol;
printf FILE "$spacer|  1 Hour Leak Volume  :  %10.3f   |\n", $Leak9513_1HVol;
printf FILE "$spacer|  8 Hour Leak Volume  :  %10.3f   |\n", $Leak9513_8HVol;
print FILE "$spacer -------------------------------------- \n";
print FILE "\n\n\n";
print FILE "$spacer -------------------------------------- \n";
print FILE "$spacer|  Leak Values for Meter 9514 to Esso  |\n";
print FILE "$spacer|                                      |\n";
printf FILE "$spacer|        Leak Rate:  %10.2f        |\n", $LeakRate9514;
print FILE "$spacer|                                      |\n";
printf FILE "$spacer|  1 Minute Leak Volume:  %10.3f   |\n", $Leak9514_1MVol;
printf FILE "$spacer|  5 Minute Leak Volume:  %10.3f   |\n", $Leak9514_5MVol;
printf FILE "$spacer|  1 Hour Leak Volume  :  %10.3f   |\n", $Leak9514_1HVol;
printf FILE "$spacer|  8 Hour Leak Volume  :  %10.3f   |\n", $Leak9514_8HVol;
print FILE "$spacer -------------------------------------- \n";
print FILE "\n\n\n";
print FILE "$spacer -------------------------------------- \n";
print FILE "$spacer|  Leak Values for Esso Meter to Shell |\n";
print FILE "$spacer|                                      |\n";
printf FILE "$spacer|        Leak Rate:  %10.2f        |\n", $LeakRate9561;
print FILE "$spacer|                                      |\n";
printf FILE "$spacer|  1 Minute Leak Volume:  %10.3f   |\n", $Leak9561_1MVol;
printf FILE "$spacer|  5 Minute Leak Volume:  %10.3f   |\n", $Leak9561_5MVol;
printf FILE "$spacer|  1 Hour Leak Volume  :  %10.3f   |\n", $Leak9561_1HVol;
printf FILE "$spacer|  8 Hour Leak Volume  :  %10.3f   |\n", $Leak9561_8HVol;
print FILE "$spacer -------------------------------------- \n";
close(FILE);

# Send leak report to printer 
system ("print", $filename) == 0 or die 'no print'

