<?
    // Klassendefinition
class WundergroundWetter extends IPSModule
{
   // Der Konstruktor des Moduls
   // Überschreibt den Standard Kontruktor von IPS
   public function __construct($InstanceID)
		{
         // Diese Zeile nicht löschen
         parent::__construct($InstanceID);
         // Selbsterstellter Code
  		 }

   public function Create()
		{
     		 // Diese Zeile nicht löschen.
    		  parent::Create();

			$this->RegisterPropertyString("Wetterstation", "");
			$this->RegisterPropertyString("API_Key", "");
			$this->RegisterPropertyInteger("UpdateInterval", 1800);
     		$this->RegisterTimer("Update", 0, 'WD_Update($_IPS["TARGET"]);');
            
            //Variable Änderungen aufzeichnen
            $this->RegisterPropertyBoolean("logTemp_now", false);
            $this->RegisterPropertyBoolean("logTemp_feel", false);
            $this->RegisterPropertyBoolean("logTemp_dewpoint", false);
            $this->RegisterPropertyBoolean("logHum_now", false);
            $this->RegisterPropertyBoolean("logPres_now", false);
            $this->RegisterPropertyBoolean("logWind_deg", false);
            $this->RegisterPropertyBoolean("logWind_now", false);
            $this->RegisterPropertyBoolean("logWind_gust", false);
            $this->RegisterPropertyBoolean("logRain_now", false);
            $this->RegisterPropertyBoolean("logRain_today", false);
            $this->RegisterPropertyBoolean("logSolar_now", false);
            $this->RegisterPropertyBoolean("logVis_now", false);
            $this->RegisterPropertyBoolean("logUV_now", false);
            
            //Variablenprofil anlegen
            $this->Var_Pro_Erstellen("WD_Niederschlag",2,"Liter/m²",0,10,0,2,"Rainfall");
            $this->Var_Pro_Erstellen("WD_Sonnenstrahlung",2,"W/m²",0,2000,0,2,"Sun");
            $this->Var_Pro_Erstellen("WD_Sichtweite",2,"km",0,0,0,2,"");
            $this->Var_Pro_WD_WindSpeedkmh();
            $this->Var_Pro_WD_UVIndex();
  		 }

