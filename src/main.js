import Vue from 'vue'
import functions from './functions'
import RAAStatsViz from './components/RAAStatsViz'

import { library } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faShare, faShareAlt, faShareAltSquare } from '@fortawesome/free-solid-svg-icons'

library.add(faShare)
library.add(faShareAlt)
library.add(faShareAltSquare)

Vue.component('raa-stats-viz', RAAStatsViz)
Vue.component('fa-icon', FontAwesomeIcon)

Vue.prototype.RAA = functions
window.RAA = functions
window.Vue = Vue
