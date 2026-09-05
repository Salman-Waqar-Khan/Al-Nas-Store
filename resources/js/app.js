import './bootstrap';

const catalog = window.storeProducts || [];
let cart = JSON.parse(localStorage.getItem('your-store-cart') || '[]');
const money = value => `৳${Number(value).toLocaleString('en-BD')}`;
const drawer = document.querySelector('#cart-drawer');
const backdrop = document.querySelector('#cart-backdrop');
const findProduct = id => catalog.find(product => product.id === Number(id));
const findVariant = (product, variantId) => product?.variants?.find(variant => variant.id === Number(variantId));
const itemDetails = item => {
    const product = findProduct(item.id);
    const variant = findVariant(product, item.variant_id);
    return product ? {product, variant, stock: Number(variant?.stock ?? product.stock), price: Number(variant?.price ?? product.price), image: variant?.image ?? product.image} : null;
};
const sameCartItem = (item, id, variantId) => item.id === Number(id) && Number(item.variant_id || 0) === Number(variantId || 0);

cart = cart.flatMap(item => {
    const details = itemDetails(item);
    if (!details || (details.product.has_variants && !details.variant) || details.stock < 1) return [];
    return [{id: details.product.id, variant_id: details.variant?.id ?? null, quantity: Math.min(10, details.stock, Math.max(1, Number(item.quantity) || 1))}];
});

document.querySelectorAll('.product-card').forEach((card, index) => {
    const product = catalog[index];
    if (!product) return;
    const heading = card.querySelector('h3');
    if (heading && product.slug) {
        const link = document.createElement('a');
        link.href = `/products/${encodeURIComponent(product.slug)}`;
        link.className = 'transition hover:text-amber-700';
        link.textContent = heading.textContent;
        heading.replaceChildren(link);
    }
    if (product.has_variants) {
        const displayedPrice = heading?.parentElement?.querySelector('p');
        if (displayedPrice) displayedPrice.textContent = Number(product.price_min) === Number(product.price_max)
            ? money(product.price_min)
            : `${money(product.price_min)} – ${money(product.price_max)}`;
        card.querySelectorAll('[data-add]').forEach(button => {
            button.removeAttribute('data-add');
            button.dataset.chooseSize = `/products/${encodeURIComponent(product.slug)}`;
            button.textContent = 'Choose size';
        });
    }
    if (Number(product.stock) < 1) {
        card.querySelectorAll('[data-add]').forEach(button => { button.disabled = true; button.textContent = 'Out of stock'; button.classList.add('cursor-not-allowed', 'opacity-60'); });
        const media = card.querySelector('.product-media');
        const badge = document.createElement('span');
        badge.className = 'absolute bottom-3 left-3 rounded-full bg-rose-100 px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-rose-800';
        badge.textContent = 'Out of stock';
        media?.appendChild(badge);
    }
});

function save() { localStorage.setItem('your-store-cart', JSON.stringify(cart)); renderCart(); }
function openCart() { drawer?.classList.remove('translate-x-full'); backdrop?.classList.remove('hidden'); document.body.classList.add('overflow-hidden'); }
function closeCart() { drawer?.classList.add('translate-x-full'); backdrop?.classList.add('hidden'); document.body.classList.remove('overflow-hidden'); }

function renderCart() {
    const count = cart.reduce((n, item) => n + item.quantity, 0);
    document.querySelectorAll('[data-cart-count]').forEach(el => el.textContent = count);
    const list = document.querySelector('#cart-items');
    const empty = document.querySelector('#cart-empty');
    if (!list) return;
    list.innerHTML = cart.map(item => {
        const details = itemDetails(item);
        if (!details) return '';
        const {product, variant, price, image, stock} = details;
        const maxQuantity = Math.min(10, stock);
        const key = `${item.id}:${item.variant_id || 0}`;
        return `<div class="flex gap-4 border-b border-stone-200 pb-5">
            <img src="${image}" class="h-24 w-20 rounded-xl object-cover" alt="${product.name}">
            <div class="min-w-0 flex-1"><p class="font-semibold">${product.name}</p>${variant ? `<p class="text-xs font-bold text-amber-700">${variant.label}</p>` : ''}<p class="mt-1 text-sm text-stone-500">${money(price)}</p>
            <div class="mt-3 flex items-center gap-3"><button data-minus="${key}" class="h-8 w-8 rounded-full border">−</button><span>${item.quantity}</span><button data-plus="${key}" ${item.quantity >= maxQuantity ? 'disabled' : ''} class="h-8 w-8 rounded-full border disabled:cursor-not-allowed disabled:opacity-40">+</button><button data-remove="${key}" class="ml-auto text-xs underline">Remove</button></div></div>
        </div>`;
    }).join('');
    empty?.classList.toggle('hidden', cart.length > 0);
    const subtotal = cart.reduce((sum, item) => sum + (itemDetails(item)?.price || 0) * item.quantity, 0);
    const subtotalElement = document.querySelector('#cart-subtotal');
    if (subtotalElement) subtotalElement.textContent = money(subtotal);
    const checkoutButton = document.querySelector('#checkout-button');
    if (checkoutButton) checkoutButton.disabled = cart.length === 0;
}

