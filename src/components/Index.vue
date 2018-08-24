<template>
  <div class="container index-container d-flex flex-column justify-content-center align-items-center">
    <div class="form-group">
      <label for="comps">Please choose a competition:</label>
      <v-select
        :options="comps"
        v-model="comp"
        label="name"
      >
      </v-select>
    </div>
    <Refresh @refresh="refresh" label="Refresh Competitions" />
  </div>
</template>

<script>
import VSelect from 'vue-select'
import store from '../store'
import { client } from '../axios'

export default {
  name: 'Index',
  data () {
    return {
      comp: null,
      comps: []
    }
  },
  computed: {
  },
  watch: {
    comp(comp) {
      this.$router.push('/comp/' + comp.cid)
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
    'v-select': VSelect
  }
}
</script>

<style lang="less">
.index-container {
  height: calc(100vh - 56px);
  .form-group {
    width: 100%;
  }
}
</style>
