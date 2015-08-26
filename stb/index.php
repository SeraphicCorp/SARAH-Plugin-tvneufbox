<?php
////////////////////////////////////////////////////////
// Script de pilotage du STB Evolution de SFR
// 
// Auteur: M.JEANNE
// Version: 1.0.15
////////////////////////////////////////////////////////

include_once("./stb.php");
// reccup numero de chaine
if (isset($_GET["chaine"])) $numero=$_GET["chaine"]; else $numero="";
if (isset($_GET["cmd"])) $commande=$_GET["cmd"]; else $cmd="";

if ($commande!="") {
	$commande=strtolower($commande);
	$liste=explode(",", $commande);
	for ($ii=0; $ii<count($liste); $ii++) {
		switch($liste[$ii]) {
		case "on":
		case "off": sendTouche(T_ON, false); break;
		case "sfr": sendTouche(T_SFR); break;
		case "up": 
		case "haut": sendTouche(T_UP); break;
		case "dn": 
		case "bas": sendTouche(T_DN); break;
		case "lt": 
		case "gauche": sendTouche(T_LT); break;
		case "rt": 
		case "droite": sendTouche(T_RT); break;
		case "ok": sendTouche(T_OK); break;
		case "vm": sendTouche(T_VMOINS); break;		
		case "vp": sendTouche(T_VPLUS); break;
		case "play": 
		case "pause": sendTouche(T_PLAY); break;
		case "rec": sendTouche(T_REC); break;
		case "rw": sendTouche(T_REWIND, false); break;
		case "ff": sendTouche(T_FORWARD, false); break;
		case "ret": 
		case "retour": sendTouche(T_RETOUR); break;
		
		case "query": 
			$retour=STBQuery(); 
			if ($retour!="") $liste=explode(",", $retour); else $liste=array("STB","?","?","FW","univers","nn-chaine");
			switch($liste[4]) {
				case " ": 				$univers=U_VEILLE; break; // stb en veille
				case "SFR Evolution":    	$univers=U_MENU; break;  // menu SFR
				case "Télévision":         	$univers=U_SFR; break; // univers SFR
				case "Options TV":        	$univers=U_OPTIONS; break; // menu options
				case "Mosaique TV":      	$univers=U_MOSAIQUE; break; // mosaique
				case "Guide TV":           	$univers=U_GUIDE; break; // guide TV
				case "TV à la demande": 	$univers=U_REPLAY; break;
				case "Réglages":            	$univers=U_REGLAGES; break;
				case "Jeux à la Demande": 	$univers=U_JEUX; break;
				case "Club Video": 		$univers=U_VOD; break;
				case "Applications": 		$univers=U_APPLIS; break;
				case "Canal+/Canalsat": 	$univers=U_CANAL; break;
				case "Actu neufbox TV": 	$univers=U_ACTU; break;
				case "Enregistrements": 	$univers=U_RECORDS; break;
				case "Media Center": 		$univers=U_MEDIACENTER; break;
				case "Recherche": 		$univers=U_RECHERCHE; break;
				case "Mode d'Emploi": 		$univers=U_HELP; break;
			}
			$liste2=explode("-", $liste[5]);
			
			echo "<xml>\r\n<univers>".$liste[4]."</univers>\r\n<chaine>".$liste2[1]."</chaine>\r\n<u>".$univers."</u>\r\n<n>".$liste2[0]."</n>\r\n</xml>\r\n";
			echo ":".$retour;
			break;		
		
		
		case 1: sleep(1); break;
		case 2: sleep(2); break;
		case 3: sleep(3); break;
		case 4: sleep(4); break;
		case 5: sleep(5); break;
		
		}
	}
}

if ($numero!="") sendChaine($numero);



?>