document.addEventListener('click', event => {
    const chooseSize = event.target.closest('[data-choose-size]');
    if (chooseSize) window.location.href = chooseSize.dataset.chooseSize;
    const buyNow = event.target.closest('[data-buy-now]');
    if (buyNow && !buyNow.disabled) {
        const id = Number(buyNow.dataset.buyNow);
        const product = findProduct(id);
        const variantId = Number(buyNow.dataset.variantId || 0) || null;
        const variant = findVariant(product, variantId);
        const availableStock = Number(variant?.stock ?? product?.stock ?? 0);
        const quantityInput = buyNow.dataset.quantityInput ? document.querySelector(buyNow.dataset.quantityInput) : null;
        const quantity = Math.min(10, availableStock, Math.max(1, Number(quantityInput?.value || 1)));
        if (!product || (product.has_variants && !variant) || quantity < 1) return;
        cart = [{id, variant_id: variantId, quantity}];
        localStorage.setItem('your-store-cart', JSON.stringify(cart));
        localStorage.setItem('alnas-buy-now', '1');
        window.location.href = '/';
        return;
    }
    const add = event.target.closest('[data-add]');
    if (add && !add.disabled) {
        const id = Number(add.dataset.add);
        const product = findProduct(id);
        const variantId = Number(add.dataset.variantId || 0) || null;
        const variant = findVariant(product, variantId);
        const availableStock = Number(variant?.stock ?? product?.stock ?? 0);
        if (!product || (product.has_variants && !variant) || availableStock < 1) return;
        const quantityInput = add.dataset.quantityInput ? document.querySelector(add.dataset.quantityInput) : null;
        const requestedQuantity = Math.max(1, Number(quantityInput?.value || 1));
        const found = cart.find(item => sameCartItem(item, id, variantId));
        const nextQuantity = Math.min(10, availableStock, (found?.quantity || 0) + requestedQuantity);
        found ? found.quantity = nextQuantity : cart.push({id, variant_id: variantId, quantity: nextQuantity});
        save(); openCart();
    }
    if (event.target.closest('[data-open-cart]')) openCart();
    if (event.target.closest('[data-close-cart]')) closeCart();
    const plus = event.target.closest('[data-plus]'); if (plus && !plus.disabled) { const [id, variantId] = plus.dataset.plus.split(':').map(Number); const item = cart.find(i => sameCartItem(i,id,variantId)); item.quantity = Math.min(10, itemDetails(item)?.stock || 0, item.quantity + 1); save(); }
    const minus = event.target.closest('[data-minus]'); if (minus) { const [id, variantId] = minus.dataset.minus.split(':').map(Number); const item = cart.find(i => sameCartItem(i,id,variantId)); item.quantity--; if (item.quantity < 1) cart = cart.filter(i => i !== item); save(); }
    const remove = event.target.closest('[data-remove]'); if (remove) { const [id, variantId] = remove.dataset.remove.split(':').map(Number); cart = cart.filter(i => !sameCartItem(i,id,variantId)); save(); }
});

document.querySelectorAll('[data-variant-option]').forEach(option => option.addEventListener('click', () => {
    document.querySelectorAll('[data-variant-option]').forEach(button => {
        button.classList.remove('border-[#081426]', 'bg-[#081426]', 'text-white');
        button.classList.add('border-stone-300');
    });
    option.classList.add('border-[#081426]', 'bg-[#081426]', 'text-white');
    option.classList.remove('border-stone-300');
    const price = Number(option.dataset.price);
    const oldPrice = Number(option.dataset.oldPrice || 0);
    const stock = Number(option.dataset.stock);
    const priceElement = document.querySelector('#product-price');
    const oldPriceElement = document.querySelector('#product-old-price');
    const imageElement = document.querySelector('#product-main-image');
    const stockElement = document.querySelector('#product-stock');
    const addButton = document.querySelector('[data-product-detail] [data-add]');
    const buyNowButton = document.querySelector('[data-product-detail] [data-buy-now]');
    const quantityInput = document.querySelector('#product-quantity');
    const sizePrompt = document.querySelector('#size-prompt');
    if (priceElement) priceElement.textContent = money(price);
    if (oldPriceElement) { oldPriceElement.textContent = money(oldPrice); oldPriceElement.classList.toggle('hidden', !oldPrice); }
    if (imageElement) imageElement.src = option.dataset.image;
    if (stockElement) {
        stockElement.textContent = stock > 0 ? `In stock · ${stock} available` : 'Out of stock';
        stockElement.className = `inline-flex rounded-full px-3 py-1 text-xs font-bold ${stock > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'}`;
    }
    if (addButton) { addButton.dataset.variantId = option.dataset.variantId; addButton.disabled = stock < 1; addButton.textContent = stock > 0 ? 'Add to bag' : 'Out of stock'; }
    if (buyNowButton) { buyNowButton.dataset.variantId = option.dataset.variantId; buyNowButton.disabled = stock < 1; buyNowButton.textContent = stock > 0 ? 'Buy now' : 'Out of stock'; }
    if (quantityInput) { quantityInput.disabled = stock < 1; quantityInput.max = Math.min(10, Math.max(1, stock)); quantityInput.value = 1; }
    if (sizePrompt) sizePrompt.textContent = `Selected size: ${option.textContent.trim()}`;
}));

