window.onload = function() {

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

// Met à jour l'url de la page : à appeler à chaque changement de sessionStorage
function updateURL() {
	var tabUrl = document.URL.split('#');
	if(sessionStorage.length)
		document.location = tabUrl[0] + '#' + encodeURIComponent(JSON.stringify(sessionStorage));
	else
		document.location = tabUrl[0] + '#{}';
}

// Test si changement de page
if(	sessionStorage.getItem('location_script') === null
	|| sessionStorage.getItem('location_script') === window.location.pathname){
	
	// Récupère les données de session et les applique
	var tabUrl = document.URL.split('#');
	if(tabUrl.length == 2 && tabUrl[1].length){
		try {
			$.each(JSON.parse(decodeURIComponent(tabUrl[1])), function(i, e){
				sessionStorage.setItem(i, e);
			});
		}
		catch(e) {
			updateURL();
		}
	}
	if(sessionStorage.getItem('location_script') === null)
		sessionStorage.setItem('location_script', window.location.pathname);
}
// Si on a changé de page on clear le sessionStorage et on enregistre la location
else {
	sessionStorage.setItem('location_script', window.location.pathname);
	sessionStorage.clear();
}


// Masquage de colonnes
function masquerColonne(index, dataId) {
	var cases = $('table' + ((dataId.length && dataId != 'defaut')? '[data-id='+dataId+']' : '') + '.liste').find('td, th');
	for (var i = 0; i < cases.length; i++) {
		var td = $(cases[i]);
		if(td.index() == index){
			td.hide();
		}
	}
}

// Fonction qui sera appelée au click des bouton pour masquer les colonnes
function masquerColonnesOnClick(event) {
	event.preventDefault();
	var listeParent = $(event.currentTarget).parents('div.liste-parent');
		dataId = listeParent.find('table.liste').attr('data-id'),
		dataId = ((typeof dataId == 'undefined')? '' : dataId);
	listeParent.find('a.annuler-masque').show();
	var index = $(event.target).parent().index();
	masquerColonne(index, dataId);

	if(!dataId.length)
		dataId = 'defaut';
	// Ajout du masque dans la session
	var tabMask = JSON.parse(sessionStorage.getItem('mask'));
	if(tabMask != null){
		var tabMaskListe = tabMask[dataId];
		if(tabMaskListe == null)
			tabMaskListe = [index];
		else {
			tabMaskListe.push(index);
			tabMaskListe = tabMaskListe.unique();
		}
		tabMask[dataId] = tabMaskListe;
	}
	else {
		tabMask = new Object();
		tabMask[dataId] = [index];
	}
	sessionStorage.setItem('mask', JSON.stringify(tabMask));
	updateURL();
}


// Afficher les colonnes masquées
function afficherColonnes(event) {
	event.preventDefault();
	var listeParent = $(event.currentTarget).parents('div.liste-parent').find('table.liste'),
		dataId = listeParent.attr('data-id'),
		dataId = ((typeof dataId == 'undefined')? '' : dataId),
		cols = listeParent.find('td, th');
	for (var i = 0; i < cols.length; i++) {
		$(cols[i]).show();
	}
	//Suppression du mask
	var tabMask = JSON.parse(sessionStorage.getItem('mask'));
	if(tabMask != null) {
		if(dataId.length)
			tabMask[dataId] = null;
		else {
			tabMask.defaut = null;
		}
		sessionStorage.setItem('mask', JSON.stringify(tabMask));
	}
	listeParent.parents('div.liste-parent').find('a.annuler-masque').hide();
	updateURL();
}

// Permet d'actualiser la largeur des colonnes des titres de la liste
var titresFixes = $('.liste[fixed-titles=true]').length == 1;
function actualiserLargeurCol() {
	if(titresFixes) {
		var ths = $(ligneTitres).children('th'),
			tds = $(".liste tr:not(.tabSelect, .ligne-titres):eq(0) td"),
			tdSelect = $('.liste tr.tabSelect td');
		// On fixe la largeur des td
		tds.each(function(index, td) {
			if(index < ths.length) {
				var padding = parseInt(window.getComputedStyle(ths[index], null).paddingLeft) 
					+ parseInt(window.getComputedStyle(ths[index], null).paddingRight) + 2,
					tdWidth = td.offsetWidth - padding,
					thWidth = ths[index].offsetWidth - padding,
					tdSelectWidth = ((tdSelect.length && index < tdSelect.length)?
						tdSelect[index].offsetWidth - padding : 0 );
				if(tdSelectWidth > 0)
					$(td).css('min-width', Math.max(tdWidth, thWidth, tdSelectWidth)+'px');
				else
					$(td).css('min-width', Math.max(tdWidth, thWidth)+'px');
			}
		});
		// On fixe la largeur des th en fonction de celles des tds
		for (var i = ths.length - 1; i >= 0; i--) {
			$(ths[i]).css('min-width', $(tds[i]).css('min-width'));
		}
	}
}

// Affichage des champs de recherche
function afficherChampsRecherche(listeParent, premierChargement){
	var inputTr = listeParent.find('tr.tabSelect');
	if(inputTr.length) {
		var dataId = listeParent.find('.liste').attr('data-id'),
			dataId = ((typeof dataId == 'undefined')? 'defaut' : dataId),
			formQuest = listeParent.find('form.recherche'),
			dispTSData = listeParent.find('table.liste').attr('disp-tabSelect') == 'true',
			displayTS = function(afficher){
				if(afficher){ inputTr.show(); formQuest.show(); }
				else{ inputTr.hide(); formQuest.hide(); }
				if(!premierChargement || typeof tabQuest[dataId] == 'undefined')
					tabQuest[dataId] = afficher;
			};

		try {
			tabQuest = JSON.parse(sessionStorage.getItem('tabQuest'));
		} catch(e) {
			tabQuest = null;
		}

		if(tabQuest == null)
			tabQuest = {};

		if(typeof tabQuest[dataId] == 'undefined')
			displayTS(dispTSData);
		else
			displayTS(!(premierChargement ^ tabQuest[dataId])); // ^ => XOR
		
		sessionStorage.setItem('tabQuest', JSON.stringify(tabQuest));
	}
	actualiserLargeurCol();
	updateURL();
}
$('.liste-parent').each(function(i, e){
	afficherChampsRecherche($(e), true);
});


// Masquer les colonnes correspondantes dans sessionStorage
$('a.annuler-masque').hide();
var tabMask = JSON.parse(sessionStorage.getItem('mask'));
if(tabMask != null){
	$.each(tabMask, function(index, element){
		if(element != null) {
			$('.liste[data-id='+index+']').parents('.liste-parent').find('a.annuler-masque').show();
			for (var i = 0; i < element.length; i++)
				masquerColonne(element[i], index);
		}
	});
}


// Fixer les titres & boutons de la liste sur le doc
if(titresFixes) {
	var ligneTitres = document.querySelector(".ligne-titres"),
		divBoutons = document.querySelector(".boutons-options"),
		offsetTop = $(ligneTitres).offset().top,
		offsetLeftLT = $(ligneTitres).offset().left,
		offsetLeftDB = $(divBoutons).offset().left,
		toRefresh = true;

	actualiserLargeurCol();

	// Callback sur le scroll
	function scrollTitre(){
		if((document.body.scrollTop || document.documentElement.scrollTop) >= offsetTop) {
			if(toRefresh){
				actualiserLargeurCol();
				toRefresh = false;
			}
			ligneTitres.classList.add("fixed");
			divBoutons.classList.add('fixed');
			$(divBoutons).css('left', offsetLeftDB - $(window).scrollLeft());
			$(ligneTitres).css('left', offsetLeftLT - $(window).scrollLeft());
			$(divBoutons).next().css('margin-left', window.getComputedStyle(divBoutons, null).width);
		}
		else {
			ligneTitres.classList.remove("fixed");
			divBoutons.classList.remove("fixed");
			$(divBoutons).next().css('margin-left', 0);
		}
	}
}

// Masque les colonnes enregistrées dans le sessionStorage 
$('table.liste').each(function(i, e) {
	var dataId = e.getAttribute('data-id'),
		dataId = ((dataId == null)? 'defaut' : dataId),
		tabMask = JSON.parse(sessionStorage.getItem('mask'));

	if(tabMask == null)
		tabMask = {};

	if(typeof tabMask[dataId] != 'undefined' && tabMask[dataId] != null){
		for(var j=0; j<tabMask[dataId].length; j++)
			masquerColonne(tabMask[dataId][j], dataId);
		$(e).parents('div.liste-parent').find('a.annuler-masque').show();
	}
});


//Ajoute le masque à l'url avant export en excel
function maskExportExcel(listeParent) {
	var dataId = listeParent.find('.liste').attr('data-id'),
		dataId = ((typeof dataId == 'undefined')? 'defaut' : dataId),
		tabMask = JSON.parse(sessionStorage.getItem('mask')),
		tabUrl = document.URL.toString().split('#'),
		url = tabUrl[0] + ((tabUrl[0].indexOf('?') !== -1)? '' : '?')
			+ "&lm_excel" + ((dataId == 'defaut')? '' : dataId) + "=1";

	if(tabMask != null && typeof tabMask[dataId] == 'object'){
		var maskUrl = encodeURIComponent(tabMask[dataId].join());
		url += '&lm_mask' + ((dataId == 'defaut')? '' : dataId) + "=" + maskUrl;
	}
	document.location = url;
}


/*----------------------------------------------------
--  APPLICAITON DES LISTENNERS
-----------------------------------------------------*/
$('a.masque').click(masquerColonnesOnClick);
$('.boutons-options a.btn-recherche').click(function(event) {
	event.preventDefault();
	afficherChampsRecherche($(event.currentTarget).parents('.liste-parent'), false);
});
$('a.annuler-masque').click(afficherColonnes);
$('tr.recherche > input')
if(titresFixes)
	addEventListener("scroll", scrollTitre, false);
$('a.btn-excel').click(function(event) {
	event.preventDefault();
	maskExportExcel($(event.currentTarget).parents('.liste-parent'));
});


}