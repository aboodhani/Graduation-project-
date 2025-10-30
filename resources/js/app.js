// --- CSS ---
// Import Bootstrap CSS
import 'bootstrap/dist/css/bootstrap.min.css';

// Import Tailwind's entrypoint
import '../css/app.css';

// Import your custom styles (loads last to override)
import '../css/style.css';


// --- JAVASCRIPT ---

// Import Bootstrap's JS bundle (includes Popper for dropdowns/modals)
import 'bootstrap/dist/js/bootstrap.bundle.min.js';

// Import Laravel's bootstrap.js (for Axios, etc.)
import './bootstrap';

// Import Alpine
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();