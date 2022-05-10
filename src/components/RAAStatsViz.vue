<template>
  <div :class="{'RAAStatsViz':true, 'fullscreen':(fullscreen), 'mobile': (isMobile)}">
    <button
      v-if="!fullscreen"
      class="teaserButton"
      @click="goFullscreen(true)"
    >
      Statistik erkunden
    </button>

    <div
      v-if="fullscreen"
      class="header"
    >
      <h2
        v-if="isMobile"
        class="headline"
      >
        Rechte Gewalt in Sachsen
      </h2>

      <div
        v-if="!isMobile"
        :class="{'viewControl':true, 'hidden':(!mobileMenuActive)}"
        @click="showMobileMenu(false)"
      >
        <p class="label">
          Rechte Gewalt in Sachsen
        </p>
        <span
          :class="{'active': (numerosityIsActive)}"
          @click="switchView('numerosity')"
        >Angriffe & Betroffene</span>
        <span
          :class="{'active': (streamIsActive)}"
          @click="switchView('steam')"
        >Entwicklung</span>
      </div>

      <span
        v-if="isMobile && !mobileMenuActive"
        class="mobileMenuButton"
        @click="showMobileMenu(true)"
      >{{ numerosityIsActive ? 'Jahr ' + selectedYear : selectedGrouping }}</span>

      <div
        v-if="numerosityIsActive"
        :class="{'viewControl':true, 'hidden':(!mobileMenuActive)}"
        @click="showMobileMenu(false)"
      >
        <p class="label">
          Jahr wählen
        </p>
        <span
          v-for="set in sets"
          :key="set.year"
          :class="{'active': (set.year == selectedYear)}"
          @click="selectedYear = set.year"
        >{{ set.year }}</span>
      </div>

      <div
        v-if="streamIsActive"
        class="viewControl"
        @click="showMobileMenu(false)"
      >
        <p class="label">
          Aufschlüsseln nach
        </p>
        <span
          :class="{'active': (selectedGrouping == 'Motiv')}"
          @click="selectedGrouping = 'Motiv'"
        >Motiv</span>

        <span
          :class="{'active': (selectedGrouping == 'Region')}"
          @click="selectedGrouping = 'Region'"
        >Region</span>
      </div>

      <span
        v-if="!isMobile"
        class="active right closeBtn"
        @click="goFullscreen(false)"
      >✖</span>
    </div>

    <numerosity-view
      v-if="numerosityIsActive"
      v-bind="propsToPass"
    />
    <stream-view
      v-if="streamIsActive"
      v-bind="propsToPass"
    />

    <p
      v-if="fullscreen && isMobile"
      class="closeBtn"
      style="text-align: center; padding: 1em; border-top: 1px solid black;"
      @click="goFullscreen(false)"
    >
      ✖
    </p>
  </div>
</template>

<script>
import { Component, Vue } from 'vue-property-decorator'
import * as statsData from '../../data/raa-stats.json'
import 'core-js'
import KolleDeviceDetector from '@/helpers/kolle-device-detector'

import NumerosityView from './NumerosityView'
import StreamView from './StreamView'

export default
@Component({
  components: {
    NumerosityView,
    StreamView
  }
})
class RAAStatsViz extends Vue {
  baseSize = 1
  fullscreen = false
  isMobile = false
  mobileMenuActive = false

  selectedYear = null
  selectedGrouping = null

  numerosityIsActive = true
  streamIsActive = false

  get propsToPass () {
    return {
      sets: this.sets,
      baseSize: this.baseSize,
      fullscreen: this.fullscreen,
      isMobile: this.isMobile,
      selectedYear: this.selectedYear,
      selectedGrouping: this.selectedGrouping
    }
  }

  created () {
    this.selectedYear = this.sets[0].year
    if (!this.selectedGrouping) this.selectedGrouping = 'Motiv'
  }

  updated () {
    this.calculateBaseSize()
  }

  mounted () {
    this.isMobile = KolleDeviceDetector.isMobile()
    this.calculateBaseSize()

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
    this.calculateBaseSize()
    this.fullscreen = bool

    const bodyClasses = document.getElementsByTagName('body')[0].classList
    if (bool) {
      bodyClasses.add('noscroll')
      window.location.hash = '#tool'
    } else {
      bodyClasses.remove('noscroll')
      window.location.hash = ''
      this.switchView('numerosity')
    }
  }

  showMobileMenu (bool) {
    // this.calculateBaseSize()
    this.mobileMenuActive = bool

    // const bodyClasses = document.getElementsByTagName('body')[0].classList
    // if (bool) bodyClasses.add('noscroll')
    // else bodyClasses.remove('noscroll')
  }

  calculateBaseSize () {
    if (this.isMobile) {
      this.baseSize = this.fullscreen ? Math.floor(this.$el.clientHeight * 0.004) : Math.floor(this.$el.clientHeight * 0.006)
    } else {
      this.baseSize = this.fullscreen ? Math.floor(this.$el.clientHeight * 0.008) : Math.floor(this.$el.clientHeight * 0.017)
    }
  }
}
</script>

<style scoped lang="scss">
.RAAStatsViz {
  height: 100%;
  width: 100%;
  position: relative;

  &.fullscreen {
    position: fixed;
    height: calc(100vh - 10rem);
    width: 100vw;
    overflow-y: auto;

    top: 10rem;
    right: 0;
    z-index: 999;
    padding: 0 5rem 2rem;
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
    border-bottom: 2px dotted #AAAAAA;
    display: inline-block;
    justify-self: start;

    > * {
      display: inline-block;
      margin: 0 .5em;
      cursor: pointer;
      font-size: .8rem;

      &.active {
        font-weight: bold;
        font-size: 1rem;
      }

      &.label {
        display: block;
        font-size: .7em;
        cursor: unset;
        margin-bottom: .5em;
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

      &.hidden {
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
    position: absolute;
    top: 0;
    right: 0;
    background-color: rgb(239, 123, 31);
    color: white;
  }

  .closeBtn {
    cursor: pointer;
    font-size: 2rem;
  }

  .attacks {
    .subHeadline {
      color: #F67878;
    }

    circle {
      fill: #F67878;
    }

    text {
      fill: #F67878;
      font-size: .7rem;
    }
  }

  .victims {
    .subHeadline {
      color: #8FC3A7;
    }

    circle {
      fill: #8FC3A7;
    }

    text {
      fill: #8FC3A7;
      font-size: .7rem;
    }
  }

  &.mobile {
    height: 100vh;
    top: 0;
    padding: 2rem 2rem;

    .header {
      display: flex;
      flex-direction: column;

      .headline {
        order: -1;
        align-self: center;
        margin-bottom: .3em;
      }

      .mobileMenuButton {
        align-self: center;
        font-style: italic;
        border-bottom: 2px dotted #AAAAAA;
        font-weight: bold;
      }

      .viewControl {
        align-self: center;
        padding: 1em 0;

        &.hidden {
          display: none;
        }
      }
    }
  }
}
</style>
