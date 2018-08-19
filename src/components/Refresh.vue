<template>
  <div class="form-group">
    <button class="btn btn-primary" @click="refresh">Refresh</button>
  </div>
</template>

<script>
export default {
  methods: {
    refresh() {
      this.$emit('refresh')
    }
  }
}
</script>