        // Überschreibt die intere IPS_ApplyChanges($id) Funktion
   public function ApplyChanges()
		{
         // Diese Zeile nicht löschen
         parent::ApplyChanges();

			if (($this->ReadPropertyString("API_Key") != "") AND ($this->ReadPropertyString("Wetterstation") != ""))
				{
					//Variablen erstellen
					$this->RegisterVariableFloat("Temp_now","Temperatur","Temperature",1);
					$this->RegisterVariableFloat("Temp_feel","Temperatur gefühlt","Temperature",2);
					$this->RegisterVariableFloat("Temp_dewpoint","Temperatur Taupunkt","Temperature",3);
					$this->RegisterVariableFloat("Hum_now","Luftfeuchtigkeit","Humidity.F",4);
					$this->RegisterVariableFloat("Pres_now","Luftdruck","AirPressure.F",5);
					$this->RegisterVariableFloat("Wind_deg","Windrichtung","WindDirection.Text",6);
					$this->RegisterVariableFloat("Wind_now","Windgeschwindigkeit","WD_WindSpeed_kmh",7);
					$this->RegisterVariableFloat("Wind_gust","Windböe","WD_WindSpeed_kmh",8);
					$this->RegisterVariableFloat("Rain_now","Niederschlag/h","WD_Niederschlag",9);
					$this->RegisterVariableFloat("Rain_today","Niederschlag Tag","WD_Niederschlag",10);
					$this->RegisterVariableFloat("Solar_now","Sonnenstrahlung","WD_Sonnenstrahlung",11);
					$this->RegisterVariableFloat("Vis_now","Sichtweite","WD_Sichtweite",12);
                    $this->RegisterVariableInteger("UV_now","UV Strahlung","WD_UV_Index",13);
		        //Timer zeit setzen
					$this->SetTimerInterval("Update", $this->ReadPropertyInteger("UpdateInterval")*1000);
                // Variable Logging Aktivieren/Deaktivieren
                if ($this->ReadPropertyBoolean("logTemp_now") === true)
                    $this-> VarLogging("Temp_now","logTemp_now",0);
                if ($this->ReadPropertyBoolean("logTemp_feel") === true)
                    $this-> VarLogging("Temp_feel","logTemp_feel",0);
                if ($this->ReadPropertyBoolean("logTemp_dewpoint") === true)
                    $this-> VarLogging("Temp_dewpoint","logTemp_dewpoint",0);
                if ($this->ReadPropertyBoolean("logHum_now") === true)
                    $this-> VarLogging("Hum_now","logHum_now",0);
                if ($this->ReadPropertyBoolean("logPres_now") === true)
                    $this-> VarLogging("Pres_now","logPres_now",0);
                if ($this->ReadPropertyBoolean("logWind_deg") === true)
                    $this-> VarLogging("Wind_deg","logWind_deg",0);
                if ($this->ReadPropertyBoolean("logWind_now") === true)
                    $this-> VarLogging("Wind_now","logWind_now",0);
                if ($this->ReadPropertyBoolean("logWind_gust") === true)
                    $this-> VarLogging("Wind_gust","logWind_gust",0);
                if ($this->ReadPropertyBoolean("logRain_now") === true)
                    $this-> VarLogging("Rain_now","logRain_now",1);
                if ($this->ReadPropertyBoolean("logRain_today") === true)
                    $this-> VarLogging("Rain_today","logRain_today",1);
                if ($this->ReadPropertyBoolean("logSolar_now") === true)
                    $this-> VarLogging("Solar_now","logSolar_now",1);
                if ($this->ReadPropertyBoolean("logVis_now") === true)
                    $this-> VarLogging("Vis_now","logVis_now",0);
                if ($this->ReadPropertyBoolean("logUV_now") === true)
                    $this-> VarLogging("UV_now","logUV_now",0);
                //Instanz ist aktiv
				$this->SetStatus(102);

				}
			else
				{
				//Instanz ist inaktiv
				$this->SetStatus(104);
				}

   	}

