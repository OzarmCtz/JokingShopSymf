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

document.addEventListener("DOMContentLoaded", initBootstrapDropdowns);
document.addEventListener("turbo:load", initBootstrapDropdowns);

console.log("This log comes from assets/app.js - welcome to AssetMapper! 🎉");