document.querySelector('#checkout-button')?.addEventListener('click', () => { closeCart(); document.querySelector('#checkout-modal').classList.remove('hidden'); document.querySelector('#checkout-items').value = JSON.stringify(cart); });
document.querySelectorAll('[data-close-checkout]').forEach(el => el.addEventListener('click', () => document.querySelector('#checkout-modal').classList.add('hidden')));
document.querySelector('#checkout-form')?.addEventListener('submit', event => { if (!cart.length) event.preventDefault(); });

const observer = new IntersectionObserver(entries => entries.forEach(e => e.isIntersecting && e.target.classList.add('visible')), {threshold: .12});
document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
renderCart();

if (localStorage.getItem('alnas-buy-now') === '1' && document.querySelector('#checkout-modal')) {
    localStorage.removeItem('alnas-buy-now');
    document.querySelector('#checkout-items').value = JSON.stringify(cart);
    document.querySelector('#checkout-modal').classList.remove('hidden');
}

const recentOrdersHeading = [...document.querySelectorAll('h2')].find(heading => heading.textContent.trim() === 'Recent orders');
if (recentOrdersHeading && !document.querySelector('a[href="/admin/orders"]')) {
    const link = document.createElement('a');
    link.href = '/admin/orders';
    link.className = 'ml-3 inline-flex rounded-full border border-stone-300 px-4 py-2 text-xs font-bold transition hover:border-stone-900';
    link.textContent = 'View all orders';
    recentOrdersHeading.parentElement?.appendChild(link);
}

const addProductForm = document.querySelector('form[action$="/admin/products"]');
if (addProductForm && !addProductForm.querySelector('[data-initial-variants]')) {
    const submitButton = [...addProductForm.querySelectorAll('button')].find(button => button.textContent.trim() === 'Add product');
    const variantSection = document.createElement('section');
    variantSection.dataset.initialVariants = '';
    variantSection.className = 'rounded-2xl border border-amber-200 bg-amber-50/60 p-4 sm:col-span-2';
    variantSection.innerHTML = `
        <label class="flex cursor-pointer items-start gap-3">
            <input type="checkbox" data-enable-initial-variants class="mt-1">
            <span><b class="block">Add perfume/attar sizes now</b><small class="text-stone-600">Create 3.5ml, 6ml and 12ml prices, stock and photos with this product.</small></span>
        </label>
        <div data-initial-variant-fields class="mt-5 hidden space-y-4">
            ${['3.5ml','6ml','12ml'].map((label,index) => `<div class="grid gap-3 rounded-xl bg-white p-4 sm:grid-cols-5">
                <label class="text-xs font-bold">Size<input name="variants[${index}][label]" value="${label}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm font-normal"></label>
                <label class="text-xs font-bold">Price<input type="number" min="0" name="variants[${index}][price]" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm font-normal"></label>
                <label class="text-xs font-bold">Old price<input type="number" min="0" name="variants[${index}][old_price]" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm font-normal"></label>
                <label class="text-xs font-bold">Stock<input type="number" min="0" name="variants[${index}][stock]" value="10" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm font-normal"></label>
                <label class="text-xs font-bold">Own photo<input type="file" name="variants[${index}][image_file]" accept="image/jpeg,image/png,image/webp" class="mt-1 block w-full text-[11px] font-normal"></label>
            </div>`).join('')}
            <p class="text-xs text-stone-600">You can add more sizes later from <b>Manage sizes</b>.</p>
        </div>`;
    submitButton?.before(variantSection);
    const toggle = variantSection.querySelector('[data-enable-initial-variants]');
    const fields = variantSection.querySelector('[data-initial-variant-fields]');
    const basePrice = addProductForm.querySelector('input[name="price"]');
    const baseStock = addProductForm.querySelector('input[name="stock"]');
    fields.querySelectorAll('input').forEach(input => input.disabled = true);
    toggle.addEventListener('change', () => {
        fields.classList.toggle('hidden', !toggle.checked);
        fields.querySelectorAll('input').forEach(input => input.disabled = !toggle.checked);
        fields.querySelectorAll('input[name$="[price]"], input[name$="[stock]"], input[type="file"]').forEach(input => input.required = toggle.checked);
        if (toggle.checked) {
            basePrice.dataset.previousValue = basePrice.value;
            baseStock.dataset.previousValue = baseStock.value;
            basePrice.value = 0;
            baseStock.value = 0;
            basePrice.type = 'hidden';
            baseStock.type = 'hidden';
        } else {
            basePrice.value = basePrice.dataset.previousValue || '';
            baseStock.value = baseStock.dataset.previousValue || '10';
            basePrice.type = 'number';
            baseStock.type = 'number';
        }
    });
}

