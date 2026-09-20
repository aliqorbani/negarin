import '../css/app.css';

// Turbo Drive first: it starts driving navigation as soon as it's
// imported, and its `turbo:load` event is what Alpine's own MutationObserver
// relies on picking up newly-swapped-in `x-data` elements.
import './spa.js';

import Alpine from 'alpinejs';
import { negarinOtp } from './otp.js';
import { negarinSizeSelect } from './size-select.js';
import { negarinSearch } from './search.js';
import { negarinToastStore } from './toast.js';
import { negarinCheckoutForm } from './checkout.js';
import './ajax-cart.js';
import './cart.js';
import './header-offset.js';

window.Alpine = Alpine;
Alpine.store('toast', negarinToastStore());
Alpine.data('negarinOtp', negarinOtp);
Alpine.data('negarinSizeSelect', negarinSizeSelect);
Alpine.data('negarinCheckoutForm', negarinCheckoutForm);
Alpine.data('negarinSearch', negarinSearch);
Alpine.start();