
// Retourne un array unique
Array.prototype.unique = function() {
	return this.filter(function(val, i, self){
		return self.indexOf(val) === i;
	});
};

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
$('a#annuler-masque').click(afficherColonnes);
