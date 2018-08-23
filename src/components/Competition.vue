<template>
  <div class="row mx-0">
    <div class="col-12">
      <h3>{{ name }}</h3>
    </div>
    <div class="col-sm-12 col-md-4 col-lg-3">
      <div class="form-group">
        <label for="rounds">Events</label>
        <select class="form-control" v-model="event">
          <option v-for="event in events" :value="event">{{ event.name }}</option>
        </select>
      </div>
      <div class="form-group">
        <label for="rounds">Rounds</label>
        <select class="form-control" v-model="round">
          <option v-for="round in event.rounds" :value="round">{{ round.name }}</option>
        </select>
      </div>
      <Refresh @refresh="refresh" label="Refresh Events" />
    </div>
    <div class="col-sm-12 col-md-8 col-lg-9">
      <div class="row">
        <div class="col-12">
          <div class="form-group position-relative">
            <label for="q">Number, name or WCA ID</label>
            <input :type="type" id="q" class="form-control"
              v-model.trim="q"
              ref="q"
              @keyup.enter="select"
              @keyup.up="up"
              @keyup.down="down"
              @focus="focus"
              @blur="blur"
              autocomplete="off"
            />
          </div>
          <div class="form-group position-relative" v-if="competitors.length && q.length">
            <ul class="competitor-list list-group position-absolute">
              <li class="list-group-item" :class="{ active: i === index }" v-for="(competitor, i) in competitors" @click="select">#{{competitor.compid}}. {{ competitor.name }}</li>
            </ul>
          </div>
        </div>
        <div class="w-100"></div>
        <div class="col-12" v-if="result.compid">
          <div class="score-card">
            <table class="table table-bordered">
              <tbody>
                <tr>
                  <th>No.</th>
                  <td>{{ result.compid }}</td>
                </tr>
                <tr>
                  <th>Name</th>
                  <td>{{ result.name }}</td>
                </tr>
                <tr v-for="(score, i) in result.scores">
                  <th>#{{ i + 1}}</th>
                  <td v-html="score"></td>
                </tr>
              </tbody>
            </table>
            <div class="buttons">
              <button class="btn btn-success" @click="checkResult(true)">Good (Y)</button>
              <button class="btn btn-danger" @click="checkResult(false)">Bad (N)</button>
            </div>
          </div>
        </div>
        <div class="col-12">
          <h4>Results</h4>
          <Refresh @refresh="refreshResults" label="Refresh Results" />
          <div class="status" v-if="results.length">Total: {{ results.length }}, Good: {{ goodResults.length }}, Bad: {{ badResults.length }}</div>
          <div class="table-responsive" v-if="results.length">
            <table class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th>Status</th>
                  <th @click="sortBy = 'compid'" class="sortable" :class="{ 'sort-by-this': sortBy == 'compid' }">Number</th>
                  <th @click="sortBy = 'pos'" class="sortable" :class="{ 'sort-by-this': sortBy == 'pos' }">#</th>
                  <th @click="sortBy = 'name'" class="sortable" :class="{ 'sort-by-this': sortBy == 'name' }">Name</th>
                  <td v-for="(score, i) in results[0].scores">t{{ i + 1 }}</td>
                </tr>
              </thead>
              <tbody>
                <tr v-for="result in results">
                  <td v-html="getCheckedStatus(result)"></td>
                  <td>{{ result.compid }}</td>
                  <td>{{ result.pos }}</td>
                  <td>{{ result.name }}</td>
                  <td v-for="score in result.scores" v-html="score">
                  </td>
                </tr>
              </tbody>
            </table>
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
      name: '',
      events: [],
      event: {},
      round: {},
      results: [],
      result: {},
      type: 'text',
      q: '',
      focused: false,
      index: 0,
      sortBy: 'pos'
    }
  },
  props: ['id', 'cat', 'rnd'],
  computed: {
    competitors() {
      const q = this.q.toLowerCase()
      if (q.length == 0) {
        return []
      }
      return this.results.filter(r => r.compid == q) // match compid exactly
        .concat(this.results.filter(r => r.compid.toString().indexOf(q) > -1 && r.compid != q).sort((a, b) => a.compid - b.compid)) // match part of compid
        .concat(this.results.filter(r => r.name.toLowerCase().indexOf(q) > -1).sort((a, b) => a.name.toLowerCase().indexOf(q) - b.name.toLowerCase().indexOf(q))) // match part of name
        .slice(0, 5) // the first 5 results
    },
    checkedResults() {
      return this.results.filter(r => 'checked' in r)
    },
    goodResults() {
      return this.results.filter(r => r.checked === true)
    },
    badResults() {
      return this.results.filter(r => r.checked === false)
    },
    resultsKey() {
      return ['results', this.id, this.cat, this.rnd].join('_')
    }
  },
  watch: {
    '$route.params.cat'() {
      this.setEventRound()
    },
    '$route.params.rnd'() {
      this.setEventRound()
    },
    events() {
      this.setEventRound()
    },
    sortBy(s) {
      this.results.sort((rA, rB) => rA[s] < rB[s] ? -1 : 1)
    },
    round(round) {
      if (!round.cat) {
        return
      }
      this.$router.push({
        name: 'Results',
        params: {
          id: this.id,
          cat: round.cat,
          rnd: round.rnd
        }
      })
      this.sortBy = 'pos'
    },
    results() {
      this.storeResults()
    }
  },
  mounted() {
    if (this.cat) {
      this.setEventRound()
    }
    if (store.get('comp_' + this.id)) {
      const data = store.get('comp_' + this.id)
      this.name = data.name
      this.events = data.events
    } else {
      this.refresh()
    }
  },
  created() {
    window.addEventListener('keyup', this.keyup.bind(this), true)
  },
  methods: {
    select() {
      this.result = this.competitors[this.index]
      this.reset()
    },
    reset() {
      this.$refs.q.blur()
      this.q = ''
      this.index = 0
    },
    focus() {
      this.focused = true
      this.index = 0
    },
    blur() {
      this.focused = false
    },
    up() {
      if (this.index > 0) {
        this.index--
      }
    },
    down() {
      if (this.index < this.competitors.length - 1) {
        this.index++
      }
    },
    keyup(e) {
      const which = e.which
      if (this.result.compid && !this.focused) {
        switch (which) {
          case 89: // Y
          case 107: // + on num keys
            this.checkResult(true)
            break
          case 78: // N
          case 109: // - on num keys
            this.checkResult(false)
            break
        }
      }
    },
    setEventRound() {
      const { cat, rnd } = this.$route.params
      this.events.forEach(event => {
        if (event.cat == cat) {
          this.event = event
          event.rounds.forEach(round => {
            if (round.rnd == rnd) {
              this.round = round
            }
          })
        }
      })
      this.fetchResults()
    },
    checkResult(checked) {
      if (!('checked' in this.result)) {
        this.$set(this.result, 'checked', checked)
      }
      this.result.checked = checked
      this.$refs.q.focus()
      this.result = {}
    },
    getCheckedStatus(result) {
      if (result.checked === true) {
        return '<b class="text-success">✓</b>'
      } else if (result.checked === false) {
        return '<b class="text-danger">×</b>'
      }
    },
    fetchResults() {
      if (!this.cat) {
        return
      }
      const key = this.resultsKey
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
        const resultsByCompId = {}
        this.results.forEach(result => {
          resultsByCompId[result.compid] = result
        })
        res.data.forEach(r => {
          if (resultsByCompId[r.compid]) {
            if (this.isSameResult(r, resultsByCompId[r.compid])) {
              r.checked = resultsByCompId[r.compid].checked
            }
          }
        })
        this.results = res.data
      } catch (e) {
        alert(e)
      }
      this.$parent.loading(false)
    },
    isSameResult(newResult, oldResult) {
      let same = true
      newResult.scores.forEach((score, index) => {
        if (score != oldResult.scores[index]) {
          same = false
        }
      })
      return same
    },
    storeResults() {
      store.set(this.resultsKey, this.results)
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
        this.name = res.data.name
        this.events = res.data.events
        store.set('comp_' + this.id, res.data)
      } catch (e) {
        alert(e)
      }
      this.$parent.loading(false)
    }
  }
}
</script>

<style lang="less">
.score-card {
  font-size: 1.5em;
  @media (max-width: 479px) {
    position: fixed;
    z-index: 999;
    top: 0;
    bottom: 0;
    left: 0;
    right: 0;
    background-color: #fff;
    padding-top: 1em;
    .buttons {
      display: flex;
      button {
        flex: 1;
        padding-top: 1em;
        padding-bottom: 1em;
      }
    }
  }
}
.competitor-list {
  z-index: 99;
  top: 0;
  left: 0;
  right: 0;
  li {
    cursor: pointer;
  }
}
.sortable {
  cursor: pointer;
  white-space: nowrap;
  &.sort-by-this:after {
    content: ' \02b06';
  }
}
</style>
