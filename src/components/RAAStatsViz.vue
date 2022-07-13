<template>
  <div
    v-bind:class="{'RAAStatsViz':true, 'fullscreen':(fullscreen), 'mobile': (isMobile)}"
    :style="{'top': (fullscreen && !isMobile) ? fullscreenMarginTop : null, 'height': (fullscreen && !isMobile) ? `calc(100vh - ${fullscreenMarginTop})` : null}"
  >
    <div class="header" v-if="fullscreen">
      <h2 v-if="isMobile" class="headline">Vorfälle rechter Gewalt in Niedersachsen</h2>

      <div
        v-if="!isMobile"
        v-bind:class="{'viewControl':true, 'hydden':(!mobileMenuActive)}"
        @click="showMobileMenu(false)"
      >
        <p class="label">Vorfälle in Niedersachsen</p>
        <span
          v-bind:class="{'active': (numerosityIsActive)}"
          @click="switchView('numerosity')"
        >Angriffe & Betroffene</span>
        <span v-if="hasStreamView" v-bind:class="{'active': (streamIsActive)}" @click="switchView('steam')">Entwicklung</span>
      </div>

      <div
        v-bind:class="{'viewControl':true}"
        v-if="numerosityIsActive"
        @click="showMobileMenu(false)"
      >
        <p class="label">Jahr wählen</p>
        <span
          v-bind:class="{'active': (set.year == selectedYear)}"
          v-for="set in sets"
          :key="set.year"
          @click="selectedYear = set.year"
        >{{ set.year }}</span>
      </div>

      <div class="viewControl" v-if="streamIsActive" @click="showMobileMenu(false)">
        <p class="label">Aufschlüsseln nach</p>
        <span
          v-bind:class="{'active': (selectedGrouping == 'Motiv')}"
          @click="selectedGrouping = 'Motiv'"
        >Motiv</span>

        <span
          v-bind:class="{'active': (selectedGrouping == 'Region')}"
          @click="selectedGrouping = 'Region'"
        >Region</span>
      </div>

      <span v-if="!isMobile" class="active right closeBtn" @click="goFullscreen(false)">✖</span>
    </div>


    <numerosity-view v-bind="propsToPass" v-if="numerosityIsActive" />
    <stream-view v-bind="propsToPass" v-if="streamIsActive" />

    <button class="teaserButton" :style="`background-color: ${teaserButtonColor}`" @click="goFullscreen(true)" v-if="!fullscreen">Statistik erkunden</button>

    <p
      v-if="fullscreen && isMobile"
      class="closeBtn"
      style="text-align: center; padding: 1em; border-top: 1px solid black;"
      @click="goFullscreen(false)"
    >✖</p>
  </div>
</template>

<script>
import { Component, Vue } from 'vue-property-decorator'
import * as statsData from '../data/data.json'
import 'core-js'
import KolleDeviceDetector from '@/helpers/kolle-device-detector'

import NumerosityView from './NumerosityView'
import StreamView from './StreamView'

export default
@Component({
  props: ['teaserButtonColor', 'fullscreenMarginTop','hasStreamView'],
  components: {
    NumerosityView,
    StreamView
  }
})
class RAAStatsViz extends Vue {
  fullscreen = false
  isMobile = false
  mobileMenuActive = false

  availableWidth = 0

  selectedYear = null
  selectedGrouping = null

  numerosityIsActive = true
  streamIsActive = false

  get propsToPass () {
    return {
      sets: this.sets,
      availableWidth: this.availableWidth,
      fullscreen: this.fullscreen,
      isMobile: this.isMobile,
      selectedYear: this.selectedYear,
      selectedGrouping: this.selectedGrouping
    }
  }

  created () {
    this.selectedYear = this.sets[0].year
  }

  updated () {
    this.calculateAvailableSpace()
  }

  mounted () {
    this.isMobile = KolleDeviceDetector.isMobile()
    this.calculateAvailableSpace()

    if (window.location.hash === '#tool') this.goFullscreen(true)
  }

  data () {
    return {
      sets: statsData.default
    }
  }

