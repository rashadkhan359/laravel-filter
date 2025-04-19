/**
 * Laravel Filter - JavaScript Components
 */

// Vue Components
import VueFilterBuilder from './components/vue/FilterBuilder.vue';

// React Components
import ReactFilterBuilder from './components/FilterBuilder.jsx';

// Export all components
export {
  VueFilterBuilder,
  ReactFilterBuilder
};

// If using window global, make components available
if (typeof window !== 'undefined') {
  window.LaravelFilter = {
    VueFilterBuilder,
    ReactFilterBuilder
  };
}
