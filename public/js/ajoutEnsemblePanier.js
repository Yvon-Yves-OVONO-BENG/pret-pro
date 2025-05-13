	
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
			
			//j'envoie la request avec le produit ID et quantité
			xhr.send('produit_id=' + produitId + '&quantite=' + quantite);
		});
		
	});

	////fonction qui met à jour le nombre de produit dans mon panier
	function updateCartCount(){
		var xhr = new XMLHttpRequest();
		xhr.open('GET', '/pretpro/public/compter-produits-panier', true);
		xhr.onreadystatechange = function() {
			if (xhr.readyState === 4 && xhr.status === 200) {
				var response = JSON.parse(xhr.responseText);
				if (response.success) {
					var nombreProduitDansLePanier = document.getElementById('nombreProduitPanier');
					nombreProduitDansLePanier.textContent = response.nombreProduit;
					console.log(nombreProduitDansLePanier.textContent);
				}
			}
		};
		xhr.send();

	}
	