import '../css/app.css';

// Turbo Drive first: it starts driving navigation as soon as it's
// imported, and its `turbo:load` event is what Alpine's own MutationObserver
// relies on picking up newly-swapped-in `x-data` elements.
import './spa.js';

import Alpine from 'alpinejs';
import { negarinOtp } from './otp.js';
import { negarinCustomOrder } from './custom-order.js';
import { negarinSearch } from './search.js';
import './ajax-cart.js';
import './cart.js';
import './checkout.js';

window.Alpine = Alpine;
Alpine.data('negarinOtp', negarinOtp);
Alpine.data('negarinCustomOrder', negarinCustomOrder);
Alpine.data('negarinSearch', negarinSearch);
Alpine.start();
