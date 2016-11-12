# SERVEUR D'EMPLOIS DU TEMPS #

les fichiers PHP du répertoire principal permettent de servir
un système d'organisation d'emploi du temps. Il faut peupler une
base de données (fichier edt.db) sur le modèle du fichier
edt.db.sample, qui est manœuvré par Sqlite3.

Pour démarrer il faut définir au moins un compte avec des droits
d'administration étendus, puis ...
  * ajouter d'autres utilisateurs avec des droits plus ou moins étendus,
  * ajouter des professeurs
  * ajouter des classes
  * ajouter des noms de salles de classe

Alors, il devient possible d'ajouter des étiquettes mobiles dans un
afficheur, et les placer dans ce tableau, organisé par classe (en colonnes)
et par heure (en lignes), de 8:00  18:00.

Les étiquettes sont facilement mobiles. Les actions suivantes sont
possibles pour gérer les tableaux journaliers :
  * récupérer un tableau pour une date donnée (aujourd'hui, par défaut)
  * enregistrer le tableau courant à la date courante
  * enregistrer le tableau courant à une autre date

## ASTUCES ##

### CRÉER UN COMPTE ADMINISTRATEUR ###

Ça peut se faire en utilisant **sqlitebrowser** pour éditer la base de données
recopiée depuis edt.db.sample. Il faut créer un enregistrement dans la
table **users**. Pour cela, on peut utiliser un mot de passe crypté plutôt
qu'un mot de passe en clair.

Sous Linux, la recette suivante permet d'obtenir un mot de passe crypté à
partir d'un mot de passe en clair : 

    $ echo -n "azerty" | md5sum
	ab4f63f9ac65152575886860dde480a1  -
	
La chaîne *ab4f63f9ac65152575886860dde480a1* est le mot de passe crypté.

### PERMISSIONS CORRECTES POUR QUE LE SERVEUR PUISSE MODIFIER LA BASE DE DONNÉES ###

Pour un système Debian/GNU-Linux, il faut que la base de données appartienne
à l'utilisateur **www-data** et que le répertoire soit inscriptible par ce
même utilisateur. Si l'utilisateur qui a des droits sur le répertoire courant
a aussi des prérogatives de *sudoer*, il peut lancer la commande
**bonsDroits.sh**, qui fait le nécessaire.

# UN AUTOMATE POUR AFFICHER L'EMPLOI DU TEMPS #

Dans le répertoire *afficheur/*, la commande **maintenant.py** est capable
d'exploiter un service d'emploi du temps EDT, et d'afficher en plein écran
les étiquettes sous les salles correspondantes, pour la tranche temporelle
la plus pertinente.

## Exemple d'utilisation : ##

    $ ./maintenant.py --date "2016-11-14 09:30" http://edt.exemple.org
	
Le paramètre *http://edt.exemple.org* est l'URL de base du service de temps ;
une requête sera faite à *http://edt.exemple.org/maintenant.ajax.php* pour
récupérer les étiquettes.

Le paramètre optionnel *--date "2016-11-14 09:30"* permet de simuler le
fonctionnement de l'afficheur plein écran à la date et à l'heure voulue.
En l'absence d'un tel paramètre, c'est la date et l'heure courantes qui
sont utilisées.

On peut en savoir plus sur les options de cette commande en tapant :

    $ ./maintenant.py  --help
	
Voici le texte de l'aide qu'on obtient :

    usage: maintenant.py [-h] [--date DATE] [--proxy PROXY] [--port PORT] url
    
    positional arguments:
      url                   URL du service EDT (exemple :
                            "http://edt.exemple.com")
    
    optional arguments:
      -h, --help            show this help message and exit
      --date DATE, -d DATE  Simule une date (exemple : "2016-11-14 09:30")
      --proxy PROXY, -x PROXY
                            Nom d'hôte du proxy (exemple : "proxy.mondomaine.com")
      --port PORT, -p PORT  Port du proxy (3128 par défaut)
    
    +-------------------------------------------------+
    | Affiche l'emploi du temps pour l'heure courante |
    +-------------------------------------------------+
       - l'affichage se fait en plein écran
       - on peut simuler une date et une heure (option --date)
       - on peut déclarer un proxy pour accéder au service EDT
    +-------------------------------------------------+
    |                      EXEMPLE                    |
    +-------------------------------------------------+
        maintenant.py http://edt.example.com -d "2016-11-14 15:30" -x proxy.mondomaine.com

