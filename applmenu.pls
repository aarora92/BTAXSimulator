#The PLS reads this file and builds up the main application menu 
#from it.
#keywords are "item","text","hotkey" and "menu".
#The '{' and the '}' signs are used to structure the menus.
#Predefined values are "separator" for the keyword "text"
#and "USERPROG" for the keyword "menu"


menu=APPLICATION text="AutomationX"
{

	text=separator

#	menu=PREPARATION	text="Batch System"
#	{
#		item=BATCH		text="Batch"
#		text=separator
#		item=SELECTION		text="Start Modes"		
#		item=RECIPE		text="Recipe List"	      
#		item=TANK_DEF		text="Tank List"	
#		item=PRODUCT_DEF	text="Product List"		hotkey=F7
#		text=separator
#		item=STAT_BATCH		text="Statistics"     
#	}	

	menu=ISPS	text="Soft PLC"
	{
		item=SPS_PROJECT	text="Project"
		item=SPS_TYPEDEF	text="Types"
		item=SPS_GLOBAL		text="Global Data"
		item=SPS_MESSAGES	text="PLC Messages"
		text=separator
		item=SPS_EDITOR		text="PLC Editor"
	}	
	
	menu=ADDITIONAL	text="Journals"
	{
		item=JOURNAL		text="Application Journal"		hotkey=F3
		item=SYSTEMJOURNAL	text="System Journal"
	}	
	menu=SYSTEM	text="System"
	{
		item=EDIT_PASSWORD	text="Change Passwords"
		item=RELOAD_PIC		text="Reload Process Pictures"
		menu=LANGUAGES		text="Languages"
		{
			item=GERMAN	text="DEUTCH"
			item=ENGLISH	text="ENGLISH"
			item=SVENSKA	text="FRANCAIS"
		}
		item=REBOOT		text=Reboot
		item=SHUTDOWN		text=Shutdown
		text=separator
		item=COLOR		text="Colours" hotkey=F4
		item=PICTURES		text="Config Process Pictures V3.2"
		item=PBE		text="Process Picture Editor  V3.2"
		text=separator		
		item=SYSTEM_TERMINAL	text="X-Terminal"
		
	}	

	text=separator
	menu=USERPROG		text="User Programms"
	item=PASSWORD		text="Keyword"  hotkey=F1	

	text=separator
	
}

#  {Pbi_Start,"PBI"},
#  {EditorPbi_Start,"PBI_NEW"},
#  {Password,"PASSWORD"},
#  /*******************************/   
#  {Batch,"BATCH"},
#  {Batchcontrol,"SELECTION"},
#  {RecipeList,"RECIPE"},
#  {Product,"PRODUCT_DEF"},
#  {Tank,"TANK_DEF"},
#  {Statistic,"STAT_BATCH"},
#  /*******************************/   
#  {Message,"JOURLAL"},
#  {Sysmes,"SYSTEMJOURNAL"},
#  {PpList,"PROCESS_PARAMETER"},
#  {UsrList,"SYSTEM_COMMANDS"},
#  {RemovePrinterJob,"STAT_BATCH"},
#  /*******************************/   
#  {Spsdef,"SPS_PROJECT"},
#  {Typedef,"SPS_TYPEDEF"},
#  {GlobalData,"SPS_GLOBAL"},
#  {Sps_StartEditorFromMenu,"SPS_EDITOR"},
#  {CompileMessage,"STAT_BATCH"},
#  /*******************************/   
#  {PasswordDefine,"EDIT_PASSWORD"},
#  {Pb_StartEditor,"PBI_EDITOR"},
#  {GlobalData,"STAT_BATCH"},
#  {App_ReloadPictures,"RELOAD_PIC"},
#  {Col_Setup,"COLOR"},
#  {Passw,"DEF_PASSWORD"},
#  {Pictures,"STAT_BATCH"},
#  {Terminal,"SYSTEM_TERMINAL"},
#  {Reboot,"REBOOT"},
#  {Shutdown,"SHUTDOWN"},
