document.querySelectorAll('form[action*="/admin/products/"][method="POST"]').forEach(form => {
    if (!form.querySelector('input[name="_method"][value="DELETE"]')) return;
    const match = form.action.match(/\/admin\/products\/(\d+)$/);
    const row = form.closest('tr');
    const category = row?.children?.[1]?.textContent || '';
    if (!match || !row || !/attar|perfume/i.test(category) || row.querySelector('[data-manage-variants]')) return;
    const link = document.createElement('a');
    link.href = `/admin/products/${match[1]}/variants`;
    link.dataset.manageVariants = '';
    link.className = 'mt-2 block text-xs font-bold text-blue-700 hover:underline';
    link.textContent = 'Manage sizes';
    form.parentElement?.insertBefore(link, form);
});

let pendingDeleteForm = null;

function ensureDeleteModal() {
    let modal = document.querySelector('#admin-delete-modal');
    if (modal) return modal;

    modal = document.createElement('div');
    modal.id = 'admin-delete-modal';
    modal.className = 'fixed inset-0 z-[100] hidden items-center justify-center bg-black/50 p-4 backdrop-blur-sm';
    modal.innerHTML = `<div data-delete-backdrop class="absolute inset-0"></div>
        <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl sm:p-8" role="dialog" aria-modal="true" aria-labelledby="delete-modal-title">
            <div class="grid h-12 w-12 place-items-center rounded-full bg-red-100 text-2xl text-red-700">!</div>
            <h2 id="delete-modal-title" class="font-display mt-5 text-3xl font-semibold">Delete this item?</h2>
            <p class="mt-2 text-sm leading-6 text-stone-600">You are about to permanently delete <strong data-delete-name>this item</strong>. This action cannot be undone.</p>
            <div class="mt-7 flex justify-end gap-3">
                <button type="button" data-cancel-delete class="rounded-full border border-stone-300 px-5 py-2.5 text-sm font-bold">Cancel</button>
                <button type="button" data-confirm-delete class="rounded-full bg-red-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-red-700">Yes, delete</button>
            </div>
        </div>`;
    document.body.appendChild(modal);
    return modal;
}

function closeDeleteModal() {
    const modal = document.querySelector('#admin-delete-modal');
    modal?.classList.add('hidden');
    modal?.classList.remove('flex');
    pendingDeleteForm = null;
}

document.addEventListener('click', event => {
    const button = event.target.closest('form[action*="/admin/products/"] button, form[action*="/admin/categories/"] button');
    if (!button || button.textContent.trim() !== 'Delete') return;
    event.preventDefault();
    event.stopImmediatePropagation();

    pendingDeleteForm = button.closest('form');
    const rowName = pendingDeleteForm.closest('tr')?.querySelector('td b')?.textContent.trim();
    const categoryName = pendingDeleteForm.parentElement?.querySelector('span')?.childNodes[0]?.textContent.trim();
    const modal = ensureDeleteModal();
    modal.querySelector('[data-delete-name]').textContent = rowName || categoryName || 'this item';
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    modal.querySelector('[data-cancel-delete]').focus();
}, true);

document.addEventListener('click', event => {
    if (event.target.closest('[data-cancel-delete]') || event.target.matches('[data-delete-backdrop]')) closeDeleteModal();
    if (event.target.closest('[data-confirm-delete]') && pendingDeleteForm) pendingDeleteForm.submit();
});

document.addEventListener('keydown', event => {
    if (event.key === 'Escape') {
        closeDeleteModal();
        document.querySelectorAll('details[open]').forEach(details => details.removeAttribute('open'));
    }
});
