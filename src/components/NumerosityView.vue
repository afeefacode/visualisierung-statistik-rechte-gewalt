<template>
  <div v-bind:class="{NumerosityView: true, fullscreen: fullscreen, 'mobile': (isMobile) }">
    <div class="entities">
      <template v-for="type in settings.entities">
        <section
          :key="type"
          v-bind:class="{ 'entity': true, [type]: true, 'hidden': !(fullscreen || type == 'attacks') }"
          v-if="currentSet[type]"
        >
          <p class="subHeadline">
            <!-- {{ currentGrouping[type].groups[0].value }} {{ currentGrouping[type].groups[0].label }} -->
            {{ currentSet[type].groupings[0].groups[0].value }} {{ currentSet[type].groupings[0].groups[0].label }}
            <span
              v-if="!fullscreen"
            >im Jahr 2021</span>
          </p>

          <svg :ref="type" v-bind:style="{ 'height': svgHeight }">
            <g class="groupings" :transform="'translate(' + baseSize + ',' + baseSize + ')'">
              <template v-for="(group, i) in currentGrouping[type].groups">
                <circle
                  v-for="(n) in group.value"
                  :key="circleKey(currentGrouping[type].groups, i, n-1)"
                  :alt="circleKey(currentGrouping[type].groups, i, n-1)"
                  :data-gn="i"
                  :data-n="n-1"
                />

                <template v-if="fullscreen && currentGrouping[type].groups.length > 1">
                  <text
                    :x="settings.entitiesPerRow  * spaceScale * baseSize + 10"
                    :y="calcGroupPos(type, i) + 5"
                    :key="'label-' + i"
                  >{{ group.label }}</text>
                  <text
                    v-if="fullscreen"
                    :x="settings.entitiesPerRow  * spaceScale * baseSize + 10"
                    :y="calcGroupPos(type, i) + 5 + 13"
                    :key="'value-' + i"
                  >{{ group.value }}</text>
                </template>
              </template>
            </g>
          </svg>

          <div v-if="fullscreen" class="viewControl">
            <p class="label">Aufschlüsseln nach</p>
            <p
              v-for="grouping in currentSet[type].groupings"
              :key="grouping.label"
              v-bind:class="{ 'active': currentGrouping[type].label == grouping.label }"
              @click="setGrouping(grouping, type)"
            >{{ grouping.label }}</p>
          </div>
        </section>
        <section v-else>
          <p class="info">
            <br />Betroffene wurden für diesen Jahrgang nicht separat erfasst.
          </p>
        </section>
      </template>
    </div>
  </div>
</template>

<script>
import { Component, Vue, Watch } from 'vue-property-decorator'
import * as d3 from 'd3'
import 'core-js'
// // import 'core-js/es/array'
// import 'core-js/es6/symbol'

export default
@Component({
  props: ['sets', 'baseSize', 'fullscreen', 'isMobile', 'selectedYear']
})
class NumerosityView extends Vue {
  settings = {
    entitiesPerRow: 25,
    groupGap: 30,
    entities: ['attacks', 'victims']
  }

  currentSet = null
  currentGrouping = { attacks: {}, victims: {} }

  svgHeight = 'auto'

  greenColorScale = null

  created () {
    this.setCurrentSet()

    this.colors = ["#8DC2A6", "#A4E2CF", "#93D9BC", "#93D9AC", "#8CCF9C"]
    this.greenColorScale = d3.scaleOrdinal().range(this.colors)
  }

  updated () {
    this.draw()

    // for (const entity of this.settings.entities) {
    //   if (!this.currentSet[entity]) return

    //   const svg = this.$refs[entity][0]
    //   const circle = d3.select(svg).selectAll('circle')

    // }
  }

  mounted () {
    this.draw()
  }

  @Watch('selectedYear')
  onYearChange () {
    this.setCurrentSet()
    this.draw()
  }

  setCurrentSet () {
    this.currentSet = _.find(this.sets, (set) => { return set.year == this.selectedYear })
    if (this.currentSet === undefined) this.currentSet = this.sets[0]

    this.setInitialGrouping()
  }

  setGrouping (grouping, entity) {
    this.currentGrouping[entity] = grouping
    // console.log('set grouping to', this.currentGrouping)
  }

  setInitialGrouping () {
    for (const entity of this.settings.entities) {
      if (this.currentSet[entity] === undefined) {
        this.currentGrouping[entity] = { groups: [{ label: 'Betroffene nicht erfasst', value: 0 }] }
      } else {
        this.currentGrouping[entity] = this.currentSet[entity].groupings[0]
      }
    }
  }

