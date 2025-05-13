	///////// je récupère toutes mes classes "quantite"
	var quantites = document.querySelectorAll('.quantite');

	///////je boucle sur mes champs
	quantites.forEach(input => {
		input.addEventListener('blur', function() {
			var produitId = this.dataset.produitId;
			var quantite = this.value;

			var xhr = new XMLHttpRequest();
			xhr.open('POST', '/pretpro/public/ajout-quantite-produit-panier', true);
			xhr.setRequestHeader('Content-type', 'application/json');
			xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
			xhr.onreadystatechange = function() {
				if (xhr.readyState === 4 && xhr.status === 200) {
					console.log('Mise à jour avec succès');
				}
			};
			xhr.send(JSON.stringify({ produitId: produitId, quantite: quantite}));
		});
	});