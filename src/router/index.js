import Vue from 'vue'
import Router from 'vue-router'
import Index from '@/components/Index'
import Competition from '@/components/Competition'

Vue.use(Router)

export default new Router({
  routes: [
    {
      path: '/',
      name: 'Index',
      component: Index
    },
    {
      path: '/comp/:id',
      name: 'Competition',
      component: Competition
    },
    {
      path: '/comp/:id/:cat/:rnd',
      name: 'Results',
      component: Competition
    }
  ]
})
