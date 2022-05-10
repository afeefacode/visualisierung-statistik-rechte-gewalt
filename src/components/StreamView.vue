<template>
  <div v-bind:class="{StreamView: true, 'mobile': (isMobile) }">
    <svg :ref="'svg'" style="height: auto">
      <path
        v-for="key in stackKeys"
        :key="key"
        class="layer"
        transform="scale(1,0.1)"
        transform-origin="50% 100%"
        @mousemove="showTooltip({text: key})"
        @mouseleave="hideTooltip()"
      />

      <g class="axis" />
      <line
        class="indicator"
        v-if="indicator.active"
        :x1="indicator.x"
        y1="0"
        :x2="indicator.x"
        y2="200"
        style="stroke:rgb(255,0,0);stroke-width:2"
      />
    </svg>

    <div
      class="tooltip"
      v-if="this.tooltip.active"
      v-bind:style="{ top: (this.tooltip.y) + 'px', left: (this.tooltip.x) + 'px' }"
    >
      <p class="heading" v-if="tooltip.heading">{{ tooltip.heading }}</p>

      <p v-if="tooltip.text">{{ tooltip.text }}</p>

      <div class="table" v-if="tooltip.stack">
        <template v-for="(value, key) in tooltip.stack">
          <span class="value" :style="'color: ' + colorScale(key)" :key="'v'+key">{{ value }}</span>
          <span class="key" :key="'k'+key">{{ key }}</span>
        </template>
      </div>
    </div>
  </div>
</template>

<script>

import { Component, Vue, Watch } from 'vue-property-decorator'
import * as d3 from 'd3'
import * as _ from 'lodash'

export default
@Component({
  props: ['sets', 'baseSize', 'fullscreen', 'isMobile', 'selectedGrouping']
})
class StreamView extends Vue {
  settings = {
    marginGraph: 50
  }

  colors = null
  xScale = null
  yScale = null
  colorScale = null
  stack = null
  nest = null
  area = null
  data = null

  stackKeys = null
  years = null

  tooltip = { stack: null, text: '', y: 0, x: 0, active: false }
  indicator = { y: 0, active: false }

  created () {
    console.debug('origin data', this.sets)

    if (this.isMobile) this.settings.marginGraph = 0

    this.prepareData()

    this.setColors(this.data.sets)
  }

  updated () {
    this.draw()
  }

  @Watch('selectedGrouping')
  onGroupingChange () {
    this.prepareData()
    this.setDimensions()
    this.setColors(this.data.sets)
  }

  prepareData () {
    let prep = { sets: [] }

    for (const set of this.sets) {
      let serie = { year: set.year }

      const motivGrouping = _.find(set.attacks.groupings, (grouping) => { return grouping.label == this.selectedGrouping })

      for (const group of motivGrouping.groups) {
        serie[group.label] = group.value
      }

      prep.sets.push(serie)
    }

    this.data = prep
    console.debug('prepared data', this.data)

    this.setStacks(this.data.sets)
    this.setYears(this.data.sets)

    // fill missing data values with 0 (in case a group is not present in a specific year, e.g. "motiv sozialdarwinismus")
    let stacksCopy = this.data.sets
    let i = 0
    for (const stack of stacksCopy) {
      for (const stackKey of this.stackKeys) {
        if (!stack[stackKey]) this.data.sets[i][stackKey] = 0
      }
      i++
    }
  }

  mounted () {
    this.setDimensions()
    this.draw()
  }

  draw () {
    let layers = this.stack(this.data.sets)
    // console.debug('stack geometry', layers)

    this.xScale.domain(this.years)

    const layersFlat = _.flattenDeep(layers)
    this.yScale.domain([d3.min(layersFlat), d3.max(layersFlat)])

    let svg = d3.select(this.$refs.svg)

    svg.selectAll(".layer")
      .data(layers)
      .attr("d", (d) => { return this.area(d) })
      .style("fill", (d, i) => { return this.colorScale(this.sumsOverTheYears[d.key]) })
      .transition()
      .duration(500)
      .attr("transform", "scale(1,1)")
      .style("opacity", 1)

    svg
      .select('.axis')
      .attr("transform", "translate(0," + (this.$refs.svg.clientHeight - this.settings.marginGraph / 2) + ")")
      .call(this.axis);

    svg.selectAll('.axis .tick')
      .on("mousemove", (d) => { this.showTooltip({ year: d, heading: 'Angriffe ' + d }) })
      .on("mouseleave", (d) => { this.hideTooltip() })

    // svg
    //   .on("mousemove", (d) => { this.setIndicator({ year: d }) })
    //   .on("mouseleave", (d) => { this.hideIndicator() })
  }