  // draws to early because the DOM is not yet updated by vue and $refs are not available yet
  // @Watch('currentGrouping.attacks.groups', { deep: true })
  // onGroupChange (val, oldVal) {
  //   console.log('watched group change')
  //   this.draw()
  // }

  getGroupValues (grouping) {
    let sum = 0
    for (const group of grouping.groups) {
      sum += group.value
    }

    return sum
  }

  draw () {
    console.log('draw', this.currentSet)

    for (const entity of this.settings.entities) {
      if (!this.currentSet[entity]) return

      const svg = this.$refs[entity][0]
      const circle = d3.select(svg).selectAll('circle')

      circle
        // .data(this.sets)
        .transition()
        .attr('cx', (d, i, nodeList) => {
          if (this.fullscreen) {
            return this.calcCirclePos(i, entity, nodeList).x
          }
          return this.calcCirclePos(i, entity, nodeList).x + 10 + Math.floor(Math.random() * 20 + 1)
        })
        .attr('cy', (d, i, nodeList) => {
          if (this.fullscreen) {
            return this.calcCirclePos(i, entity, nodeList).y
          }
          return this.calcCirclePos(i, entity, nodeList).y + 10 + Math.floor(Math.random() * 20 + 1)
        })
        .duration(() => {
          return this.fullscreen ? 500 : 1000
        })
        .delay((d, i) => {
          return this.fullscreen ? Math.floor(Math.random() * 500 + 10) : Math.floor(Math.random() * 1000 + 10)
        })
        .attr('r', (d, i) => {
          return this.baseSize
        })
        .style("fill", (d, i) => { return entity === 'victims' ? this.greenColorScale(Math.ceil(Math.random() * 100)) : null })

      // const lastCircle =
      // svg.style.height = circle.nodes()[circle.nodes().length - 1].getAttribute('cy') + 'px'
      svg.style.height = this.calcCirclePos(circle.nodes().length - 1, entity, circle.nodes()).y + 50 + 'px'

      // circle
      //   .enter()
      //   .append("circle")
      //   .attr("cx",function(d,i){
      //       return d.val
      //   })
      //   .attr("cy",function(d,i){
      //       return d.val
      //   })
      // .attr("r",function(d,i){return 10})
      // .on("click",function(){
      //   that.addRecord()
      // })
      // }

      // this.svgHeight = '700px'
    }


    // this.first = false
  }

  circleKey (groups, nthGroup, nthInGroup) {
    let index = 0
    for (let n = 0; n < nthGroup; n++) {
      index += groups[n].value
    }

    return index + nthInGroup
  }

  calcCirclePos (i, entity, nodeList) {
    const nthGroup = Number(nodeList[i].getAttribute('data-gn'))
    const nthInGroup = Number(nodeList[i].getAttribute('data-n'))

    const blockY = this.calcGroupPos(entity, nthGroup)

    const row = Math.floor(nthInGroup / this.settings.entitiesPerRow)

    return {
      x: (nthInGroup % this.settings.entitiesPerRow) * this.spaceScale * this.baseSize,
      y: row * this.spaceScale * this.baseSize + blockY
    }
  }

  calcGroupPos (type, i) {
    let groupSpaceSum = 0
    for (let n = 0; n < i; n++) {
      const rows = Math.ceil(
        this.currentGrouping[type].groups[n].value / this.settings.entitiesPerRow
      )
      groupSpaceSum += rows * this.baseSize * this.spaceScale
    }

    return groupSpaceSum + i * this.settings.groupGap
  }

  get spaceScale () {
    return 3
  }
}
</script>

<style scoped lang="scss">
.NumerosityView {
  height: 100%;
  width: 100%;
  padding: 1rem 0;

  .header {
    & > * {
      display: inline-block;
      margin-right: 2rem;
    }

    .headline {
      margin-top: 0;
    }
  }

  .viewControl {
    border-bottom: 2px dotted #AAAAAA;
    display: inline-block;
    justify-self: start;
  }

  .entities {
    display: grid;
    grid-template-columns: 1fr 1fr;
    height: 100%;

    .entity {
      display: grid;
      grid-template-columns: 100%;
      grid-template-rows: auto 1fr auto;
      // align-content: flex-start;

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
    justify-self: center;
    align-self: flex-start;
  }

  .attacks {
    grid-column-end: span 2;

    .subHeadline {
      color: #F67878;
    }

    circle {
      fill: #F67878;
    }

    text {
      fill: #F67878;
      font-size: 0.7rem;
    }
  }
  &.fullscreen .attacks {
    grid-column-end: span 1;
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
      font-size: 0.7rem;
    }
  }

  &.mobile {
    .entities {
      grid-template-columns: 1fr;
      grid-gap: 5rem;
    }
  }
}
</style>
