import Vue from 'vue'
import App from './App'
import ToggleButton from 'vue-js-toggle-button'
import VueTouch from 'vue-touch'
import router from './router'

import 'bootstrap/dist/css/bootstrap.css'

import Refresh from './components/Refresh'

Vue.config.productionTip = false
Vue.use(ToggleButton)
Vue.use(VueTouch, {
  name: 'v-touch'
})
Vue.component('Refresh', Refresh)

new Vue({
  el: '#app',
  router,
  components: { App },
  template: '<App/>'
})
