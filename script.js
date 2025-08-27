document.addEventListener("DOMContentLoaded", function () {
    const loginForm = document.querySelector(".login-form");
    const registerForm = document.querySelector(".register-form");
    const showRegister = document.getElementById("showRegister");
    const showLogin = document.getElementById("showLogin");

    // Show Registration Form
    showRegister.addEventListener("click", function (e) {
        e.preventDefault();
        loginForm.classList.remove("active");
        registerForm.classList.add("active");
    });

    // Show Login Form
    showLogin.addEventListener("click", function (e) {
        e.preventDefault();
        registerForm.classList.remove("active");
        loginForm.classList.add("active");
    });

    // Set the login form to be active by default
    loginForm.classList.add("active");
});

// Cart functionality
let cartItems = [];
let cartCount = 0;

function addToCart(productName, price, image) {
    const item = {
        id: Date.now(),
        name: productName,
        price: price,
        image: image,
        quantity: 1
    };

    cartItems.push(item);
    updateCartCount();
    updateCartDisplay();
    showNotification(`Added ${productName} to cart`);
}

function updateCartCount() {
    cartCount = cartItems.reduce((total, item) => total + item.quantity, 0);
    const cartCountElement = document.querySelector('.cart-count');
    if (cartCountElement) {
        cartCountElement.textContent = cartCount;
    }
}

function updateCartDisplay() {
    const cartItemsContainer = document.getElementById('cart-items');
    const cartCountElement = document.getElementById('cart-count');
    let totalQuantity = 0;
    let totalPrice = 0;
    
    if (cartItems.length === 0) {
        cartItemsContainer.innerHTML = '<p style="text-align: center; color: #666; padding: 20px; grid-column: 1 / -1;">Your cart is empty.</p>';
        cartCountElement.textContent = '0';
        document.getElementById('cart-total').textContent = '0.00';
        return;
    }
    
    cartItemsContainer.innerHTML = cartItems.map((item, index) => {
        const itemTotal = item.price * item.quantity;
        totalQuantity += item.quantity;
        totalPrice += itemTotal;
        
        return `
            <div class="cart-item">
                <div class="cart-item-name">${item.name}</div>
                <div class="cart-item-quantity">
                    <button onclick="updateQuantity(${index}, -1)">-</button>
                    <span>${item.quantity}</span>
                    <button onclick="updateQuantity(${index}, 1)">+</button>
                </div>
                <div class="cart-item-price">₱${itemTotal.toFixed(2)}</div>
                <button class="cart-item-remove" onclick="removeFromCart(${index})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
    }).join('');
    
    cartCountElement.textContent = totalQuantity;
    document.getElementById('cart-total').textContent = totalPrice.toFixed(2);
}

function updateQuantity(itemId, change) {
    const itemIndex = cartItems.findIndex(item => item.id.toString() === itemId);
    if (itemIndex === -1) return;

    const newQuantity = cartItems[itemIndex].quantity + change;
    if (newQuantity < 1) return;

    cartItems[itemIndex].quantity = newQuantity;
    updateCartDisplay();
    updateCartCount();
}

function removeFromCart(itemId) {
    cartItems = cartItems.filter(item => item.id.toString() !== itemId);
    updateCartDisplay();
    updateCartCount();
    showNotification('Item removed from cart');
}

function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 2000);
}

function updateCartTotal() {
    const total = cartItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const totalElement = document.getElementById('cart-total-amount');
    if (totalElement) {
        totalElement.textContent = `₱${total.toFixed(2)}`;
    }
}

// Search functionality with real-time results
function searchProducts() {
    const searchInput = document.getElementById('searchBar').value.toLowerCase();
    const products = document.querySelectorAll('.product');
    const searchResults = document.querySelector('.search-results');
    
    if (!searchResults) {
        const resultsContainer = document.createElement('div');
        resultsContainer.className = 'search-results';
        document.querySelector('.search-container').appendChild(resultsContainer);
    }

    const results = [];
    products.forEach(product => {
        const name = product.querySelector('h3').textContent.toLowerCase();
        const description = product.querySelector('p')?.textContent.toLowerCase() || '';
        
        if (searchInput && (name.includes(searchInput) || description.includes(searchInput))) {
            product.style.display = 'flex';
            results.push({
                name: product.querySelector('h3').textContent,
                element: product
            });
        } else {
            product.style.display = searchInput ? 'none' : 'flex';
        }
    });

    updateSearchResults(results);
}

function updateSearchResults(results) {
    const searchResults = document.querySelector('.search-results');
    if (!searchResults) return;

    searchResults.innerHTML = '';

    if (results.length > 0) {
        results.forEach(result => {
            const resultItem = document.createElement('div');
            resultItem.className = 'search-result-item';
            resultItem.textContent = result.name;
            resultItem.addEventListener('click', () => {
                document.getElementById('searchBar').value = result.name;
                searchResults.classList.remove('active');
                result.element.scrollIntoView({ behavior: 'smooth', block: 'center' });
            });
            searchResults.appendChild(resultItem);
        });
        searchResults.classList.add('active');
    } else {
        searchResults.classList.remove('active');
    }
}

// Initialize functionality on page load
document.addEventListener('DOMContentLoaded', () => {
    // Initialize cart
    updateCartCount();

    // Initialize search
    const searchBar = document.getElementById('searchBar');
    if (searchBar) {
        searchBar.addEventListener('input', searchProducts);
        searchBar.addEventListener('focus', () => {
            if (searchBar.value) {
                document.querySelector('.search-results')?.classList.add('active');
            }
        });
    }

    // Close search results when clicking outside
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.search-container')) {
            document.querySelector('.search-results')?.classList.remove('active');
        }
    });
});
