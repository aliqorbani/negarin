import '../css/app.css';

import Alpine from 'alpinejs';
import { negarinOtp } from './otp.js';
import { negarinCustomOrder } from './custom-order.js';
import './ajax-cart.js';
import './cart.js';
import './checkout.js';

window.Alpine = Alpine;
Alpine.data('negarinOtp', negarinOtp);
Alpine.data('negarinCustomOrder', negarinCustomOrder);
Alpine.start();
