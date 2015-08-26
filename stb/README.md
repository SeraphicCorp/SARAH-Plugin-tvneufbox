
[TUTO] Pilotage décodeur Evolution SFR

Messagede Insedo » 26 Juil 2012, 13:34
Bonjour,

Je vous présente un petit script php de mon cru pour piloter son STB Evolution via des appels http...
C'est pas vraiment un tuto dédié Zibase, du coup je ne me mets pas en section tutoriels, mais mon application directe se faisant via la petite box, ce forum me semble adapté. De plus le script est conçu de base pour le format zibase (retour d'infos en xml)

J'utilisais ce script depuis des mois en privé, mais à la demande de plus en plus pressante des mes proches, je le diffuse.

Les pré-requis:

    - disposer d'un serveur web sur son réseau privé. Je n'ai pas testé depuis un serveur externe et des règles de NAT sur la box adsl.. ça pourrait fonctionner. Chez moi, c'est un synology DS211+. Je pense qu'il faut au moins PHP 5, au vu de certaines fonctions utilisées.

    - disposer d'une box TV SFR Evolution (le modèle qui ressemble à un magneto des années 80)

    - connaitre l'IP de son décodeur (disponible depuis l'interface de la box adsl)

    - disposer de mes scripts PHP :) .


Etape 1: Installation
téléchargez l'archive de mes scripts (3ko):
format 7z: http://www.insedo.fr/media/stb.7z
format gz: http://www.insedo.fr/media/stb.tar.gz

Vous décompressez cette archive. Normalement vous obtenez un répertoire "stb" contenant un fichier "index.php" et un sous-répertoire qui lui-même contient le fichier "stb.php".
Déplacez ce répertoire sur votre serveur web. Sur un synology, il suffit de glisser-déposer le dossier "stb" dans le répertoire "web" (visible dans le "voisinage réseau" sous windows).

Etape2: Configuration
Dans le sous-répertoire "include", ouvrez le fichier stb.php avec un éditeur de texte, et changez l'IP du décodeur pour la votre. Sauvez le ficher et refermez.

Etape 3: y a pas, c'est prêt :lol:


pour piloter le décodeur, il suffit d’appeler l'url correspondant à votre serveur web.
Mon script utlise 2 paramètres: "chaine" et "cmd"
"chaine" sert à changer de chaine. C'est une valeur numérique.
Par exemple, si l'IP de votre serveur web est "192.168.1.252", pour "Gully", appelez l'url "http://192.168.1.252/stb/index.php?chaine=23"
Bien sur, cela nécessite que le décodeur soit allumé et sorti de veille.
Pour gérer l'allumage, mon script ne peut rien faire, mais pour la sortie de veille, c'est le but du paramètre "cmd".

"cmd" est une liste de commandes à envoyer au décodeur. Et la liste de fonction est longue. Chaque commande doit être séparée par une virgule, sans espace.
Si vous utilisez à la fois "chaine" et "cmd", les commandes sont éxécutées AVANT le changement de chaine.
Les commandes disponnibles sont les suivantes:
on, off : permet de sortir ou d'entrer en mode veille
sfr: touche SFR
up, haut, dn, bas, droite, rt, gauche, lt : simulation des touches de direction
ok : bouton ok
vp, vm : volume + ou -
play, pause, rec : les touches magnétoscope (play et pause sont 1 seule et même touche, il y a 2 commandes juste pour le fun)
ff, rw: avance et retour
ret, retour: le bouton retour.
query: c'est une fonction spéciale, pas une touche. Voir plus bas.
1,2,3,4,5: ce ne sont pas des touches, mais des temporisations de 1 à 5 secondes.

Un exemple, pour aller sur syfy dans l'unvivers Canalsat:
http://192.168.1.252/stb/index.php?chaine=23&cmd=sfr,3,up,1,lt,1,ok,5,5
explication: touche SFR pour sortir de l'endroit ou on se trouve et arriver sur le menu, on temporise 2 secondes, car le STB est parfois lent, ensuite haut puis gauche (chacune temporisé de 1 seconde) dans le caroussel, on valide par ok, on patiente 10 secondes car l'univers CSat est parfois long à se lancer, ensuite on zappe sur la chaine 23.

Arrivons maintenant aux problèmes: le décodeur s'allume et se met en veille avec la même touche (même si j'ai donné 2 noms différents aux commandes). Donc si on envoi "cmd=on" et qu'il est déjà allumé, le STB va s'éteindre.
Heureusement, il est possible d'interroger le STB par la commande "query". Cette commande renvoi un ficher xml qui indique l'univers affiché et la chaine (de l'univers SFR, ça ne marche pas dans les autres univers). Dans le fichier XML, il faut recherche les balises "c" et "u" (pour chaine et univers) pour un usage informatique, "unvivers" et "chaine" pour un format texte.
exemple: http://192.168.1.252/stb/?cmd=query
retour:

Code: Tout sélectionner
    <xml>
    <univers> </univers>
    <chaine>TMC</chaine>
    <u>0>/u>
    <c>10</c>
    </xml>

Malgré qu'il y ai une info sur la chaine, "univers" contient un espace et u=0 => le STB n'est sur aucun univers, il est en veille.
En sortie de veille, c'est TMC qui sera affiché.
Vous aurez noté que je n'ai pas utilisé "index.php" dans cet exemple. Sur mon NAS c'est optionnel et à une époque où la mémoire était limitée sur le zibase, c'était utile... C'est aussi la raison des "c" et "u" du fichiers xml...
Liste des univers connus de mon script:

Code: Tout sélectionner
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



Intégration avec la Zibase:
Voici comment j'ai interfacé la Zibase et le décodeur.
J'utilise 2 scénarios pour éteindre le décodeur depuis mon smartphone ou depuis d'autres scripts (gestion du meuble TV)
1er script, celui qui pilote l’arrêt du STB, nommé "arret STB #2":
N'est déclenché par rien
contient 1 action: commande par http: http://192.168.1.252/stb/?cmd=off

2ème script, qui gère intelligemment l'arrêt du STB (il vérifie qu'il ne l'est pas déjà), nommé "arret STB"
action 1: mettre "u" dans une variable, par exemple v0
action 2: commande par http (http://192.168.1.252/stb/?cmd=query) avec lecture d'une valeur de retour dans v0
action 3: lancer un scénario selon une condition calculée. Valeur v0. Si supérieur à 0 (on est dans un univers, donc stb allumé), lancer "arret STB #2".


Voilà pour le moment
Je vais améliorer les scripts d'ici peu (d'autres besoin arrivent). Parmi les ajouts prévus:
- détection du ou des décodeurs en automatique
- gestion on/off intelligente depuis le script et non plus depuis la zibase.
- trouver comment appeler une chaine depuis son nom au lieu de son numéro.


En cas de dysfonctionnement, merci de vérifier:
- que vous avez la bonne IP dans le fichier stb.php
- que vous avez gardé la structure des répertoires (stb/include)
- que vous avez le bon modèle de décodeur (SFR Evolution uniquement ).

Pensez à documenter votre erreur: avez vous testé avec un navigateur? lequel ? Quel message apparaît ? Version de php ?, etc..
