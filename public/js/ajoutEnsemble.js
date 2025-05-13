let collection, boutonAjout, boutonEnregistrement, span;
    window.onload = () => {
        collection = document.querySelector("#ensemble");
        span = collection.querySelector("span");

        collection.style = "text-align: center; width: 10px; ";
        collection.style.display = "inline";
        collection.style.display = "";
 
        boutonAjout = document.createElement("button");

        boutonAjout.className = "ajout-ensemble btn btn-outline-primary";
        boutonAjout.innerText = "Ajouter un produit";
        boutonAjout.style.marginTop = "10px";
        boutonAjout.style.display = "inline-block";
        
        let nouveauBouton = span.append(boutonAjout);

        collection.dataset.index = collection.querySelectorAll("input").length;
        boutonAjout.addEventListener("click", function(){
            addButton(collection, nouveauBouton);
        });

        
    }

    function addButton(collection, nouveauBouton)
    {
        let prototype = collection.dataset.prototype;

        let index = collection.dataset.index;

        prototype = prototype.replace(/__name__/g, index);

        let content = document.createElement("html");
        content.innerHTML = prototype;

        let newForm = content.querySelector("div");

        newForm.style = "margin-top: 15px";

        let boutonSuppr = document.createElement("button");

        boutonSuppr.type = "button";
        boutonSuppr.className = "col-md-4";
        boutonSuppr.className = "btn btn-outline-danger";
        boutonSuppr.id = "delete-ensemble-" + index;
        boutonSuppr.innerText = "Supprimer ce produit";
        boutonSuppr.style.marginTop = "10px";
        boutonSuppr.style.display = "inline-block";

        newForm.append(boutonSuppr);
        collection.dataset.index++;
        
        let boutonAjout = collection.querySelector(".ajout-ensemble");

        span.insertBefore(newForm, boutonAjout);
        boutonSuppr.addEventListener("click", function(){
            this.previousElementSibling.parentElement.remove();
        })
    }