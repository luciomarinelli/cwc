<?php
//Cookieless Web Counter by Lucio Marinelli
//Please see attached GNU GENERAL PUBLIC LICENSE version 3

//This script uses geoPlugin free service for IP geolocation, please read Acceptable Use Policy https://www.geoplugin.com/aup
//REMEMBER that if you send more than 120 lookups per minute your IP will be banned for 1 hour
//if you send more than 1500 requests per minute will get you blocked PERMANENTLY 


require ("config.inc.php"); //include configuration file


//Function to detect bots
function is_bot($text) {
    $botkey=array("bot","spider","slurp","search","crawl","favicon");
    foreach ($botkey as $letter) {
        if (stripos($text,$letter) !== false) {
            return true;
        }
    }
    return false;
}


//function to replace file_get_contents() function so it works if allow_url_fopen=0 in php.ini (much slower but works)
function curl_get_file_contents($URL)
    {
        $c = curl_init();
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_URL, $URL);
        $contents = curl_exec($c);
        curl_close($c);

        if ($contents) return $contents;
        else return FALSE;
    }

//reset ban_flag
$ban_flag=0;

//get site id for <TITLE> & dump page, preventing injection
if ($_GET[action]=="dump" && is_numeric($_GET[id])) $siteid=$_GET[id];

?>


<html>
<head>
<title>Cookieless Web Counter - <?=$sitename[$siteid] ?></title>
</head>

<body>
<h1>Cookieless Web Counter <span style="font-size: 10px; font-style: italic"><a href="https://www.luciomarinelli.com" target="_external" style="text-decoration: none; color: black">by Lucio Marinelli</a></span></h1>

<?php

//Detect language from HTTP_ACCEPT_LANGUAGE string
$language=($_SERVER[HTTP_ACCEPT_LANGUAGE]);
$lang=substr($language,0,2);

switch ($lang) {
	case 'it': //ITALIAN LANGUAGE
	//pagina principale
	$site_label="Sito";
	$today_visits="Visite odierne";
	$today_visitors="Visitatori odierni";
	$yesterday_visits="Visite di ieri";
	$yesterday_visitors="Visitatori di ieri";
	$last_visits="Ultime visite";

	//dump page
	$back="Ritorna alla pagina principale";
	$timestamp_label="Data & ora";
	$php_self_label="Pagina visitata";
	$remote_addr_label="Indirizzo IP";
	$city_country_label="Città, Stato";
    $unknown_city="sconosciuta";
	$http_referer_label="Pagina di provenienza";
	$http_user_agent_label="Browser del visitatore";

	//errori
	$mysql_server_error="Errore nella connessione con il server MySQL!";
	$db_connection_error1="Errore nella connessione al database ";
	$db_connection_error2="";
	$attack="Attacco rilevato!";
    $banned="BANNATO!";
	break;

	default: //DEFAULT ENGLISH LANGUAGE
	//main page
	$site_label="Site";
	$today_visits="Today's visits";
	$today_visitors="Today's visitors";
	$yesterday_visits="Yesterday's visits";
	$yesterday_visitors="Yesterday's visitors";
	$last_visits="Last visits";

	//dump page
	$back="Back to the main page";
	$timestamp_label="Timestamp";
	$php_self_label="php_self";
	$remote_addr_label="remote_addr";
	$city_country_label="City, Country";
    $unknown_city="unknown";
	$http_referer_label="http_referer";
	$http_user_agent_label="http_user_agent";

	//errors
	$mysql_server_error="Error connecting to MySQL server!";
	$db_connection_error1="Error connecting to ";
	$db_connection_error2=" database!";
	$attack="Attack detected!";
    $banned="BANNED!";
	break;
	}


//count the number of sites
$number_of_sites=count($sitename)+1;


