
// Retourne un array unique
Array.prototype.unique = function() {
	return this.filter(function(val, i, self){
		return self.indexOf(val) === i;
	});
};

// Objet contenant les paramètres GET
var _GET = function () {
  // This function is anonymous, is executed immediately and 
  // the return value is assigned to QueryString!
  var query_string = {};
  var query = window.location.search.substring(1);
  var vars = query.split("&");
  for (var i=0;i<vars.length;i++) {
    var pair = vars[i].split("=");
        // If first entry with this name
    if (typeof query_string[pair[0]] === "undefined") {
      query_string[pair[0]] = decodeURIComponent(pair[1]);
        // If second entry with this name
    } else if (typeof query_string[pair[0]] === "string") {
      var arr = [ query_string[pair[0]],decodeURIComponent(pair[1]) ];
      query_string[pair[0]] = arr;
        // If third or later entry with this name
    } else {
      query_string[pair[0]].push(decodeURIComponent(pair[1]));
    }
  } 
  return query_string;
}();

// Masquage de colonnes
function masquerColonne(index) {
	var cases = $('table#liste').find('td, th');
	for (var i = 0; i < cases.length; i++) {
		var td = $(cases[i]);
		if(td.index() == index){
			td.hide();
		}
	}
}

// Fonction qui sera appelé au click des bouton pour masquer les colonnes
function masquerColonnesOnClick(event) {
	event.preventDefault();
	$('a#annuler-masque').show();
	var index = $(event.target).parent().index();
	masquerColonne(index);
	// Ajout du masque dans la sessions
	var tabMask = JSON.parse(sessionStorage.getItem('mask'));
	if(tabMask != null){
		tabMask.push(index);
	}
	else {
		tabMask = [index];
	}
	sessionStorage.setItem('mask', JSON.stringify(tabMask.unique()));
}


// Afficher les colonnes masquées
function afficherColonnes(event) {
	event.preventDefault();
	var cols = $('table#liste').find('td, th');
	for (var i = 0; i < cols.length; i++) {
		$(cols[i]).show();
	}
	//Suppression du mask
	sessionStorage.removeItem('mask');
	$('a#annuler-masque').hide();
}

// Affiche / masque les champs de saisie
function afficherChampsRecherche(val) {
	var inputSelect = $('#liste tr.tabSelect');
	if(inputSelect.length > 0){
		sessionStorage.setItem('quest', val);
		if(val) {
			inputSelect.show();
			$('form#recherche').show();
		}
		else {
			inputSelect.hide();
			$('form#recherche').hide();
		}
	}
}
afficherChampsRecherche(sessionStorage.getItem('quest'));


// Masquer les colonnes correspondantes dans sasseionStorage
var tabMask = JSON.parse(sessionStorage.getItem('mask'));
if(tabMask != null){
	for (var i = 0; i < tabMask.length; i++) {
		masquerColonne(tabMask[i]);
	}
}
else {
	$('a#annuler-masque').hide();
}

/*----------------------------------------------------
--  APPLICAITON DES LISTENNERS
-----------------------------------------------------*/
$('a.masque').click(masquerColonnesOnClick);
$('#boutons-options a.recherche').click(function (event) {
	event.preventDefault();
	afficherChampsRecherche(sessionStorage.getItem('quest') != "true");
});
$('a#annuler-masque').click(afficherColonnes);
$('tr.recherche > input')
