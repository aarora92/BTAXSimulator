*******************************************************************************
Sepp Masswohl, 22.Feb,1996
File: startup.pls
*******************************************************************************
*******************************************************************************
This file holds some basic startup information.
flash reads this file and initializes according data entries in the
shared memory.
A valid line starts with the colon sign (':')!
*******************************************************************************
*******************************************************************************
:truecolor=True		 // options:True,False; default:False
:plc_autostart=True    // options:True,False; default:False
:plc_autostart_delay=1 // default: 30 sec
:shm_size=64 MB
:shm_count=5000
:et200_01_bd=500        // options:500,187.5,9.6,19.2; default:500
:et200_02_bd=500 
:et200_03_bd=500 
:et200_04_bd=500 
:et200_11_bd=500 
:et200_12_bd=500 
:et200_13_bd=500 
:et200_14_bd=500 
:hostmode=standalone    // options:standalone,redundant; default:standalone
server=MASTER1         // hostname in /etc/hosts 
client=MASTER8         // hostname in /etc/hosts
:printer=lp             // printername in /etc/printcap
display=MASTER7         // indicates terminal !
:language=english       // options:deustch,english,france,svenska,italian; default:english
:plc_cycle_time=0.25   // default:0.1 sec;
:avoid_master_change=False      // options:True,False; default:False
:logger=True

