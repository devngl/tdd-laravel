require('./bootstrap');

window.Vue = require('vue');

Vue.component('ticket-checkout', require('./components/TicketCheckout.vue').default);

const app = new Vue({
    el: '#app',
});
