	///je déclare mes constantes pour gérer l'évènement choix type produit
	const choixTypeProduit = document.querySelector('#choixTypeProduit');
	const produit = document.querySelector('#produit');
	const ensemble = document.querySelector('#ensemble');

	const produitForm = document.querySelector('#produitForm');
	const ensembleForm = document.querySelector('#ensembleForm');

	console.log(choixTypeProduit);
	

	////AU chargement de la page
	window.onload = () => 
	{
		/////evenement choixTypeProduit
		choixTypeProduitEvent(produit, produitForm, ensembleForm);

	};


	///si est choixTypeProduit est checké
	if(produit.checked == true)
	{
		/////j'affiche la div type handicap
		produitForm.style.display = "";
		ensembleForm.style.display = "none";
	}
	///sinon
	else
	{
		produitForm.style.display = "none";
		ensembleForm.style.display = "";
	}


	// Si il/elle est handicapé(e)
	choixTypeProduit.addEventListener('change', function()
	{
		choixTypeProduitEvent(produit, produitForm, ensembleForm);
	});

	
	//////////////////TYPE PRODUIT EVENEMENT
	const choixTypeProduitEvent = (produit, produitForm, ensembleForm) => 
	{
		if(produit.checked == true)
		{
			produitForm.style.display = "";
			ensembleForm.style.display = "none";
			
		}else{
			
			produitForm.style.display = "none";
			ensembleForm.style.display = "";
		}
	};
