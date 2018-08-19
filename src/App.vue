<template>
  <div class="wrapper">
    <div class="loading d-flex justify-content-center align-items-center" v-if="isLoading">Loading...</div>
    <router-view/>
  </div>
</template>

<script>
export default {
  name: 'App',
  data() {
    return {
      isLoading: false
    }
  },
  methods: {
    loading(isLoading) {
      this.isLoading = isLoading
    }
  }
}
</script>

<style>
.wrapper {
  width: 100vw;
  height: 100vh;
}
.loading {
  z-index: 999;
  position: fixed;
  left: 0;
  right: 0;
  top: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.4);
  color: #fff;
  font-size: 48px;
}
</style>
