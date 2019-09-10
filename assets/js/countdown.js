import dayjs from 'dayjs'

export default (selector) => {
  const output = document.querySelector(selector)

  if (output) {
    const date = dayjs(output.dataset.target)
    const target = date.toDate()

    const getTimeRemaining = () => {
      const delta = target - Date.parse(new Date())
      let seconds = Math.floor((delta / 1000) % 60)
      let minutes = Math.floor((delta / 1000 / 60) % 60)
      let hours = Math.floor((delta / (1000 * 60 * 60)) % 24)
      let days = Math.floor(delta / (1000 * 60 * 60 * 24))

      seconds = (seconds < 10 ? '0' : '') + seconds
      minutes = (minutes < 10 ? '0' : '') + minutes
      hours = (hours < 10 ? '0' : '') + hours

      return `${days ? `${days} dager, ` : ''}${hours}:${minutes}:${seconds}`
    }

    setInterval(() => {
      output.innerText = getTimeRemaining()
    }, 1000)
  }
}
