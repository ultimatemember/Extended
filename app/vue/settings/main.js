import { createApp } from 'vue'
import loadPlugins from '../plugins'
import { createRouter, createWebHashHistory } from 'vue-router'
import App from './App.vue'
import SingleExt from './SingleExt.vue'
import UMComponents from './UMComponents.vue'
import NProgress from 'nprogress'
import 'nprogress/nprogress.css'

// Vuetify
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import * as labsComponents from 'vuetify/labs/components'

let app = createApp({ ...App, name: 'UMExtended/Settings', data(){
        return um_extended;
    } 
})

app = loadPlugins(app)

const vuetify = createVuetify({
  components,
  directives,
  labsComponents
})

import { VSkeletonLoader } from 'vuetify/labs/VSkeletonLoader'

app.component('VSkeletonLoader', VSkeletonLoader ); 


app.use(vuetify)

const router = createRouter({
  history: createWebHashHistory(),
  routes: [
	{ 
		path: '/', 
		component: UMComponents, 
	},
    { path: '/search/:keyword', component: UMComponents},
    { path: '/ext/:id', component: SingleExt },
  ],
})

NProgress.configure({ parent: '.um-extended-settings',  showSpinner: true, asing: 'ease', speed: 50,  minimum: 0.6, trickleSpeed: 500  });

router.beforeEach((to, from, next)=>{
	  NProgress.inc(0.8)
    NProgress.start()
	  return next();
})
  

router.afterEach(()=> {
    setTimeout(function(){ 
	NProgress.done()
	NProgress.remove()
}, 500);
  })

// Use the router.
app.use(router)

// // Set state from the window object.
app.mount('#um-extended-settings')


export default app