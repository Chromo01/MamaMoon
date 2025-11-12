document.addEventListener('DOMContentLoaded', async () => {
    window.addEventListener('error', (e) => {
    console.error('[MM] Global error:', e.message, e.filename+':'+e.lineno);
  });
  
  let candles = [];
  let currentProduct = null;

  // --- Immediate references ---
  const imageElement = document.querySelector('#picbox .image-fit, #picbox img');
  const priceElement = document.querySelector('.buy-box .price');
  const scentButtons = document.querySelectorAll('.scent-selector button');
  const addToCartBtn = document.querySelector('.buy-box .add-to-cart-btn');

  // --- Fetch product data ---
  try {
    const response = await fetch('nuerons.php');
    candles = await response.json();
    showRelatedProducts();
  } catch (err) {
    console.error('Error fetching candle data:', err);
  }

  try {
  const response = await fetch('nuerons.php');
  if (!response.ok) throw new Error('nuerons.php returned '+response.status);
  candles = await response.json();
  console.log('[MM] Loaded candles:', candles);
  showRelatedProducts();
} catch (err) {
  console.error('[MM] Error fetching candle data:', err);
}

  // --- Scent buttons ---
  scentButtons.forEach(button => {
    button.addEventListener('click', () => {
      const scent = button.textContent.trim();
      const match = candles.find(c => c.name.toLowerCase() === scent.toLowerCase());
      if (match) {
        currentProduct = match;
        imageElement.src = match.image_url;
        imageElement.alt = match.name;
        priceElement.textContent = `$${Number(match.price).toFixed(2)}`;
        showRelatedProducts(match.name);
        document.getElementById('product-description').textContent = match.description || 'No description available.';
      }
    });
  });

  // --- Related Products Bar ---
  function showRelatedProducts(currentName = '') {
      function showRelatedProducts(currentName = '') {
        const relatedContainer = document.getElementById('related-container');
        if (!relatedContainer) { console.warn('[MM] #related-container missing'); return; }

        relatedContainer.innerHTML = '<!-- populated -->'; // quick marker
        const related = candles.filter(c => c.name?.toLowerCase() !== currentName.toLowerCase());
        // ... your existing item creation
      }
    const relatedContainer = document.getElementById('related-container');
    if (!relatedContainer) return;
    relatedContainer.innerHTML = '';
    const related = candles.filter(c => c.name.toLowerCase() !== currentName.toLowerCase());

    related.forEach(candle => {
      const itemDiv = document.createElement('div');
      itemDiv.className = 'related-item';
      itemDiv.innerHTML = `
        <img src="${candle.image_url}" alt="${candle.name}">
        <p>$${Number(candle.price).toFixed(2)}</p>
      `;
      itemDiv.addEventListener('click', () => {
        currentProduct = candle;
        imageElement.src = candle.image_url;
        imageElement.alt = candle.name;
        priceElement.textContent = `$${Number(candle.price).toFixed(2)}`;
        showRelatedProducts(candle.name);
        document.getElementById('product-description').textContent =
          candle.description || 'No description available.';
      });
      relatedContainer.appendChild(itemDiv);
    });
  }

  try {
    const resp = await fetch('header.html', { cache: 'no-store' });
    if (!resp.ok) throw new Error('header.html status '+resp.status);
    const headerHtml = await resp.text();

    const placeholder = document.getElementById('header-placeholder');
    if (!placeholder) {
      console.warn('[MM] #header-placeholder not found on this page.');
    } else {
      placeholder.innerHTML = headerHtml;
    }

    // If header didn’t inject, stop wiring to avoid null errors
    const cartButton   = document.getElementById('cart-button');
    const cartDropdown = document.getElementById('cart-dropdown');
    const cartItems    = document.getElementById('cart-items');
    const cartCount    = document.getElementById('cart-count');
    if (!cartButton || !cartDropdown || !cartItems || !cartCount) {
      console.warn('[MM] Header elements missing — skipping header wiring.');
    } else {

    // ===== Header UI wiring =====
    const cartButton   = document.getElementById('cart-button');
    const cartDropdown = document.getElementById('cart-dropdown');
    const cartItems    = document.getElementById('cart-items');
    const cartCount    = document.getElementById('cart-count');

    function updateCartCounter() {
      const cart = JSON.parse(localStorage.getItem('cart') || '[]');
      const total = cart.reduce((s, i) => s + i.quantity, 0);
      if (cartCount) cartCount.textContent = total;
      if (cartItems) {
        cartItems.innerHTML = cart.length
          ? cart.map(i => `<li>${i.name} — $${Number(i.price).toFixed(2)} × ${i.quantity}</li>`).join('')
          : '<li>Your cart is empty</li>';
      }
    }

    if (cartButton && cartDropdown) {
      cartButton.addEventListener('click', (e) => {
        e.stopPropagation();
        cartDropdown.classList.toggle('hidden');
        updateCartCounter();
      });

      // close when clicking elsewhere
      document.addEventListener('click', (e) => {
        if (!cartButton.contains(e.target) && !cartDropdown.contains(e.target)) {
          cartDropdown.classList.add('hidden');
        }
      });
    }

    // NOTE: you already declared addToCartBtn at the top — reuse it here
    if (addToCartBtn) {
      addToCartBtn.addEventListener('click', () => {
        const product = currentProduct || (Array.isArray(candles) ? candles[0] : null);
        if (!product) return alert('No candle selected yet!');

        const cart = JSON.parse(localStorage.getItem('cart') || '[]');
        const existing = cart.find(i => i.name === product.name);
        if (existing) existing.quantity++;
        else cart.push({ name: product.name, price: Number(product.price), image: product.image_url, quantity: 1 });

        localStorage.setItem('cart', JSON.stringify(cart));
        updateCartCounter();
      });
    }

    updateCartCounter();

    // Search toggle
    const searchToggle = document.getElementById('search-toggle');
    const searchBar    = document.getElementById('search-bar');
    const searchBtn    = document.getElementById('search-btn');
    const searchInput  = document.getElementById('search-input');

    if (searchToggle && searchBar) {
      searchToggle.addEventListener('click', () => {
        searchBar.classList.toggle('hidden');
      });
    }
    if (searchBtn && searchInput) {
      searchBtn.addEventListener('click', () => {
        const q = searchInput.value.trim();
        if (q) window.location.href = `search.html?query=${encodeURIComponent(q)}`;
      });
    }

    // Candles dropdown (ensure <ul class="dropdown-menu hidden"> in header.html)
    const candleToggle = document.getElementById('candles-toggle');
    const dropdownMenu = document.querySelector('.dropdown-menu');

    if (candleToggle && dropdownMenu) {
      candleToggle.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        dropdownMenu.classList.toggle('show');
        dropdownMenu.classList.toggle('hidden');
      });

      document.addEventListener('click', (e) => {
        if (!candleToggle.contains(e.target) && !dropdownMenu.contains(e.target)) {
          dropdownMenu.classList.remove('show');
          dropdownMenu.classList.add('hidden');
        }
      });
    }

  }
  } catch (err) {
    console.error('[MM] Failed to load header:', err);
  }

}); // ✅ closes DOMContentLoaded