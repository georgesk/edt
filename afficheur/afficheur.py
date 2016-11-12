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
from PyQt5.QtWebKit import *
from PyQt5.QtWebKitWidgets import *

import sys
from Ui_afficheur import Ui_MainWindow

class MaFenetre(QMainWindow):
    def __init__(self, args, parent=None):
        QMainWindow.__init__(self, parent)
        self.ui=Ui_MainWindow()
        self.ui.setupUi(self)
        self.ui.menubar.hide()
        self.ui.toolButton.clicked.connect(self.close)
        self.ui.label.setText("affectation des salles de physique-chimie")
        self.url="http://edt.lyceejeanbart.fr/maintenant.php"
        self.hint=QLabel("Retrouvez ces informations à : {}".format(
            self.url
        ))
        self.ui.statusbar.addPermanentWidget(self.hint)
        self.loadURL()
        self.timer=QTimer()
        self.timer.timeout.connect(self.loadURL)
        self.timer.start(20000) ## toutes les 20 secondes
        return

    def loadURL(self,url="http://www.python.org"):
        """
        charge une URL dans le navigateur intégré.
        """
        if self.url:
            url=self.url
        self.ui.webView.load(QUrl(url))
        self.ui.statusbar.showMessage("Mise à jour ... ok.", 1000)
        
    

def main(args) :
    app=QApplication(args)
    w=MaFenetre(args)
    w.showFullScreen()
    app.lastWindowClosed.connect(app.quit)
    app.exec_()
         
if __name__=="__main__":
    main(sys.argv)
    