   public function Update()
		{

$locationID =  $this->ReadPropertyString("Wetterstation");  // Location ID
$APIkey = $this->ReadPropertyString("API_Key");  // API Key Wunderground

//Wetterdaten vom aktuellen Wetter
$WetterJetzt = Sys_GetURLContent("http://api.wunderground.com/api/".$APIkey."/conditions/q/CA/".$locationID.".json");
$jsonNow = json_decode($WetterJetzt);

$aktuell = "current_observation"; // Aktuelle Wetter daten holen

$Temp_now = $jsonNow->$aktuell->temp_c;
$Temp_feel = $jsonNow->$aktuell->feelslike_c;
$Temp_dewpoint = $jsonNow->$aktuell->dewpoint_c;
$Hum_now = $jsonNow->$aktuell->relative_humidity;
$Pres_now = $jsonNow->$aktuell->pressure_mb;
$Wind_deg = $jsonNow->$aktuell->wind_degrees;
$Wind_now = $jsonNow->$aktuell->wind_kph;
$Wind_gust = $jsonNow->$aktuell->wind_gust_kph;
$Rain_now = $jsonNow->$aktuell->precip_1hr_metric;
$Rain_today = $jsonNow->$aktuell->precip_today_metric;
$Solar_now = $jsonNow->$aktuell->solarradiation;
$Vis_now = $jsonNow->$aktuell->visibility_km;
$UV_now = $jsonNow->$aktuell->UV;



$contentNextD = Sys_GetURLContent("http://api.wunderground.com/api/".$APIkey."/forecast/q/".$locationID.".json");
$jsonNextD = json_decode($contentNextD);

							SetValue($this->GetIDForIdent("Temp_now"),$Temp_now);
							SetValue($this->GetIDForIdent("Temp_feel"), $Temp_feel);
							SetValue($this->GetIDForIdent("Temp_dewpoint"), $emp_dewpoint);
							SetValue($this->GetIDForIdent("Hum_now"), $Hum_now);
							SetValue($this->GetIDForIdent("Pres_now"), $Pres_now);
							SetValue($this->GetIDForIdent("Wind_deg"), $Wind_deg);
							SetValue($this->GetIDForIdent("Wind_gust"), $Wind_gust);
							SetValue($this->GetIDForIdent("Rain_now "), $Rain_now );
							SetValue($this->GetIDForIdent("Rain_today"), $Rain_today);
							SetValue($this->GetIDForIdent("Solar_now"), $Solar_now);
							SetValue($this->GetIDForIdent("Vis_now"), $Vis_now);
                            SetValue($this->GetIDForIdent("UV_now"), $UV_now);


}

protected function Var_Pro_Erstellen($name,$ProfileType,$Suffix,$MinValue,$MaxValue,$StepSize,$Digits,$Icon)
{

	if (IPS_VariableProfileExists($name) == false)
	{
    	IPS_CreateVariableProfile($name, $ProfileType);
    	IPS_SetVariableProfileText($name, "", $Suffix);
    	IPS_SetVariableProfileValues($name, $MinValue, $MaxValue,$StepSize);
    	IPS_SetVariableProfileDigits($name, $Digits);
    	IPS_SetVariableProfileIcon($name,$Icon);
	}
}
protected function Var_Pro_WD_WindSpeedKmh()
{
	if (IPS_VariableProfileExists("WD_WindSpeed_kmh") == false)

	{
        IPS_CreateVariableProfile("WD_WindSpeed_kmh", 2);
        IPS_SetVariableProfileText("WD_WindSpeed_kmh", "", "km/h");
        IPS_SetVariableProfileValues("WD_WindSpeed_kmh", 0, 200, 0);
        IPS_SetVariableProfileDigits("WD_WindSpeed_kmh", 1);
        IPS_SetVariableProfileIcon("WD_WindSpeed_kmh", "WindSpeed");
        IPS_SetVariableProfileAssociation("WD_WindSpeed_kmh", 0, "%.1f", "WindSpeed", 16776960);
        IPS_SetVariableProfileAssociation("WD_WindSpeed_kmh", 2, "%.1f", "WindSpeed", 6736947);
        IPS_SetVariableProfileAssociation("WD_WindSpeed_kmh", 4, "%.1f", "WindSpeed", 16737894);
        IPS_SetVariableProfileAssociation("WD_WindSpeed_kmh", 6, "%.1f", "WindSpeed", 3381504);
        IPS_SetVariableProfileAssociation("WD_WindSpeed_kmh", 10, "%.1f", "WindSpeed", 52428);
        IPS_SetVariableProfileAssociation("WD_WindSpeed_kmh", 20, "%.1f", "WindSpeed", 16724940);
        IPS_SetVariableProfileAssociation("WD_WindSpeed_kmh", 36, "%.1f", "WindSpeed", 16764159);
	}
}
protected function Var_Pro_WD_UVIndex()
{
	if (IPS_VariableProfileExists("WD_UV_Index") == false)
	{
        IPS_CreateVariableProfile("WD_UV_Index", 1);
        IPS_SetVariableProfileValues("WD_UV_Index", 0, 12, 0);
        IPS_SetVariableProfileAssociation("WD_UV_Index", 0, "%.1f","",0xC0FFA0);
        IPS_SetVariableProfileAssociation("WD_UV_Index", 3, "%.1f","",0xF8F040);
        IPS_SetVariableProfileAssociation("WD_UV_Index", 6, "%.1f","",0xF87820);
        IPS_SetVariableProfileAssociation("WD_UV_Index", 8, "%.1f","",0xD80020);
        IPS_SetVariableProfileAssociation("WD_UV_Index", 11, "%.1f","",0xA80080);
	}
}

protected function VarLogging($VarName,$LogStatus,$Type)
{
    $archiveHandlerID = IPS_GetInstanceListByModuleID('{43192F0B-135B-4CE7-A0A7-1475603F3060}')[0];
    AC_SetAggregationType($archiveHandlerID, $this->GetIDForIdent($VarName), $Type);
    AC_SetLoggingStatus($archiveHandlerID, $this->GetIDForIdent($VarName), $this->ReadPropertyBoolean($LogStatus));
    IPS_ApplyChanges($archiveHandlerID);
}

}
?>