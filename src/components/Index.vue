<template>
  <div class="container d-flex flex-column justify-content-center align-items-center h-100">
    <div class="form-group">
      <label for="comps">Please choose a comp:</label>
      <select class="form-control" v-model="comp">
        <optgroup v-for="(list, type) in comps" :label="type|translate">
          <option :value="c.cid" v-for="c in list">{{ c.name }}</option>
        </optgroup>
      </select>
    </div>
    <Refresh @refresh="refresh" />
  </div>
</template>

<script>
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
  }
}
</script>

<style scoped>
</style>
