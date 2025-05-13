///////// MA FONCTION DES CONDITIONS DE SAISES
document.addEventListener('DOMContentLoaded', function() {
	
	const client = document.getElementById('confirmer_panier_client');
	const nomClient = document.getElementById('confirmer_panier_nomClient');
	const contact = document.getElementById('confirmer_panier_contactClient');
	const boutonPayer = document.getElementById('boutonEnvoie');
	
	///Condition 1 : si la liste déroulnte(client) et nomClient sont renseignés, 
	/////le bouton reste sur disabled
	if (client.value.trim() != '' && nomClient.value.trim() != '') {
		boutonPayer.setAttribute('disabled', 'disabled');

		return;
	} 


	///Condition 2 : si un champ est renseigné seul, le bouton reste sur disabled
	if ((client.value.trim() != '' && nomClient.value.trim() === '' && contact.value.trim() === '' ) ||
		(client.value.trim() === '' && nomClient.value.trim() != '' && contact.value.trim() === '') ||
		(client.value.trim() === '' && nomClient.value.trim() === '' && contact.value.trim() != '')) {
			boutonPayer.setAttribute('disabled', 'disabled') = true;

			return;
	}

	////Condition 3 : si client et contact sont renseignés
	if (client.value.trim() != '' && contact.value.trim() != '') {
		boutonPayer.removeAttribute('disabled');

		return;
	}


	///Condition 4 : si nomClient et contact sont renseignés
	if (nomClient.value.trim() != '' && contact.value.trim() != '') {
		boutonPayer.setAttribute('disabled');

		return;
	}


	/////PAR DEFAUT LE BOUTON EST SUR DISABLES
	boutonPayer.setAttribute('disabled', 'disabled');
});
