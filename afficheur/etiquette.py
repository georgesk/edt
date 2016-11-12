"""
Structure des étiquettes du programme edt, adaptée au langage Python
"""
from PyQt5.QtGui import *
from PyQt5.QtCore import *

class Offset:
    def __init__(self, data):
        """
        Le constructeur prend en entrée les données issues de la requête AJAX
        qui sont désérialisées depuis un format JSON
        """
        for field in ("left", "top"):
            setattr(self,field, data[field])
        return
    def __str__(self):
        return "Offset({left}, {top})".format(**self.__dict__)

def crushToWidth(obj, w):
    """
    compresse la largeur d'un objet QGraphicsItem pour qu'il rentre
    à coup sûr dans une largeur donnée
    @param un descendant de QGraphicsItem
    @param w une largeur à ne pas dépasser
    """
    width=obj.boundingRect().width()
    if width > w:
        factor=w/width
        obj.setScale(factor)
    return
    
class Etiquette:
    rack ={
	"colWidth"  : 90,     #// largeur des colonnes en pixels
	"linHeight" : 50,     #// hauteur des lignes en pixels
	"mainTop"   : 60,     #// haut de l'affichage principal
	"mainLeft"  : 5,      #// marge gauche de l'affichage principal
	"toolWidth" : 200,    #// largeur de la zone d'outils
	"toolHeight": 64,     #// hauteur de la zone d'outils
    }
    def __init__(self, data, colonnes=[]):
        """
        Le constructeur prend en entrée les données issues de la requête AJAX
        qui sont désérialisées depuis un format JSON.
        @param data des données désérialisées concernant les étiquettes et
        leur position dans le rack
        @param colonnes une liste de colonnes nommées (vide par défaut)
        """
        self.colonnes=colonnes
        for field in ("classe", "color", "duree", "nom"):
            setattr(self,field, data[field])
        setattr(self,"offset", Offset(data["offset"]))
        return
    def __repr__(self):
        return "Étiquette({nom},{classe},{duree},{offset})".format(**self.__dict__)
    def __str__(self):
        colonne=self.colonneNom()
        if not colonne:
            colonne=self.colonne()
        return "Étiquette({nom}, {classe}, {duree} h, {heure}, {colonne})".format(
            nom=self.nom,
            classe=self.classe,
            duree=int(self.duree)/2,
            heure=self.heure(),
            colonne=colonne,
        )

    def heure(self):
        """
        Calcule l'heure (entière ou demi-entière) d'une étiquette,
        sur la base des dimensions de Etiquette.rack, et sachant qu'une
        ligne d'étiquettes correspons à une demi-heure. La ligne zéro
        correspond à 8:00
        """
        return 8+round(
            (self.offset.top - self.rack["mainTop"] - self.rack["toolHeight"])
            /
            self.rack["linHeight"]
        )/2
    
    def colonne(self):
        """
        Calcule le numéro de colonne d'une étiquette, sur la base
        des dimensions de Etiquette.rack, puis renvoie son numéro
        """
        c = round(
            (self.offset.left - self.rack["mainLeft"] - self.rack["toolWidth"])
            /
            self.rack["colWidth"]
        )
        return c
    def colonneNom(self):
        """
        Renvoie un nom de colonne quand c'est possible
        """
        result=""
        c=self.colonne()
        if c < len(self.colonnes):
            result=self.colonnes[c]
        return result
    
    def enScene(self, scene, o_left=50, o_top=100,
                nomFont=None, classeFont=None):
        """
        affiche l'étiquette dans une scène
        @param scene un object QGraphicsScene
        @param o_left abscisse de l'origine
        @param o_top ordonnée de l'origine
        @param nomFont police de caractère pour le nom
        @param classeFont police de caractères pour la classe
        """
        if not self.colonneNom():
            return ## n'affiche pas les étiquettes de colonnes anonymes
        if nomFont==None:
            nomFont=QFont("Helvetica", pointSize=10, weight=QFont.ExtraBold)
            #nomFont=QFont("Helvetica", pointSize=10)
        if classeFont==None:
            classeFont=QFont("Helvetica", pointSize=20, weight=QFont.ExtraBold)
            #classeFont=QFont("Helvetica", pointSize=20)
        w0=self.rack["colWidth"]
        h0=self.rack["linHeight"]
        dx=o_left-self.rack["mainLeft"]-self.rack["toolWidth"]-4
        dy=o_top-self.rack["mainTop"]-self.rack["toolHeight"]-99
        x=dx+self.offset.left
        y=dy+self.offset.top
        h=int(self.duree)*(h0)
        w=w0
        rect=scene.addRect(
            QRectF(x, y, w, h),
            brush=QBrush(QColor(self.color)),
        )
        for i in range(0, int(self.duree), 2):
            nom=scene.addText(self.nom,nomFont)
            nom.setPos(x+5,y+3+i*self.rack["linHeight"])
            crushToWidth(nom, self.rack["colWidth"]-10)

            classe=scene.addText(self.classe,classeFont)
            classe.setPos(x+5,y+23+i*self.rack["linHeight"])
            crushToWidth(classe, self.rack["colWidth"]-10)
        return
        
