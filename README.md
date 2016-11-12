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

