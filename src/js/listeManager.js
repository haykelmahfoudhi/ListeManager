
// Masquage de colonnes
function masquerColonnes(event) {
	event.preventDefault();
	$('a#annuler-masque').show();
	var index = $(event.target).parent().index();
	var cases = $('table#liste').find('td, th');
	for (var i = 0; i < cases.length; i++) {
		var td = $(cases[i]);
		if(td.index() == index){
			td.hide();
		}
	}
	// Ajout du masque dans les liens
	$('a.titre-colonne, #pagination a, #boutons-options a').each(function(i, e) {
		console.log(e);
	});
}

// Afficher les colonnes masquées
function afficherColonnes(event) {
	event.preventDefault();
	var cols = $('table#liste').find('td, th');
	console.log('yolo');
	for (var i = 0; i < cols.length; i++) {
		$(cols[i]).show();
	}
}



/*----------------------------------------------------
--  APPLICAITON DES LISTENNERS
-----------------------------------------------------*/
// $('a.masque').click(masquerColonnes);
$('a#annuler-masque').hide();
$('a#annuler-masque').click(afficherColonnes);
