/**
 * Répare la localisation incomplète du paquet libjs-jquery-timepicker
 * pour le français
 **/
$.timepicker.regional.fr={
  timeOnlyTitle:"Choisir une heure",
  timeText:"Heure",
  hourText:"Heures",
  minuteText:"Minutes",
  secondText:"Secondes",
  millisecText:"Millisecondes",
  timezoneText:"Fuseau horaire",
  currentText:"Maintenant",
  closeText:"Valider",
  timeFormat:"HH:mm",
  amNames:["AM","A"],
  pmNames:["PM","P"],
  isRTL:false};
$.timepicker.setDefaults($.timepicker.regional.fr);

$.datepicker.regional.fr = {
	closeText: 'Valider',
	prevText: '< Précédent',
	nextText: 'Suivant >',
	currentText: 'Maintenant',
	monthNames: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre','Octobre', 'Novembre', 'Décembre'],
	monthNamesShort: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aou', 'Sep', 'Oct', 'Nov', 'Dec'],
	dayNames: ['Dimanche','Lundi','Mardi','Mercredi', 'Jeudi','Vendredi', 'Samedi'],
	dayNamesShort: ['DIM','LUN','MAR','MER','JEU','VEN','SAM'],
	dayNamesMin: ['DIM','LUN','MAR','MER','JEU','VEN','SAM'],
	weekHeader: 'Sem',
	dateFormat: 'dd/mm/yy',
	firstDay: 1,
	isRTL: false,
	showMonthAfterYear: false,
	yearSuffix: ''
};
$.datepicker.setDefaults($.datepicker.regional.fr);
