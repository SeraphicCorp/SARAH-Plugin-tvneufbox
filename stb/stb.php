<?php
////////////////////////////////////////////////////////
// Script de pilotage du STB Evolution de SFR
//
// Auteur: M.JEANNE
// Version: 1.0.15
////////////////////////////////////////////////////////

// constantes 
// La seule qu'il soit utile de modifier:
const STB_IP="boxtv";

// le STB utilise 2 ports: 50000 de son coté pour recevoir les commande, 50001 coté appli pour y envoyer des infos
const Port_STB_distant = 50000;
const Port_STB_local = 50001;

////////////////////////////////////////////////////////
// définition des touches 
const T_RETOUR = 12;
const T_1 = 19;
const T_2 = 20;
const T_3 = 21;
const T_4 = 22;
const T_5 = 23;
const T_6 = 24;
const T_7 = 25;
const T_8 = 26;
const T_9 = 27;
const T_0 = 28;
const T_HAUT = 7;
const T_UP = 7;
const T_BAS = 11;
const T_DN = 11;
const T_DROITE = 10;
const T_RT = 10;
const T_GAUCHE = 8;
const T_LT = 8;
const T_REC = 17;
const T_FORWARD = 15;
const T_PLAY = 14;
const T_PAUSE = 14;
const T_REWIND = 13;
const T_OK = 9;
const T_SFR = 6;
const T_ON = 5;
const T_OFF = 5;
const T_VPLUS = 18;
const T_VMOINS = 16;

$tableauTouchesNum= array(0=>28,1=>19,2=>20,3=>21,4=>22,5=>23,6=>24,7=>25,8=>26,9=>27);

////////////////////////////////////////////////////////
// définition des univers
const U_VEILLE=0; // stb en veille, pas d'univers
const U_MENU=1;  // menu SFR
const U_SFR=2; // univers SFR
const U_CANAL=3;  // univers canal+/canalsat
const U_OPTIONS=4; // menu options
const U_MOSAIQUE=5; // mosaique
const U_GUIDE=6; // guide TV
const U_REPLAY=7;
const U_ACTU=8;
const U_VOD=9;
const U_JEUX=10;
const U_RECORDS=11;
const U_MEDIACENTER=12;
const U_REGLAGES=13;
const U_REPLAY=14;
const U_RECHERCHE=15;
const U_HELP=16;

////////////////////////////////////////////////////////
//  definition appui ou relache d'une touche
const PRESS=1;
const RELACHE=0;

////////////////////////////////////////////////////////
// fonction d'envoi d'un code touche unique
// params:
//	$touche: code de la touche à simuler
//	$depress: simuler le relachement de la touche (comportement par defaut)
//	$linefeed: terminaison de ligne par defaut
// 	$IP: IP du STB
function sendTouche($touche, $depress=true, $linefeed=true, $IP=STB_IP)  {

	if ($linefeed==true) $terminaison="\n"; else $terminaison=chr(0);
	
	// ouverture des deux canaux de communication. ça ne marche pas si un seul uniquement est ouvert
	$socketSend = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die("Err: creation socket Envoi\n");
	$socketRecv = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die("Err: creation socket Reception\n");
	$result = socket_connect($socketSend, $IP, Port_STB_distant) or die("Err: connexion au socket Envoi\n");
	$result = socket_connect($socketRecv, $IP, Port_STB_local) or die("Err: connexion au connect Reception\n");

	// envoi du code de la touche + press (ou relache)
	$valeur=$touche." 1".$terminaison;
	socket_write($socketSend, $valeur, strlen ($valeur)) or die("Impossible de communiquer avec le STB\n");
	
	// le STB renvoi la commande en echo. // WAITALL devrait permettre d'attendre l'accusé reception
	$retour="";
	socket_recv($socketSend, $retour, strlen($valeur), MSG_WAITALL) or die("Err: lecture depuis le STB"); 

	
	if ($depress==true) { // on simule la libération de la touche
		$valeur=$touche."  0".$terminaison;
		socket_write($socketSend, $valeur, strlen ($valeur)) or die("Impossible de communiquer avec le STB (depress)\n");
		$retour="";
		socket_recv($socketSend, $retour, strlen($valeur), MSG_WAITALL) or die("Err: lecture depuis le STB"); 		
	}		

	// fin de la communication
	socket_close($socketSend);
	socket_close($socketRecv);

} // sendkey

////////////////////////////////////////////////////////
// fonction d'envoi d'un numéro de chaine
// converti un nombre de 1 à 3 chiffres en suite de touches
// puis appel la fonction d'envoi de touche
// params:
//	$chaine: numéro de la chaine en format texte
//	$debug: permet quelques affichages
function sendChaine($chaine="", $debug=false) {
global $tableauTouchesNum;
	if ($debug) echo "chaine=".$chaine."\r\n";
	
	if ($chaine=="") exit(0);
	for ($ii=0; $ii<strlen($chaine); $ii++) {
		if ($debug) echo "<br>touche=".$chaine[$ii]."\r\n";
		sendTouche($tableauTouchesNum[$chaine[$ii]]);
	}
	
}

////////////////////////////////////////////////////////
// fonction de lecture d'infos depuis le STB
// params: IP du STB
// le retour est une chaine de type:
//	ST1,X,Y,FW, Univers, Chaine
// 	ST1: modèle de STB (à confirmer)
//	X et Y: inconnus
//	FW: version du firmware
//	Univers: univers en cours
//	Chaine: la chaine en cours DANS l'UNIVERS SFR sous la forme "num-nom". Ne fonctionne pas dans l'univers canal+
function STBQuery($IP=STB_IP) {
	$socketSend = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die("Err: creation socket Envoi\n");
	$socketRecv = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die("Err: creation socket Reception\n");
	$result = socket_connect($socketSend, $IP, Port_STB_distant) or die("Err: connexion au socket Envoi\n");
	$result = socket_connect($socketRecv, $IP, Port_STB_local) or die("Err: connexion au connect Reception\n");
	
	// envoi d'une commande type "hello" au STB afin de forcer une réponse
	$valeur="0  0\n";
	socket_write($socketSend, $valeur, strlen ($valeur)) or die("Impossible de communiquer avec le STB\n");
	$retour="";
	socket_recv($socketSend, $retour, strlen($valeur), MSG_WAITALL) or die("Err: lecture depuis le STB"); 	
	
	// lecture de la réponse sur le socket 2
	$retour="";
	socket_recv($socketRecv, $retour, 60, MSG_MSG_WAITALL|MSG_DONTWAIT) or die("Err: lecture depuis le STB"); 	
	
	// fin de la communication
	socket_close($socketSend);
	socket_close($socketRecv);
	echo $retour;
	return $retour;
}


?>