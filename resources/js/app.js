import './bootstrap';

import Alpine from 'alpinejs';

// ✅ START: FIX - Import Chart.js and make it globally available
import Chart from 'chart.js/auto';
window.Chart = Chart;
// ✅ END: FIX

window.Alpine = Alpine;

Alpine.start();