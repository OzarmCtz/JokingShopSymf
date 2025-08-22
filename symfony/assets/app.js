import "./bootstrap.js";
import "./styles/app.css";

// Import ESM de Bootstrap (via importmap)
import "bootstrap";

function initBootstrapDropdowns() {
    if (!window.bootstrap) return;
    document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach((el) => {
        window.bootstrap.Dropdown.getInstance(el)?.dispose();
        window.bootstrap.Dropdown.getOrCreateInstance(el);
    });
}

// Polyfill pour le lazy loading sur les navigateurs plus anciens
function initLazyLoadingPolyfill() {
    // Vérifier si le navigateur supporte nativement loading="lazy"
    if ("loading" in HTMLImageElement.prototype) {
        return; // Le navigateur supporte le lazy loading natif
    }

    // Polyfill pour les navigateurs plus anciens
    if ("IntersectionObserver" in window) {
        const imageObserver = new IntersectionObserver(
            (entries, observer) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        // L'image est déjà chargée avec src, pas besoin de data-src
                        img.classList.add("lazy-loaded");
                        observer.unobserve(img);
                    }
                });
            },
            {
                rootMargin: "50px 0px",
                threshold: 0.1,
            }
        );

        // Observer toutes les images avec loading="lazy"
        document.querySelectorAll('img[loading="lazy"]').forEach((img) => {
            imageObserver.observe(img);
        });
    }
}

document.addEventListener("DOMContentLoaded", () => {
    initBootstrapDropdowns();
    initLazyLoadingPolyfill();
});

document.addEventListener("turbo:load", () => {
    initBootstrapDropdowns();
    initLazyLoadingPolyfill();
});

console.log("This log comes from assets/app.js - welcome to AssetMapper! 🎉");