//dump last visits
if ($_GET[action]=="dump" && $_GET[id]<$number_of_sites) {

    //check if too many requests have been sent to geoPlugin to prevent permanent ban
    if ($ban_flag=0) {
        $ban_test=curl_get_file_contents('http://www.geoplugin.net/php.gp?ip=1.1.1.1');
        if (strpos ($ban_test, '403 Forbidden')) $ban_flag=1;
    }

	//connect to MySQL server
	$conn = mysql_connect($dbhost[$siteid],$dbuser[$siteid],$dbpass[$siteid]) or die ("$mysql_server_error");

	//connect to database
	mysql_select_db($dbname[$siteid],$conn) or die ("$db_connection_error1"."$dbname[$siteid]"."$db_connection_error2");

	//show last 20-50-100 (n) records for the selected site
	$query = ("SELECT * FROM $tablename[$siteid] ORDER BY timestamp DESC");
	$result = mysql_query ($query) or die (mysql_error());

	//get number of visits preventing injection
	if (is_numeric($_GET[n])) $n_vis=$_GET[n]+1;
	else die ("$attack");

	echo "<h2>$sitename[$siteid]</h2>";
	echo "<h3>$last_visits ($_GET[n])</h3><table border=\"1px\" style=\"font-size: 12px\">";
	echo "<div><a href=\"$_SERVER[PHP_SELF]\">$back</a></div>";
	echo "<table>";
	echo "<tr><th>Id</th><th>$timestamp_label</th><th>$php_self_label</th><th>$remote_addr_label</th><th>$city_country_label</th><th>$http_referer_label</th><th>$http_user_agent_label</th></tr>";

	for ($i=1;$i<$n_vis;$i++) {
		$visita = mysql_fetch_assoc($result);
		if (is_bot($visita[http_user_agent])) $stile=" style=\"color: gray\""; //Visits from bots are grayed
        if ($ban_flag=1) {
            $geo_city=$banned;
            $geo_country=$banned;
        } else {
            if (ini_get('allow_url_fopen')) {
                $geolocate=unserialize(file_get_contents('http://www.geoplugin.net/php.gp?ip='.$visita[remote_addr]));
            } else {
                $geolocate=unserialize(curl_get_file_contents('http://www.geoplugin.net/php.gp?ip='.$visita[remote_addr]));
            }
            if ($geolocate['geoplugin_city'] == "") $geo_city=$unknown_city;
            else $geo_city=$geolocate['geoplugin_city'];
            $geo_country=$geolocate['geoplugin_countryName'];
        }
		echo "<tr$stile><td>$visita[id]</td><td>$visita[timestamp]</td><td>$visita[php_self]</td><td>$visita[remote_addr]</td><td>$geo_city, $geo_country</td><td>$visita[http_referer]</td><td>$visita[http_user_agent]</td></tr>";
		$stile="";
		}

	echo "</table>";
	echo "<div><a href=\"$_SERVER[PHP_SELF]\">$back</a></div>";
	}

//show the main page
else {
	echo "<table cellpadding=5>";
	echo "<tr style=\"text-align: left; background-color: #dcfcf9\"><th>$site_label</th><th>$today_visits</th><th>$today_visitors</th><th>$yesterday_visits</th><th>$yesterday_visitors</th><th>$last_visits</th></tr>";

	for ($siteid=1; $siteid<$number_of_sites; $siteid++) {

		//connect to MySQL server
		$conn = mysql_connect($dbhost[$siteid],$dbuser[$siteid],$dbpass[$siteid]) or die("$mysql_server_error");

		//connect to database
		mysql_select_db($dbname[$siteid],$conn) or die("$db_connection_error1"."$dbname[$siteid]"."$db_connection_error2");

		//count today's visits
		$q_visite_odierne=("SELECT timestamp FROM $tablename[$siteid] WHERE DATE(timestamp)=CURDATE()");
		$r_visite_odierne=mysql_query ($q_visite_odierne) or die (mysql_error());
		$visite_odierne=mysql_num_rows ($r_visite_odierne);

		//count today's visitors
		$q_visitatori_odierni=("SELECT remote_addr FROM $tablename[$siteid] WHERE DATE(timestamp)=CURDATE() GROUP BY remote_addr");
		$r_visitatori_odierni=mysql_query ($q_visitatori_odierni) or die (mysql_error());
		$visitatori_odierni=mysql_num_rows ($r_visitatori_odierni);

		//count yesterday's visits
		$q_visite_ieri=("SELECT timestamp FROM $tablename[$siteid] WHERE DATE(timestamp)=CURDATE()- INTERVAL 1 DAY");
		$r_visite_ieri=mysql_query ($q_visite_ieri) or die (mysql_error());
		$visite_ieri=mysql_num_rows ($r_visite_ieri);

		//count yesterday's visitors
		$q_visitatori_ieri=("SELECT remote_addr FROM $tablename[$siteid] WHERE DATE(timestamp)=CURDATE()- INTERVAL 1 DAY GROUP BY remote_addr");
		$r_visitatori_ieri=mysql_query ($q_visitatori_ieri) or die (mysql_error());
		$visitatori_ieri=mysql_num_rows ($r_visitatori_ieri);

		echo "<tr><td>$sitename[$siteid]</td><td>$visite_odierne</td><td>$visitatori_odierni</td><td>$visite_ieri</td><td>$visitatori_ieri</td><td><a href=\"$_SERVER[PHP_SELF]?id=$siteid&amp;action=dump&amp;n=20\">20</a>&nbsp;&nbsp;<a href=\"$_SERVER[PHP_SELF]?id=$siteid&amp;action=dump&amp;n=50\">50</a>&nbsp;&nbsp;<a href=\"$_SERVER[PHP_SELF]?id=$siteid&amp;action=dump&amp;n=100\">100</a></td></tr>";
		}
	echo "</table>";
	}

?>

<div style="font-family: sans serif; font-size: 10px; margin-top: 5em; text-align: right">Version 20200812</div>

</body>
</html>

