///////// MA FONCTION DES CONDITIONS DE SAISES
document.addEventListener('DOMContentLoaded', function() {
	
		
		const client = document.getElementById('confirmer_panier_client');
		const nomClient = document.getElementById('confirmer_panier_nomClient');
		const contact = document.getElementById('confirmer_panier_contactClient');
		const boutonPayer = document.getElementById('boutonEnvoie');
		
		//////fonction validation
		function verificationDesChamps() {
			if ((client.value.trim() && contact.value && !nomClient.value) ||
				(nomClient.value && contact.value && !client.value)) {
					boutonPayer.disabled = false;
					console.log('ok');
				}
				else {
					boutonPayer.disabled = true;
				}
		}

		/////j'appelle la finctio au niveau des champs
		client.addEventListener('input', verificationDesChamps());
		nomClient.addEventListener('input', verificationDesChamps());
		contact.addEventListener('input', verificationDesChamps());
	
});
