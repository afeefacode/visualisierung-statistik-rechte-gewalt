<template>
  <div id="app">
    <RAAStatsViz />
  </div>
</template>

<script>
import RAAStatsViz from './components/RAAStatsViz.vue'

export default {
  name: 'App',
  components: {
    RAAStatsViz
  }
}
</script>

<style>
#app {
  font-family: Avenir, Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2C3E50;
  margin-top: 60px;
}
</style>
