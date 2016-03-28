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
                $this->RegisterPropertyInteger("UpdateInterval", 10);
  
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

                if (($this->ReadPropertyString("API_Key") != "") AND ($this->ReadPropertyString("Wetterstation") != "")){
                    //Variablen erstellen Wetter jetzt
                    $this->RegisterVariableFloat("Temp_now","Temperatur","Temperature",1);
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
                    //Variablen erstellen Wettervorhersage
                    $this->RegisterVariableFloat("Temp_high_heute","Temperatur/Tag heute","Temperature",14);
                    $this->RegisterVariableFloat("Temp_low_heute","Temperatur/Nacht heute","Temperature",15);
                    $this->RegisterVariableFloat("Rain_heute","Niederschlag/h heute","WD_Niederschlag",16);
                    $this->RegisterVariableFloat("Temp_high_morgen","Temperatur Tag morgen","Temperature",17);
                    $this->RegisterVariableFloat("Temp_low_morgen","Temperatur Nacht morgen","Temperature",18);
                    $this->RegisterVariableFloat("Rain_morgen","Niederschlag/h morgen","WD_Niederschlag",19);
                    $this->RegisterVariableString("Wettervorhersage_html","Wettervorhersage","HTMLBox",20);
                    //Timer zeit setzen
                    $this->SetTimerMinutes($this->InstanceID,"Update",$this->ReadPropertyInteger("UpdateInterval"));
                    //Instanz ist aktiv
                    $this->SetStatus(102);
                }
                else {
                    //Instanz ist inaktiv
                   $this->SetStatus(104); 
                }
                
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
            }

        public function Update()
            {
                $locationID =  $this->ReadPropertyString("Wetterstation");  // Location ID
                $APIkey = $this->ReadPropertyString("API_Key");  // API Key Wunderground

                //Wetterdaten vom aktuellen Wetter
                $WetterJetzt = Sys_GetURLContent("http://api.wunderground.com/api/".$APIkey."/conditions/q/CA/".$locationID.".json"); //Seite öffnen
                $jsonNow = json_decode($WetterJetzt); //json in String speichern
                //Wetterdaten für die nächsten  Tage
                $contentNextD = Sys_GetURLContent("http://api.wunderground.com/api/".$APIkey."/forecast/q/".$locationID.".json"); //Seite öffnen
                $jsonNextD = json_decode($contentNextD);
                //Wetterdaten in Variable speichern
                $Temp_now = $jsonNow->current_observation->temp_c;
                $Temp_feel = $jsonNow->current_observation->feelslike_c;
                $Temp_dewpoint = $jsonNow->current_observation->dewpoint_c;
                $Hum_now = $jsonNow->current_observation->relative_humidity;
                $Pres_now = $jsonNow->current_observation->pressure_mb;
                $Wind_deg = $jsonNow->current_observation->wind_degrees;
                $Wind_now = $jsonNow->current_observation->wind_kph;
                $Wind_gust = $jsonNow->current_observation->wind_gust_kph;
                $Rain_now = $jsonNow->current_observation->precip_1hr_metric;
                $Rain_today = $jsonNow->current_observation->precip_today_metric;
                $Solar_now = $jsonNow->current_observation->solarradiation;
                $Vis_now = $jsonNow->current_observation->visibility_km;
                $UV_now = $jsonNow->current_observation->UV;

                //Wetterdaten für diw nächsten 2 Tage in Variable speichern
                $Temp_high_heute = $jsonNextD->forecast->simpleforecast->forecastday[0]->high->celsius;
                $Temp_low_heute = $jsonNextD->forecast->simpleforecast->forecastday[0]->low->celsius;
                $Rain_heute = $jsonNextD->forecast->simpleforecast->forecastday[0]->qpf_allday->mm;
                $Temp_high_morgen = $jsonNextD->forecast->simpleforecast->forecastday[1]->high->celsius;
                $Temp_low_morgen = $jsonNextD->forecast->simpleforecast->forecastday[1]->low->celsius;
                $Rain_morgen = $jsonNextD->forecast->simpleforecast->forecastday[1]->qpf_allday->mm;

                  $this->SetValueByID($this->GetIDForIdent("Temp_now"),$jsonNow->current_observation->temp_c);
                  $this->SetValueByID($this->GetIDForIdent("Temp_feel"), $jsonNow->current_observation->feelslike_c);
                  $this->SetValueByID($this->GetIDForIdent("Temp_dewpoint"), $jsonNow->current_observation->dewpoint_c);
                  $this->SetValueByID($this->GetIDForIdent("Hum_now"), substr($jsonNow->current_observation->relative_humidity, 0, -1));
                  $this->SetValueByID($this->GetIDForIdent("Pres_now"), $jsonNow->current_observation->pressure_mb);
                  $this->SetValueByID($this->GetIDForIdent("Wind_deg"), $jsonNow->current_observation->wind_degrees;
                  $this->SetValueByID($this->GetIDForIdent("Wind_now"), $jsonNow->current_observation->wind_kph);
                  $this->SetValueByID($this->GetIDForIdent("Wind_gust"), $jsonNow->current_observation->wind_gust_kph;);
                  $this->SetValueByID($this->GetIDForIdent("Rain_now"), $jsonNow->current_observation->precip_1hr_metric);
                  $this->SetValueByID($this->GetIDForIdent("Rain_today"), $jsonNow->current_observation->precip_today_metric);
                  $this->SetValueByID($this->GetIDForIdent("Solar_now"), $jsonNow->current_observation->solarradiation);
                  $this->SetValueByID($this->GetIDForIdent("Vis_now"), $jsonNow->current_observation->visibility_km);
                  $this->SetValueByID($this->GetIDForIdent("UV_now"), $jsonNow->current_observation->UV);
                  $this->SetValueByID($this->GetIDForIdent("Temp_high_heute"), $jsonNextD->forecast->simpleforecast->forecastday[0]->high->celsius);
                  $this->SetValueByID($this->GetIDForIdent("Temp_low_heute"), $jsonNextD->forecast->simpleforecast->forecastday[0]->low->celsius);
                  $this->SetValueByID($this->GetIDForIdent("Rain_heute"), $jsonNextD->forecast->simpleforecast->forecastday[0]->qpf_allday->mm);
                  $this->SetValueByID($this->GetIDForIdent("Temp_high_morgen"), $jsonNextD->forecast->simpleforecast->forecastday[1]->high->celsius);
                  $this->SetValueByID($this->GetIDForIdent("Temp_low_morgen"), $jsonNextD->forecast->simpleforecast->forecastday[1]->low->celsius);
                  $this->SetValueByID($this->GetIDForIdent("Rain_morgen"), $jsonNextD->forecast->simpleforecast->forecastday[1]->qpf_allday->mm);
                  SetValue($this->GetIDForIdent("Wettervorhersage_html"), $this->String_Wetter_Now_And_Next_Days($jsonNow ,$jsonNextD) );

            }

       public function String_Wetter_Now_And_Next_Days($WetterJetzt, $WetterNextDays)
            {           
                $html = '<table >
                            <tr>
                                <td align="center" valign="top"  style="width:130px;padding-left:20px;">
                                    Aktuell<br>
                                    <img src="'.$WetterJetzt->current_observation->icon_url.'" style="float:left;">
                                    <div style="float:right">
                                         '.$WetterJetzt->current_observation->temp_c.' °C<br>
                                        '.$WetterJetzt->current_observation->relative_humidity.'<br>
                                     </div>
                                    <div style="clear:both; font-size: 10px;">
                                        Ø Wind: '.$WetterJetzt->current_observation->wind_kph.' km/h<br>
                                        '.$WetterJetzt->current_observation->feelslike_c.' °C gefühlt<br>
                                        '.$WetterJetzt->current_observation->pressure_mb.' hPa<br>
                                        Regen 1h: '.$WetterJetzt->current_observation->precip_1hr_metric.' Liter/m²<br>
                                        Sichtweite '.$WetterJetzt->current_observation->visibility_km.' km
                                     </div>
                                 </td>';
                foreach($WetterNextDays->forecast->simpleforecast->forecastday as $name=> $day){
                    if( $this->isToday($day->date->epoch))
                        $Wochentag = "Heute";
                    else {
                        $tag = array("Sonntag","Montag","Dienstag","Mittwoch","Donnerstag","Freitag","Samstag");
                        $Wochentag =$tag[date("w",intval($day->date->epoch))];
                         }
                     $html.= '<td align="center" valign="top"  style="width:130px;padding-left:20px;">
                                '.$Wochentag.'<br>
                                <img src="'.$day->icon_url.'" style="float:left;">
                                <div style="float:right">
                                    '.$day->low->celsius.' °C<br>
                                    '.$day->high->celsius.' °C
                                 </div>
                                 <div style="clear:both; font-size: 10px;"> 
                                    Ø Wind: '.$day->avewind->kph.' km/h<br>
                                    Niederschlag: '.($day->qpf_allday->mm).' Liter/m²
                                  </div>
                               </td>';
                       }
                $html .= "</tr>
                           </table>";
                return $html;
            }

        protected function Var_Pro_Erstellen($name,$ProfileType,$Suffix,$MinValue,$MaxValue,$StepSize,$Digits,$Icon)
            {
                if (IPS_VariableProfileExists($name) == false){
                    IPS_CreateVariableProfile($name, $ProfileType);
                    IPS_SetVariableProfileText($name, "", $Suffix);
                    IPS_SetVariableProfileValues($name, $MinValue, $MaxValue,$StepSize);
                    IPS_SetVariableProfileDigits($name, $Digits);
                    IPS_SetVariableProfileIcon($name,$Icon);
                 }
            }
        protected function Var_Pro_WD_WindSpeedKmh()
            {
                if (IPS_VariableProfileExists("WD_WindSpeed_kmh") == false){
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
                if (IPS_VariableProfileExists("WD_UV_Index") == false){
                    IPS_CreateVariableProfile("WD_UV_Index", 1);
                    IPS_SetVariableProfileValues("WD_UV_Index", 0, 12, 0);
                    IPS_SetVariableProfileAssociation("WD_UV_Index", 0, "%.1f","",0xC0FFA0);
                    IPS_SetVariableProfileAssociation("WD_UV_Index", 3, "%.1f","",0xF8F040);
                    IPS_SetVariableProfileAssociation("WD_UV_Index", 6, "%.1f","",0xF87820);
                    IPS_SetVariableProfileAssociation("WD_UV_Index", 8, "%.1f","",0xD80020);
                    IPS_SetVariableProfileAssociation("WD_UV_Index", 11, "%.1f","",0xA80080);
                 }          
            }

        private function VarLogging($VarName,$LogStatus,$Type)
            {
                $archiveHandlerID = IPS_GetInstanceListByModuleID('{43192F0B-135B-4CE7-A0A7-1475603F3060}')[0];
                AC_SetAggregationType($archiveHandlerID, $this->GetIDForIdent($VarName), $Type);
                AC_SetLoggingStatus($archiveHandlerID, $this->GetIDForIdent($VarName), $this->ReadPropertyBoolean($LogStatus));
                IPS_ApplyChanges($archiveHandlerID);
            }
        //Timer erstllen alle X minuten 
        private function SetTimerMinutes($parentID, $name,$minutes)
            {
                $eid = @IPS_GetEventIDByName($name, $parentID);
                if($eid === false){
                    $eid = IPS_CreateEvent(1);
                    IPS_SetParent($eid, $parentID);
                    IPS_SetName($eid, $name);
                 }
                else{
                    IPS_SetEventCyclic($eid, 0 /* Keine Datumsüberprüfung */, 0, 0, 2, 2 /* Minütlich */ , $minutes/* Alle XX Minuten */);
                    IPS_SetEventScript($eid, 'WD_Update($_IPS["TARGET"]);');
                    IPS_SetEventActive($eid, true);
                    IPS_SetHidden($eid, true);
                 }
            }
    
        private function isToday($time)
            {
                $begin = mktime(0, 0, 0);
                $end = mktime(23, 59, 59);
                // check if given time is between begin and end
                if($time >= $begin && $time <= $end)
                    return true;
                else 
                    return false;
            }

        private function SetValueByID($VariablenID,$Wert)
            {
                // Überprüfen ob $Wert eine Zahl ist
                if (is_numeric($Wert))
                    SetValue($VariablenID,$Wert);
                //Wenn $Wert keine Zahl ist setze den Wert auf 0
                else 
                SetValue($VariablenID,0);
            }


     }
?>