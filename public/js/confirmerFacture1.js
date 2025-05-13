document.addEventListener('DOMContentLoaded', () => {

	//je sélectionne les champs de mon formulaire avec leurs ID
	const nomClient = document.getElementById('confirmer_panier_nomClient');
	const contactClient = document.getElementById('confirmer_panier_contactClient');
	const bouton = document.getElementById('boutonEnvoie');
	console.log(bouton);
	
	function miseAjourFormulaire() {
		const nomClientValue = nomClient.value;
		const contactClientValue = contactClient.value;

		//////GERE L'ETAT DU BOUTON
		if ((nomClientValue && contactClientValue)) 
			{
				bouton.disabled = false;
				bouton.classList.remove('btn-outline-danger btn-lg btn-block', 'disabled');
				bouton.classList.add('btn-outline-primary btn-lg btn-block');
			}
			else {
				bouton.disabled = true;
				bouton.classList.remove('btn-outline-primary btn-lg btn-block');
				bouton.classList.add('btn-outline-danger btn-lg btn-block', 'disabled');
			}
	}

	nomClient.addEventListener('input', miseAjourFormulaire);
	contactClient.addEventListener('input', miseAjourFormulaire);

});	