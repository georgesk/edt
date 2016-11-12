#!/usr/bin/python3
"""
Un afficheur dédié qui lit les données de l'emploi du temps et les
affiche dans une fenêtre graphique adpatée à l'écran disponible.

Les données à afficher sont relatives à une date et une heure.
Doivent être affichées les données du jour, depuis H-1 jusqu'à H+3,
si on demande un affichage à l'heure H (soit : cinq lignes de données)
"""
from PyQt5.QtGui import *
from PyQt5.QtCore import *
from PyQt5.QtWidgets import *
from PyQt5.QtNetwork import *

import sys, json, re, datetime, argparse, atexit, textwrap
from Ui_maintenant import Ui_MainWindow
from etiquette import Etiquette
        
            
class MaFenetre(QMainWindow):
    # variables statiques de la classe
    proxyHost=None
    proxyPort=3128

    def __init__(self, parsed, parent=None):
        """
        Le constructeur
        @param parsed le résultat de l'examen des paramètres de la ligne
        de commande
        "prox" et "port"
        """
        QMainWindow.__init__(self, parent)
        self.ui=Ui_MainWindow()
        self.ui.setupUi(self)
        self.ui.menubar.hide()
        self.ui.toolButton.clicked.connect(self.close)
        self.ui.label.setText("affectation des salles de physique-chimie")
        self.url=parsed.url+"/maintenant.ajax.php"
        self.date=None
        if len(parsed.date)>0:
            datePattern=re.compile(r"(\d\d\d\d).(\d\d).(\d\d).(\d\d).(\d\d)")
            m= datePattern.match(parsed.date)
            if m:
                date="{0:s}-{1:s}-{2:s} {3:s}:{4:s}".format(*m.groups())
                self.date=datetime.datetime.strptime(date,'%Y-%m-%d %H:%M')
                self.url+="?date={0:s}-{1:s}-{2:s}+{3:s}:{4:s}".format(*m.groups())
        self.hint=QLabel("Retrouvez ces informations à : {}/maintenant.php".format(parsed.url))
        self.ui.statusbar.addPermanentWidget(self.hint)
        ## pas de barres de défilement
        self.ui.graphicsView.setHorizontalScrollBarPolicy (
            Qt.ScrollBarAlwaysOff )
        self.ui.graphicsView.setVerticalScrollBarPolicy (
            Qt.ScrollBarAlwaysOff )
        ## on attache la scène
        self.scene=QGraphicsScene()
        self.ui.graphicsView.setScene(self.scene)
        # on fixe la dimension de la fenêtre
        # avant d'y afficher quoi que ce soit
        # sinon les ajustements d'échelle sont inopérants.
        self.showFullScreen()
        ## on ajuste l'accès au web
        proxies=[]
        if parsed.proxy:
            proxies.append((parsed.proxy, parsed.port))
            proxies=proxies*3 # on essayera trois fois le premier proxy
        proxies.append((None,3128)) # solution de secours : pas de proxy
        for p in proxies:
            self.proxyHost=p[0]
            self.proxyPort=int(p[1])
            if self.loadURL()==QNetworkReply.NoError:
                # Si on arrive à obtenir un résultat par un des proxys
                # de la liste, on le garde et on stoppe les recherches.
                break
        ## on programme la répétition de ce rechargement de l'url
        delay=10 * 1000                 ## toutes les 10 secondes
        self.timer=QTimer()
        atexit.register(self.stopTimer)
        self.timer.timeout.connect(self.loadURL)
        self.timer.start(delay)
        return

    def stopTimer(self):
        self.timer.stop()

    def loadURL(self):
        """
        charge une URL dans le navigateur intégré. S'il y a une réponse
        self.parseJSON sera éventuellement appelé pour modifier les données
        affichées
        @return l'objet erreur résultat. S'il vaut QNetworkReply.NoError c'est
          que tout va bien.
        """
        el=QEventLoop()
        mgr=QNetworkAccessManager()
        if self.proxyHost:
            proxy=QNetworkProxy(QNetworkProxy.HttpProxy,
                                hostName=self.proxyHost,
                                port=self.proxyPort)
            mgr.setProxy(proxy)
        mgr.finished.connect(el.quit)
        req=QNetworkRequest(QUrl(self.url))
        reply=mgr.get(req)
        el.exec_()

        error = reply.error()
        if error == QNetworkReply.NoError:
            self.ui.statusbar.showMessage("Mise à jour ... ok.", 1000)
            self.parseJSON(reply)
        else:
            self.ui.statusbar.showMessage("Échec : {}".format(reply.errorString()), 1000)
        return error

    def adjustDate(self):
        """
        @return une date déterminée selon la date renvoyée par AJAX
        et éventuellement la date passé par paramètre au lancement
        de l'application.
        """
        try:
            date = datetime.datetime.strptime(
                self.dateServeur,
                '%A %d %B %Y, il est %H:%M')
        except:
            date = datetime.datetime.now() ## valeur par défaut si erreur
        if self.date:
            date = self.date
        return date

    def clearScene(self):
        """
        enlève tous les items graphiques
        """
        for i in self.scene.items():
            self.scene.removeItem(i)
        return

    def horaireAffichage(self, date, maxlignes = 5):
        """
        ajuste quatre entiers : self.debut, self.premiereHeure,
        self.derniereHeure, self.maxlignes qui représentent l'heure
        actuelle, la première heure à afficher, la dernière heure à
        afficher et le nombre de lignes
        @param date un objet datetime.datetime
        @param maxlignes nombre de lignes à afficher; 5 par défaut
        """
        self.maxlignes=maxlignes
        self.debut=date.hour
        self.premiereHeure=8
        self.derniereHeure=18
        if self.debut < self.premiereHeure:
            self.debut=self.premiereHeure
        # on avance l'heure du début de deux heures au maximum
        # s'il est tôt
        for i in range(2):
            if self.debut > self.premiereHeure:
                self.debut -=1
        # on avance plus l'heure de début s'il est tard
        if self.debut > 1+self.derniereHeure - maxlignes:
            self.debut = 1+self.derniereHeure - maxlignes

    def colHeures(self, heureActive, bigFont=None):
        """
        place la colonne des heures dans la scène
        @param heureActive: l'heure à laquelle on attire l'attention
        @param bigFont une fonte; None par defaut
        """
        if bigFont==None:
            bigFont=QFont("Helvetica",pointSize=30)
        h=Etiquette.rack["linHeight"]*2
        for heure in range(self.debut,self.debut+self.maxlignes):
            if heure==heureActive:
                ### on attire l'attention sur l'heure actuelle
                brightColor=QColor("lightcyan")
                width=len(self.columnlist)*Etiquette.rack["colWidth"]+300
                self.scene.addRect(0,180+(heure-self.debut)*h, width, h,
                                   brush=QBrush(brightColor),
                                   pen=QPen(brightColor)
                )
            self.scene.addRect(0,180+(heure-self.debut)*h, 120, h)
            ht=self.scene.addText("{:02d}:00".format(heure), bigFont)
            ht.setPos (5,180+10+(heure-self.debut)*h)
        return

    def placeEtiquettes(self):
        """
        Place les étiquettes dans la scène
        """
        for e in self.etiquettes:
            heure_beg=e.heure()
            duree=int(re.match(r"([.\d]*).*", e.duree).group(1))/2
            heure_end=heure_beg+duree
            hauteur_heure=2*Etiquette.rack["linHeight"]
            if heure_end >= self.debut and heure_beg < self.debut+self.maxlignes:
                e.enScene(self.scene,
                          o_left=119,
                          o_top=280+hauteur_heure*(self.premiereHeure-self.debut))
        return

    def titreSalles(self):
        """
        Place les titres de colonnes (salles occupées), avec un fond
        opaque.
        """
        for col in range(len(self.columnlist)):
            h=80 ## hauteur de la ligne de titres
            top=180-h
            w=Etiquette.rack["colWidth"]
            self.scene.addRect(120+col*w, top, w, h,
                               brush=QBrush(QColor("white"))
            )
            mediumFont=QFont("Helvetica",pointSize=24)
            cl=self.scene.addText(self.columnlist[col], mediumFont)
            cl.setPos(120+col*w+5, top+10)
        return

    def titreDate(self, date, bigFont=None):
        """
        Met la date comme titre de la scène
        @param date un objet de type datetime.datetime
        @param bigFont une fonte pour la date; None par défaut
        """
        if bigFont==None:
            bigFont=QFont("Helvetica",pointSize=30)
        width=len(self.columnlist)*Etiquette.rack["colWidth"]+120
        self.scene.addRect(0,0, width, 100,
                                   brush=QBrush(QColor("white"))
        )
        dateWidget=self.scene.addText(
            date.strftime("%A %d %B %Y, il est %H:%M"),
            bigFont
        )
        dateWidget.setPos(0,0)
        return

    def cadreScene(self):
        """
        Force le cadre de la scène
        """
        height=300+5*2*Etiquette.rack["linHeight"]
        if len(self.columnlist):
            width=120+len(self.columnlist)*Etiquette.rack["colWidth"]
        else:
            width=120+4*Etiquette.rack["colWidth"]
        self.scene.setSceneRect(0,0,width,height)
        self.ui.graphicsView.fitInView(0,0,width,height)
        return

    def lignesHorizontales(self):
        """
        trace des lignes horizontales pour guider le regard
        """
        h=Etiquette.rack["linHeight"]*2
        for heure in range(self.debut,1+self.debut+self.maxlignes):
            x=0
            y=180+(heure-self.debut)*h
            w=120+len(self.columnlist)*Etiquette.rack["colWidth"]
            self.scene.addLine(x,y,x+w, y, pen=QPen(QColor(0, 0, 0xff, 0x40),3))
        return

    def parseJSON(self, reply):
        """
        Interprète les données reçues du web et dessine dans le canevas
        @param reply un objet QNetworkReply qui contient la réponse du serveur
        """
        ## digestion de la réponse du serveur :
        ## ajuste plusieurs propriétés de self
        strReply = reply.readAll()
        data=json.loads(bytes(strReply).decode("utf8"))
        self.columnlist=data["columnlist"]
        self.etiquettes=[Etiquette(e,self.columnlist) for e in data["etiquettes"]]
        self.dateServeur=data["date"]
        ## trace la scène
        self.clearScene()
        date=self.adjustDate()
        self.horaireAffichage(date)
        self.colHeures(date.hour)
        self.placeEtiquettes()
        self.titreSalles()
        self.titreDate(date)
        self.lignesHorizontales()
        self.cadreScene()

        return    

def parse(args):
    """
    Prend en compte les arguments (à utiliser avec sys.argv[1:]
    @return un NameSpace avec les variables date, proxy, port et url
    positionnées
    """
    parser = argparse.ArgumentParser(
        prog='maintenant.py',
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog=textwrap.dedent('''\
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
'''),
    )
    parser.add_argument('--date', '-d', help='Simule une date (exemple : "2016-11-14 09:30")', default="")
    parser.add_argument('--proxy', '-x', dest='proxy', help='Nom d\'hôte du proxy (exemple : "proxy.mondomaine.com")', default="")
    parser.add_argument('--port', '-p', help="Port du proxy (3128 par défaut)", default="3128")
    parser.add_argument('url', help='URL du service EDT (exemple : "http://edt.exemple.com")')
    parsed=parser.parse_args(args)
    return parsed

def main(args) :
    parsed=parse(args[1:])
    app=QApplication(args)
    w=MaFenetre(parsed)
    w.showFullScreen()
    #w.show()
    app.lastWindowClosed.connect(app.quit)
    app.exec_()
         
if __name__=="__main__":
    main(sys.argv)
    
