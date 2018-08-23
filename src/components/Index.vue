<template>
  <div class="container d-flex flex-column justify-content-center align-items-center h-100">
    <div class="form-group">
      <label for="comps">Please choose a competition:</label>
      <model-select
        :options="options"
        v-model="comp"
        placeholder="Type the name to search">
       </model-select>
    </div>
    <Refresh @refresh="refresh" label="Refresh Competitions" />
  </div>
</template>

<script>
import { ModelSelect } from 'vue-search-select'
import store from 'store'
import { client } from '../axios'

export default {
  name: 'Index',
  data () {
    return {
      comp: '',
      comps: []
    }
  },
  computed: {
    options() {
      return this.comps.map(comp => {
        return {
          value: comp.cid,
          text: comp.name
        }
      })
    }
  },
  watch: {
    comp(cid) {
      this.$router.push('/comp/' + cid)
    }
  },
  methods: {
    async refresh() {
      this.$parent.loading(true)
      try {
        const res = await client.get('', {
          params: {
            action: 'list'
          }
        })
        this.comps = res.data
        store.set('comps', res.data)
      } catch (e) {
        alert(e)
      }
      this.$parent.loading(false)
    }
  },
  mounted() {
    if (store.get('comps')) {
      this.comps = store.get('comps')
    } else {
      this.refresh()
    }
  },
  filters: {
    translate(name) {
      return name.split('_').map(str => str.slice(0, 1).toUpperCase() + str.slice(1)).join(' ')
    }
  },
  components: {
    ModelSelect
  }
}
</script>

<style scoped>
</style>
