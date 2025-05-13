document.addEventListener('DOMContentLoaded', function() {

	//je sélectionne les champs de mon formulaire avec leurs ID
	const prisEnCharge = document.getElementById('confirmer_panier_client');
	const nomClient = document.getElementById('confirmer_panier_nomClient');
	const contactClient = document.getElementById('confirmer_panier_contactClient');
	const bouton = document.getElementById('boutonEnvoie');

	////////////EVENEMENT SUR LA LISTE DEROULANTE
	prisEnCharge.addEventListener('change', function() {
		if(prisEnCharge.value.trim()) {
			console.log(prisEnCharge.value);
			nomClient.disabled = true;
			nomClient.value = "";
		}
		else
		{
			nomClient.disabled = false;
			nomClient.required = true;
		}
	});

	////////////EVENEMENT SUR LE CHAMP TEXT
	nomClient.addEventListener('input', function() {
		if (nomClient.value.trim()) {
			prisEnCharge.disabled = true;
			prisEnCharge.value = "";

		}
		else{
			prisEnCharge.disabled = false;
		}
	});
	
});
	