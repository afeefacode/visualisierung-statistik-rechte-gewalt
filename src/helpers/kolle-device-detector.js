class KolleDeviceDetector {
  getDevice () {
    let indicator = document.querySelector('.device-indicator')

    if (!indicator) {
      indicator = document.createElement('div')
      indicator.className = 'device-indicator'
      document.body.appendChild(indicator)
    }

    const device_string = window.getComputedStyle(indicator, ':before').getPropertyValue('content')

    return device_string.replace(/"/g, '')
  }

  isMobile () {
    switch (this.getDevice()) {
      case 'mobile':
        return true
      default:
        return false
    }
  }

  isDesktop () {
    switch (this.getDevice()) {
      case 'big':
      case 'tablet':
      case 'mobile':
        return true
      default:
        return false
    }
  }
}
export default new KolleDeviceDetector()
