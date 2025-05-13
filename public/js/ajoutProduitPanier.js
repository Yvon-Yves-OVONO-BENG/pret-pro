	
	//je sélectionne tous les boutons ayant la classe : "ajout-produit-panier"
	const buttons = document.querySelectorAll('.ajout-produit-panier');
	
	//je parcours mes boutons
	buttons.forEach(function(button) 
	{
		button.addEventListener('click', function() 
		{
			var produitId = this.dataset.produitId;
			var quantiteInput = this.previousElementSibling;
			var quantite = quantiteInput.value;

			///je cree un nouvel objet XMLHttpRequest
			var xhr = new XMLHttpRequest();
			xhr.open('POST', '/pretpro/public/ajout-produit-panier', true);
			xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
			xhr.onreadystatechange = function() 
			{
				if (xhr.readyState === 4 && xhr.status === 200) 
				{
					var response = JSON.parse(xhr.responseText);
					if (response.success) 
					{
						notif({
							msg: "<b>Produit ajouté au panier avec succès !</b>",
							type: "success",
							position: "left",
							width: 500,
							height: 60,
							autohide: true
							});

							updateCartCount();
					}
					else 
					{
						notif({
							msg: "<b>Erreur lors de l'ajout du produit au panier !</b>",
							type: "danger",
							position: "left",
							width: 500,
							height: 60,
							autohide: true
							});
					}
				}
			};
			
			//j'envoie la request avec le produit ID
			xhr.send('produit_id=' + produitId + '&quantite=' + quantite);
		});
		
	});

	////fonction qui met à jour le nomre de produit dans mon panier
	function updateCartCount(){
		var xhr = new XMLHttpRequest();
		xhr.open('GET', '/pretpro/public/compter-produits-panier', true);
		xhr.onreadystatechange = function() {
			if (xhr.readyState === 4 && xhr.status === 200) {
				var response = JSON.parse(xhr.responseText);
				if (response.success) {
					var nombreProduitDansLePanier = document.getElementById('nombreProduitPanier');
					var totalApayerDansLePanier = document.getElementById('totalApayerClient');

					nombreProduitDansLePanier.textContent = response.nombreProduit;
					totalApayerDansLePanier.textContent = 'Total : ' + diviseNombreEnBlocDeTrois(response.totalApayer) + ' FCFA';

				}
			}
		};
		xhr.send();

	}

	////fonction qui divise un nombre en bloc de 3 en commençant par la droite
	function diviseNombreEnBlocDeTrois(number) {
		//je vonverti d'abord un nombre en chaine de caractère
		let nombreEnChaine = number.toString();

		//j'utilise une expression régulère pour insérer des espaces tous 3 chiffres
		//en partant de la droite
		let nombreDivise = nombreEnChaine.replace(/\B(?=(\d{3})+(?!\d))/g, " ");

		return nombreDivise;
	}
	