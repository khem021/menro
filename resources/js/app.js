import './bootstrap';
import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

// Bundled rather than loaded from a CDN, which a venue firewall can block.
// The chart blocks in the views build their charts on DOMContentLoaded /
// livewire:load, both of which fire after this deferred module runs, so they
// still find the global they expect.
window.Chart = Chart;

window.Alpine = Alpine;
Alpine.start();

// Dark mode toggle — persist preference in localStorage
const theme = localStorage.getItem('theme');
if (theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    document.documentElement.classList.add('dark');
} else {
    document.documentElement.classList.remove('dark');
}
