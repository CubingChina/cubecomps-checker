<template>
  <div class="form-group">
    <button class="btn btn-info" @click="refresh">{{ label }}</button>
  </div>
</template>

<script>
export default {
  props: ['label'],
  methods: {
    refresh() {
      this.$emit('refresh')
    }
  }
}
</script>
