The following processes will be started from flash
according to the specified parameters.

Parameters are:
name.................... the process name
menu.................... the string in the application menu
autostart............... wheter the process shall be started from flash
                         automaticially or only explicitly after an user action.
restart...............   wheter the process shall be REstarted from flash
                         if it fails to send its lifetick.
Terminal................ Only,True,False
always.................. True, False, if this switch is set to False process is
                         not started if host stands alone.

:name=sps autostart=True restart=False
:name=slowsps autostart=True restart=True
:name=plserver autostart=True restart=True always=True
:name=msgserver autostart=True restart=True always=True
:name=rdserver autostart=True restart=True always=False
:name=fsv autostart=True restart=True always=False
:name=txnexgen  autostart=True restart=True always=True
:name=pls autostart=True restart=True Terminal=True always=True
:name=txoverview menu=Trend-List autostart=False restart=False always=True
:name=trenddisplay menu=Trend autostart=False restart=False always=True
:name=browser menu=System-Browser autostart=False restart=False Terminal=True always=True
:name=debug menu=System-Debugger autostart=False restart=False Terminal=True always=True
name=seq autostart=True restart=True Terminal=False always=True
:name="axservice -run" autostart=True restart=True Terminal=True always=True
:name=tcpserver autostart=True restart=True always=True
:name=axopcclient autostart=True restart=True alway=True


PCN Clients:
:name="axprocessviewer -x=0 -w=1280 -y=0 -h=985 -waitserver -display PCN212AXLAB:0" autostart=True restart=True			//aX Lab Standup Desk
:name="axprocessviewer -x=0 -w=1280 -y=0 -h=985 -waitserver -display AutoX-WIN10:0" autostart=True restart=True			//aX Lab Sitting Desk
:name="axprocessviewer -x=0 -w=1280 -y=0 -h=985 -waitserver -display USER-PC:0" autostart=True restart=True				//aX Ops Office 
:name="axprocessviewer -x=0 -w=1280 -y=0 -h=985 -waitserver -display AX-MID-PLANT:0" autostart=True restart=True			//Mid Plant Engineering Station PCN
name="axprocessviewer -x=0 -w=1280 -y=0 -h=985 -waitserver -display PCN212-spare2:0" autostart=True restart=True			//Conference Room PCN1
:name="axprocessviewer -x=0 -w=1280 -y=0 -h=985 -waitserver -display PCN212LNJ1:0" autostart=True restart=True			//Conference Room PCN2

:name="axprocessviewer -x=0 -w=1280 -y=0 -h=985 -waitserver -display BTMIDPLANT:0" autostart=True restart=True			//Mid Plant PCN  
:name="axprocessviewer -x=0 -w=1280 -y=0 -h=985 -waitserver -display PCN-WDOCK:0" autostart=True restart=True			//West Dock PCN 
:name="axprocessviewer -x=0 -w=1280 -y=0 -h=985 -waitserver -display PCN-EDOCK:0" autostart=True restart=True			//East Dock PCN 
:name="axprocessviewer -x=0 -w=1280 -y=0 -h=985 -waitserver -display PCN-RAIL:0" autostart=True restart=True				//Rail PCN



BLAN Clients:
:name="axprocessviewer -x=0 -w=1280 -y=0 -h=985 -waitserver -display 10.26.36.124:0" autostart=True restart=True			//aX Lab Standup Desk BLAN -- D96491
:name="axprocessviewer -x=0 -w=1280 -y=0 -h=985 -waitserver -display 10.26.36.26:0" autostart=True restart=True			//aX Lab Sitting Desk BLAN -- D129767
:name="axprocessviewer -x=0 -w=1280 -y=0 -h=985 -waitserver -display 10.26.36.169:0" autostart=True restart=True			//aX Ops Office BLAN -- D129916
:name="axprocessviewer -x=0 -w=1280 -y=0 -h=985 -waitserver -display 10.26.32.62:0" autostart=True restart=True			//aX Ops Terry Desk -- D129763

:name="axprocessviewer -x=0 -w=1280 -y=0 -h=985 -waitserver -display 10.26.36.115:0" autostart=True restart=True			//Rob M. -- D129929 
:name="axprocessviewer -x=0 -w=1280 -y=0 -h=985 -waitserver -display 10.26.36.70:0" autostart=True restart=True			//Chris P. -- D129926
:name="axprocessviewer -x=0 -w=1280 -y=0 -h=985 -waitserver -display 10.26.37.168:0" autostart=True restart=True			//Mid Plant BLAN -- D129919
:name="axprocessviewer -x=0 -w=1280 -y=0 -h=985 -waitserver -display 10.26.37.166:0" autostart=True restart=True			//Mid Plant BLAN -- D129943
name="axprocessviewer -x=0 -w=1280 -y=0 -h=985 -waitserver -display D129931:0" autostart=True restart=True				//Lower Plant, Doug's (Disabled as required by Doug)
:name="axprocessviewer -x=0 -w=1280 -y=0 -h=985 -waitserver -display 10.26.38.97:0" autostart=True restart=True			//Lower Plant, Frank's -- D129786
:name="axprocessviewer -x=0 -w=1280 -y=0 -h=985 -waitserver -display 10.26.36.126:0" autostart=True restart=True			//Lower Plant BLAN -- D129768
:name="axprocessviewer -x=0 -w=1280 -y=0 -h=985 -waitserver -display 10.26.38.56:0" autostart=True restart=True			//Lower Plant BLAN -- D129937
:name="axprocessviewer -x=0 -w=1280 -y=0 -h=985 -waitserver -display 10.26.38.107:0" autostart=True restart=True			//East Dock BLAN -- D129781
:name="axprocessviewer -x=0 -w=1280 -y=0 -h=985 -waitserver -display 10.26.38.101:0" autostart=True restart=True			//West Dock BLAN -- D96292
:name="axprocessviewer -x=0 -w=1920 -y=0 -h=1080 -waitserver -display 10.26.38.11:0" autostart=True restart=True			//Rail BLAN -- D96294
:name="axprocessviewer -x=0 -w=1920 -y=0 -h=1080 -waitserver -display 10.26.36.170:0" autostart=True restart=True			//Rail BLAN -- D129932 Igor
:name="axprocessviewer -x=0 -w=1920 -y=0 -h=1080 -waitserver -display 10.26.36.167:0" autostart=True restart=True			//Maintenance -- D128473 Michael
:name="axprocessviewer -x=0 -w=1920 -y=0 -h=1080 -waitserver -display 10.26.36.117:0" autostart=True restart=True			//R. Burgess -- D128535 Michael



Sample Line:
name="axprocessviewer -x=0 -w=1280 -y=0 -h=985 -nodecoration -waitserver -display COMPUTER-NAME:0" autostart=True restart=True	//Example

