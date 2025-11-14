document.addEventListener('DOMContentLoaded', async () => {
  window.addEventListener('error', (e) => {
    console.error('[MM] Global error:', e.message, `${e.filename}:${e.lineno}`);
  });

  let candles = [];
  let currentProduct = null;

  // --- Immediate references ---
  const imageElement = document.querySelector('#picbox .image-fit, #picbox img');
  const priceElement = document.querySelector('.buy-box .price');
  const scentButtons = document.querySelectorAll('.scent-selector button');
  const addToCartBtn = document.querySelector('.buy-box .add-to-cart-btn');

  /* ===== [PATCH] absolute URLs + safe fetch helpers ===== */
  const ORIGIN = window.location.origin;      // e.g., http://localhost
  const ROOT   = '/MamaMoon/';                // ← adjust only if your site root is different

  const URLS = {
    header:  ORIGIN + ROOT + 'header.html',
    candles: ORIGIN + ROOT + 'nuerons.php'
  };

  async function fetchText(url, ms = 8000) {
    const ctrl = new AbortController();
    const t = setTimeout(() => ctrl.abort(), ms);
    const res = await fetch(url, { signal: ctrl.signal, cache: 'no-store' });
    clearTimeout(t);
    if (!res.ok) throw new Error(`${url} → ${res.status}`);
    return res.text();
  }

  async function fetchJSON(url, ms = 8000) {
    const txt = await fetchText(url, ms);
    if (txt.trim().startsWith('<')) throw new Error(`${url} returned HTML, not JSON`);
    return JSON.parse(txt);
  }
  /* ===== [/PATCH] ===== */

  // --- Fetch product data ---
  try {
    candles = await fetchJSON(URLS.candles);
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
        if (imageElement) {
          imageElement.src = match.image_url;
          imageElement.alt = match.name;
        }
        if (priceElement) {
          priceElement.textContent = `$${Number(match.price).toFixed(2)}`;
        }
        showRelatedProducts(match.name);
        const descEl = document.getElementById('product-description');
        if (descEl) descEl.textContent = match.description || 'No description available.';
      }
    });
  });

  // --- Related Products Bar ---
  function showRelatedProducts(currentName = '') {
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
        if (imageElement) {
          imageElement.src = candle.image_url;
          imageElement.alt = candle.name;
        }
        if (priceElement) {
          priceElement.textContent = `$${Number(candle.price).toFixed(2)}`;
        }
        showRelatedProducts(candle.name);
        const descEl = document.getElementById('product-description');
        if (descEl) descEl.textContent = candle.description || 'No description available.';
      });
      relatedContainer.appendChild(itemDiv);
    });
  }

  /* ===== [PATCH] robust header loader (absolute path + timeout) ===== */
  try {
    const headerHtml = await fetchText(URLS.header);
    const placeholder = document.getElementById('header-placeholder');
    if (!placeholder) {
      console.warn('[MM] #header-placeholder not found on this page');
    } else {
      placeholder.innerHTML = headerHtml;

      // ===== Wire header UI only AFTER it exists =====
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
        document.addEventListener('click', (e) => {
          if (!cartButton.contains(e.target) && !cartDropdown.contains(e.target)) {
            cartDropdown.classList.add('hidden');
          }
        });
      }

      // keep using your existing page-level "add to cart" button
      const addToCartBtnPage = document.querySelector('.add-to-cart-btn');
      if (addToCartBtnPage) {
        addToCartBtnPage.addEventListener('click', () => {
          const product = (typeof currentProduct !== 'undefined' && currentProduct) ||
                        (Array.isArray(candles) ? candles[0] : null);
          if (!product) return alert('No candle selected yet!');
          const cart = JSON.parse(localStorage.getItem('cart') || '[]');
          const existing = cart.find(i => i.name === product.name);
          if (existing) existing.quantity++;
          else cart.push({ name: product.name, price: Number(product.price), image: product.image_url, quantity: 1 });
          localStorage.setItem('cart', JSON.stringify(cart));
          updateCartCounter();
        });
      }

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

      // Candles dropdown (header.html must have <ul class="dropdown-menu hidden">)
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

      updateCartCounter();
    }
  } catch (err) {
    console.error('[MM] header fetch failed:', err);
  }
  /* ===== [/PATCH] ===== */

}); // ✅ closes DOMContentLoaded