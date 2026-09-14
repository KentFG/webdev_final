document.addEventListener('DOMContentLoaded', () => {
    // Mobile navigation toggle
    const toggle = document.querySelector('.menu-toggle');
    const nav = document.querySelector('.site-nav');
    const links = document.querySelectorAll('.nav-link');
    const subscribeForm = document.querySelector('.subscribe-form');
    const formStatus = document.querySelector('.form-status');

    if (toggle && nav) {
        toggle.addEventListener('click', () => {
            const isOpen = nav.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', isOpen);
            toggle.setAttribute('aria-label', isOpen ? 'Close menu' : 'Open menu');
        });
        links.forEach(link => link.addEventListener('click', () => {
            nav.classList.remove('is-open');
            toggle.setAttribute('aria-expanded', 'false');
            toggle.setAttribute('aria-label', 'Open menu');
        }));
    }

    // Active navigation highlighting
    const sections = [...document.querySelectorAll('main section[id]')];
    if ('IntersectionObserver' in window && sections.length > 0) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    links.forEach(l => l.classList.remove('is-active'));
                    const activeLink = document.querySelector(`.nav-link[href="#${entry.target.id}"]`);
                    if (activeLink) activeLink.classList.add('is-active');
                }
            });
        }, { rootMargin: '-40% 0px -50% 0px' });
        sections.forEach(s => observer.observe(s));
    }

    // Newsletter subscription form
    if (subscribeForm) {
        subscribeForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const input = subscribeForm.querySelector('input[type=email]');
            if (!input.value.trim() || !input.checkValidity()) {
                if (formStatus) formStatus.textContent = 'Please enter a valid email address.';
                input.focus();
                return;
            }
            if (formStatus) formStatus.textContent = 'Thank you. You are on the list.';
            subscribeForm.reset();
        });
    }

    // Toast notification element
    let toastTimeout = null;
    function showToast(message, viewCartUrl = 'customer/cart.php') {
        let toast = document.getElementById('site-toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'site-toast';
            toast.className = 'site-toast';
            document.body.appendChild(toast);
        }
        toast.innerHTML = `<span>? ${message}</span> <a href="${viewCartUrl}" class="toast-btn">VIEW BAG</a>`;
        toast.classList.add('is-visible');
        if (toastTimeout) clearTimeout(toastTimeout);
        toastTimeout = setTimeout(() => {
            toast.classList.remove('is-visible');
        }, 3200);
    }

    // Seamless "ADD TO BAG" without scrolling or full page reload
    const cartForms = document.querySelectorAll('form[action*="cart_process.php"]');
    cartForms.forEach(form => {
        const actionInput = form.querySelector('input[name="action"]');
        if (actionInput && actionInput.value === 'add') {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const btn = form.querySelector('button[type="submit"]');
                const originalText = btn ? btn.textContent : 'ADD TO BAG';

                if (btn) {
                    btn.disabled = true;
                    btn.textContent = 'ADDING...';
                }

                try {
                    const formData = new FormData(form);
                    formData.append('ajax', '1');

                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    if (response.ok) {
                        const data = await response.json();
                        // Update cart badge counts across the page
                        document.querySelectorAll('.bag-count').forEach(badge => {
                            badge.textContent = data.cart_count;
                        });
                        // Update any other mini-header links showing BAG (count)
                        document.querySelectorAll('a[href*="cart.php"]').forEach(cartLink => {
                            if (cartLink.textContent.includes('BAG (')) {
                                cartLink.textContent = `BAG (${data.cart_count})`;
                            }
                        });

                        if (btn) {
                            btn.textContent = 'ADDED ?';
                            btn.style.backgroundColor = '#2e7d32';
                            btn.style.borderColor = '#2e7d32';
                            btn.style.color = '#fff';
                        }

                        const isCustomerSubdir = window.location.pathname.includes('/customer/');
                        const bagPath = isCustomerSubdir ? 'cart.php' : 'customer/cart.php';
                        showToast(data.message || 'Product added to your bag.', bagPath);

                        setTimeout(() => {
                            if (btn) {
                                btn.disabled = false;
                                btn.textContent = originalText;
                                btn.style.backgroundColor = '';
                                btn.style.borderColor = '';
                                btn.style.color = '';
                            }
                        }, 1800);
                    } else {
                        // Fallback to standard submission
                        form.submit();
                    }
                } catch (err) {
                    console.error('Cart add error:', err);
                    form.submit();
                }
            });
        }
    });

    // If page loaded with #collection, avoid smooth scroll jump from top
    if (window.location.hash === '#collection') {
        const target = document.getElementById('collection');
        if (target) {
            target.scrollIntoView({ behavior: 'instant', block: 'start' });
        }
    }
});