  switchView (viewKey) {
    this.numerosityIsActive = false
    this.streamIsActive = false

    switch (viewKey) {
      case 'numerosity':
        this.numerosityIsActive = true
        break
      case 'steam':
        this.streamIsActive = true
        break
      default:
        this.numerosityIsActive = true
    }
  }

  goFullscreen (bool) {
    this.calculateAvailableSpace()
    this.fullscreen = bool

    const bodyClasses = document.getElementsByTagName('body')[0].classList
    if (bool) {
      bodyClasses.add('noscroll')
      window.location.hash = '#tool'
    }
    else {
      bodyClasses.remove('noscroll')
      window.location.hash = ''
      this.switchView('numerosity')
      // TODO reset numerosityView to first grouping; prop selectedGrouping is not used by numerosityView
      // this.selectedGrouping = this.sets[0].attacks.groupings[0]
    }
  }

  showMobileMenu (bool) {
    // this.calculateAvailableSpace()
    this.mobileMenuActive = bool

    // const bodyClasses = document.getElementsByTagName('body')[0].classList
    // if (bool) bodyClasses.add('noscroll')
    // else bodyClasses.remove('noscroll')
  }

  calculateAvailableSpace () {
    const computedStyle = getComputedStyle(this.$el)
    const elementWidth = this.$el.clientWidth
    this.availableWidth = elementWidth - parseFloat(computedStyle.paddingLeft) - parseFloat(computedStyle.paddingRight)
  }
}
</script>

<style scoped lang="scss">
.RAAStatsViz {
  height: 100%;
  width: 100%;
  position: relative;
  box-sizing: border-box;

  &.fullscreen {
    position: fixed;
    width: 100vw;
    overflow-y: auto;

    top: 0;
    right: 0;
    z-index: 999;
    padding: 2rem 5rem;
    background: white;

    display: grid;
    grid-template-columns: 100%;
    grid-template-rows: auto 1fr;
  }

  .header {
    display: grid;
    grid-template-columns: auto auto 1fr auto;
    grid-gap: 0 5rem;
    // grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    // display: flex;
    // justify-content: space-between;

    & > * {
      align-self: flex-start;

      &.center {
        justify-self: center !important;
      }

      &.right {
        justify-self: end !important;
      }
    }

    .headline {
      margin-top: 0;
    }
  }

  ::v-deep .viewControl {
    border-bottom: 2px dotted #aaaaaa;
    display: inline-block;
    justify-self: start;

    > * {
      display: inline-block;
      margin: 0 0.5em;
      cursor: pointer;
      font-size: 0.8rem;

      &.active {
        font-weight: bold;
        font-size: 1rem;
      }

      &.label {
        display: block;
        font-size: 0.7em;
        cursor: unset;
        margin-bottom: 0.5em;
      }
    }
  }

  .entities {
    display: grid;
    grid-template-columns: 1fr 1fr;
    height: 100%;

    .entity {
      display: grid;
      grid-template-columns: 100%;
      grid-template-rows: auto 1fr auto;

      &.hydden {
        display: none;
      }

      svg {
        align-self: stretch;
        justify-self: stretch;
      }
    }
  }

  .subHeadline {
    font-weight: bold;
  }

  .teaserButton {
    top: 0;
    right: 0;
    color: white;
  }

  .closeBtn {
    cursor: pointer;
    font-size: 2rem;
  }

  .attacks {
    .subHeadline {
      color: #f67878;
    }

    circle {
      fill: #f67878;
    }

    text {
      fill: #f67878;
      font-size: 0.7rem;
    }
  }

  .victims {
    .subHeadline {
      color: #8fc3a7;
    }

    circle {
      fill: #8fc3a7;
    }

    text {
      fill: #8fc3a7;
      font-size: 0.7rem;
    }
  }

  &.mobile {
    &.fullscreen {
      height: 100vh;
      padding: 2rem 3rem 0;
    }

    .header {
      display: flex;
      flex-direction: column;

      .headline {
        order: -1;
        align-self: center;
        margin-bottom: 0.3em;
      }

      .mobileMenuButton {
        align-self: center;
        font-style: italic;
        border-bottom: 2px dotted #aaaaaa;
        font-weight: bold;
      }

      .viewControl {
        align-self: center;
        padding: 1em 0;

        &.hydden {
          display: none;
        }
      }
    }
  }
}
</style>
