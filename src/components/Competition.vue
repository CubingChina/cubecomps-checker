<template>
  <div class="container py-3">
    <div class="row">
      <div class="col-sm-12 col-md-4 col-lg-3">
        <div class="form-group">
          <label for="rounds">Events</label>
          <select class="form-control" v-model="round">
            <optgroup v-for="event in events" :label="event.name">
              <option :value="{ cat: r.cat, rnd: r.rnd }" v-for="r in event.rounds">{{ r.name }}</option>
            </optgroup>
          </select>
        </div>
        <Refresh @refresh="refresh" />
      </div>
      <div class="col-sm-12 col-md-8 col-lg-9">
        <div class="row">
          <div class="col-sm-12 col-md-6">
            <div class="form-group">
              <label for="compid">Number</label>
              <input type="tel" id="compid" class="form-control" v-model.number="compid" @keyup.enter="select" />
            </div>
          </div>
          <div class="w-100"></div>
          <div class="col-sm-12 col-md-6" v-if="selected.compid">
            <table class="table table-bordered">
              <tbody>
                <tr>
                  <th>No.</th>
                  <td>{{ selected.compid }}</td>
                </tr>
                <tr>
                  <th>Name</th>
                  <td>{{ selected.name }}</td>
                </tr>
                <tr v-for="(r, i) in selected.results">
                  <th>#{{ i + 1}}</th>
                  <td v-html="r"></td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="col-12">
            <h4>Results</h4>
            <Refresh @refresh="refreshResults" />
            <div class="table-responsive">
              <table class="table table-bordered table-striped">
                <tbody>
                  <tr v-for="result in results">
                    <td>No.{{ result.compid }}</td>
                    <td>{{ result.name }}</td>
                    <td v-for="score in result.results" v-html="score">
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import store from 'store'
import { client } from '../axios'

export default {
  data() {
    return {
      events: [],
      round: {},
      selected: {},
      results: [],
      compid: ''
    }
  },
  computed: {
    id() {
      return this.$route.params.id
    },
    cat() {
      return this.$route.params.cat
    },
    rnd() {
      return this.$route.params.rnd
    },
    compids() {
      return this.results.map(r => r.compid)
    }
  },
  watch: {
    round(r) {
      this.$router.push({
        name: 'Results',
        params: {
          id: this.id,
          cat: r.cat,
          rnd: r.rnd
        }
      })
      this.fetchResults()
    }
  },
  mounted() {
    if (this.cat) {
      this.round = {
        cat: this.cat,
        rnd: this.rnd
      }
    }
    if (store.get('comp_' + this.id)) {
      this.events = store.get('comp_' + this.id)
    } else {
      this.refresh()
    }
  },
  methods: {
    select() {
      this.selected = this.results.filter(r => r.compid == this.compid)[0] || {}
      if (this.selected.compid) {
        this.compid = ''
      }
    },
    fetchResults() {
      if (!this.cat) {
        return
      }
      const key = ['results', this.id, this.cat, this.rnd].join('_')
      if (store.get(key)) {
        this.results = store.get(key)
      } else {
        this.refreshResults()
      }
    },
    async refreshResults() {
      this.$parent.loading(true)
      try {
        const res = await client.get('', {
          params: {
            action: 'results',
            cid: this.id,
            cat: this.cat,
            rnd: this.rnd
          }
        })
        this.results = res.data
        store.set(['results', this.id, this.cat, this.rnd].join('_'), res.data)
      } catch (e) {
        alert(e)
      }
      this.$parent.loading(false)
    },
    async refresh() {
      this.$parent.loading(true)
      try {
        const res = await client.get('', {
          params: {
            action: 'comp',
            cid: this.id
          }
        })
        this.events = res.data
        store.set('comp_' + this.id, res.data)
      } catch (e) {
        alert(e)
      }
      this.$parent.loading(false)
    }
  }
}
</script>
