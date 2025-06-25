const cart = JSON.parse(localStorage.getItem('cart')) || [];

function updateCartCount() {
    document.getElementById('cart-count').textContent = cart.reduce((acc, item) => acc + item.quantity, 0);
}

function saveCart() {
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartCount();
}

function addToCart(product) {
    const existing = cart.find(p => p.id === product.id);
    if (existing) {
        existing.quantity += 1;
    } else {
        product.quantity = 1;
        cart.push(product);
    }
    saveCart();
}

document.querySelectorAll('.add-to-cart-btn').forEach(button => {
    button.addEventListener('click', () => {
        const product = {
            id: button.dataset.id,
            name: button.dataset.name,
            price: parseFloat(button.dataset.price)
        };
        addToCart(product);
    });
});

updateCartCount();

function sendOrder() {
    let jsonOrder = JSON.parse(localStorage.getItem('cart'));
    jsonOrder = JSON.stringify({'order':jsonOrder});

    fetch("http://localhost:8000/stripe/create/link", {
        method: "POST",
        body: jsonOrder,
        headers: {
            "Content-Type": "application/json"
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.url) {
                window.location.href = data.url;
            } else {
                console.error("URL de redirection manquante");
            }
        })
        .catch(error => console.error("Erreur lors de la requête :", error));
}