  setColors (sets) {
    this.colors = ["rgba(200,0,0,0.3)", "rgba(170,0,0,1)"]

    let sumsOverTheYears = {}

    for (const key of this.stackKeys) {
      sumsOverTheYears[key] = 0

      for (const set of sets) {
        sumsOverTheYears[key] += set[key]
      }
    }

    this.sumsOverTheYears = sumsOverTheYears
    const values = _.map(sumsOverTheYears, (value) => value);

    this.colorScale = d3.scaleLinear()
      .domain([_.min(values), _.max(values)])
      .range(this.colors)
      .interpolate(d3.interpolateHcl)
  }

  setYears (sets) {
    let years = []
    for (const set of sets) {
      years.push(set.year)
    }

    this.years = years.sort()
    console.debug('years', this.years)
  }

  setStacks (sets) {
    let groups = []
    for (const set of sets) {
      const setWithoutYear = _.pickBy(set, (value, key) => { return key != 'year' })
      groups.push(_.keys(setWithoutYear))
    }

    this.stackKeys = _.uniq(_.flatten(groups))
    console.debug('stack keys', this.stackKeys)
  }

  setDimensions () {
    const svgHeight = this.$refs.svg.clientHeight
    const svgWidth = this.$refs.svg.clientWidth

    this.xScale = d3.scalePoint().range([this.settings.marginGraph, svgWidth - this.settings.marginGraph])
    this.yScale = d3.scaleLinear().range([this.settings.marginGraph, svgHeight - this.settings.marginGraph])

    this.xAxis = d3.axisBottom().scale(this.xScale)

    // this.stack = null
    this.stack = d3.stack()
      .keys(this.stackKeys)
      // .order(d3.stackOrderNone)
      .offset(d3.stackOffsetSilhouette)
    // .offset(d3.stackOffsetNone)

    this.area = d3.area()
      .x((d) => { return this.xScale(d.data.year) })
      .y0((d) => { return this.yScale(d[0]) })
      .y1((d) => { return this.yScale(d[1]) })
      .curve(d3.curveCardinal.tension(0))

    this.axis = d3.axisBottom(this.xScale)
      .tickSizeInner(0)
      .tickSizeOuter(0)
      .tickPadding(10)
  }

  // setIndicator (obj) {
  //   this.indicator.x = event.pageX
  //   this.indicator.y = event.pageY
  //   this.indicator.active = true
  // }

  // hideIndicator () {
  //   this.indicator.active = false
  // }

  showTooltip (obj) {
    this.tooltip.stack = _.cloneDeep(_.find(this.data.sets, (set) => { return set.year == obj.year }))
    if (this.tooltip.stack && 'year' in this.tooltip.stack) {
      delete this.tooltip.stack.year;
    }

    this.tooltip.text = obj.text
    this.tooltip.heading = obj.heading

    this.tooltip.x = event.pageX
    this.tooltip.y = event.pageY
    this.tooltip.active = true
  }

  hideTooltip () {
    this.tooltip.active = false
  }
}
</script>

<style scoped lang="scss">
.StreamView {
  display: flex;
  svg {
    width: 100%;
    height: 100%;

    .layer {
      cursor: pointer;
      opacity: 0;

      &:hover {
        opacity: 1;
        stroke-width: 1px;
        filter: brightness(150%);
        stroke: white;
      }
    }

    .axis {
      font-size: 1rem;
      font-weight: bold;
      cursor: pointer;
      .tick {
        fill: red;
      }
    }
  }

  .tooltip {
    font-size: 0.8em;
    background: rgba(255, 255, 255, 0.9);
    border-radius: 0.5em;
    padding: 1em;
    box-shadow: 1px 2px 5px 0px rgba(0, 0, 0, 0.3);
    position: fixed;
    z-index: 999;
    transform: translate(-50%, calc(-100% - 1rem));

    .heading {
      margin: 0 0 1em;
      font-size: 1.5em;
    }

    p {
      margin: 0;
    }

    .table {
      display: grid;
      grid-template-columns: auto auto;
      grid-gap: 0.5em 1em;
      .value {
        font-weight: bold;
      }
    }
  }

  &.mobile {
    .axis {
      font-size: 0.5rem;
    }
  }
}
</style>
