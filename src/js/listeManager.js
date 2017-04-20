
// Masquage de colonnes
function masquerColonnes(event){
	$('a#annuler-masque').show();
	var index = $(event.target).parent().index();
	var cases = $('table#liste').find('td, th');
	for (var i = 0; i < cases.length; i++) {
		var td = $(cases[i]);
		if(td.index() == index){
			td.hide();
		}
	}
}

// Afficher les colonnes masquées
function afficherColonnes(event){
	var cols = $('table#liste').find('td, th');
	console.log('yolo');
	for (var i = 0; i < cols.length; i++) {
		$(cols[i]).show();
	}
}




/*----------------------------------------------------
--  APPLICAITON DES LISTENNERS
-----------------------------------------------------*/
$('a.masque').click(masquerColonnes);
$('a#annuler-masque').hide();
$('a#annuler-masque').click(afficherColonnes);
