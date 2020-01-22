import '../scss/main.scss'
import Vue from 'vue'
import dayjs from 'dayjs'

import startCountdown from './countdown'

window.startCountdown = startCountdown

let $ = window.$

$(function () {
  let tooltips = $('[data-toggle="tooltip"]')
  if (tooltips.length && tooltips.tooltip) {
    tooltips.tooltip()
  }

  const date = new Date()
  date.setFullYear(date.getFullYear() - 13)
  date.setMonth(0)
  date.setDate(1)

  const datepickers = $('.datepicker')
  if (datepickers && datepickers.datepicker) {
    datepickers.datepicker({
      isRTL: false,
      format: 'dd.mm.yyyy',
      autoclose: true,
      language: 'no',
      orientation: 'bottom left',
      defaultViewDate: {
        year: date.getFullYear(),
        month: date.getMonth(),
        day: date.getDate()
      }
    })
  }
})

window.initShop = el => {
  const shopEl = document.querySelector(el)
  if (shopEl) {
    return new Vue({
      el: shopEl,
      data: () => ({
        isBusy: false,
        loaded: false,
        isRefreshing: false,
        discount: '',
        order: null,
        now: new Date(),
        seating: { width: 0, height: 0, map: [] },
        currentStep: 0,
        ticketTypes: [],
        ticketType: null,
        selectedSeat: null,
        checkout: {
          form: {}
        },
        steps: [
          {
            'icon': 'ticket',
            'title': 'Billett'
          },
          {
            'icon': 'star',
            'title': 'Plass'
          },
          {
            'icon': 'usd',
            'title': 'Betaling'
          }
        ]
      }),

      watch: {
        ticketType (id) {
          if (this.selectedSeat) {
            this.reserve(this.selectedSeat[0], this.selectedSeat[1])
          }
        },

        reservationExpires (value) {
          if (!value) {
            this.refreshSeatmap()
          }
        }
      },

      computed: {
        paymentValidated () {
          if (!this.order) {
            return false
          }

          let valid = true
          valid &= this.order.cart.items.length > 0
          valid &= this.seating.reservation !== null
          valid &= this.selectedSeat !== null
          valid &= !!this.ticketType

          if (this.order.form) {
            for (let input of this.order.form) {
              valid &= !!this.checkout.form[input.fieldname]
            }
          }

          if (!this.order.user.phone) {
            valid &= !!this.checkout.phone.length
          }

          if (this.order.user.needs_parental_consent) {
            if (!this.order.user.parent_name) {
              valid &= !!this.checkout.parent_name.length
            }

            if (!this.order.user.parent_phone) {
              valid &= !!this.checkout.parent_phone.length
            }
          }

          if (!this.order.user.birthday) {
            valid &= !!this.checkout.birthday.length
          }

          if (!this.order.user.adresse) {
            valid &= !!this.checkout.adresse.length
          }

          if (!this.order.user.zip) {
            valid &= !!this.checkout.zip.length
          }

          return valid
        },

        reservationExpires () {
          const date = dayjs(this.seating.reservation)
          const target = date.toDate()
          const now = Date.parse(this.now)

          const delta = Date.parse(target) - now

          if (delta < 0 || !this.seating.reservation) {
            return false
          }

          let seconds = Math.floor((delta / 1000) % 60)
          let minutes = Math.floor((delta / 1000 / 60) % 60)
          let hours = Math.floor((delta / (1000 * 60 * 60)) % 24)
          let days = Math.floor(delta / (1000 * 60 * 60 * 24))

          seconds = (seconds < 10 ? '0' : '') + seconds
          minutes = (minutes < 10 ? '0' : '') + minutes
          hours = (hours < 10 ? '0' : '') + hours

          return `${days ? `${days} dager, ` : ''}${hours}:${minutes}:${seconds}`
        },

        ticket () {
          // eslint-disable-next-line
          return this.ticketTypes.find(ticket => ticket.id == this.ticketType)
        },

        formValidated () {
          let isValidated = false

          switch (this.currentStep) {
            case 0:
              isValidated = this.ticketType !== null
              break
            case 1:
              isValidated = this.selectedSeat !== null
              break
            case 2:
              isValidated = this.paymentValidated
              break
            default:
              isValidated = true
              break
          }

          return isValidated
        }
      },

      async created () {
        await this.getTicketTypes()
        if (!this.ticketType && this.ticketTypes.length) {
          this.ticketType = this.ticketTypes[0].id
        }

        await this.refreshSeatmap()
        await this.getOrder()

        const checkout = {
          form: {}
        }

        if (this.order.event && this.order.event.form) {
          for (let input of this.order.event.form) {
            if (!this.checkout.form[input.fieldname]) {
              switch (input.type) {
                case 'select':
                  const options = input.options.split(',')
                  checkout.form[input.fieldname] = options.shift()
                  break
                case 'textfield':
                  checkout.form[input.fieldname] = ''
                  break
                case 'checkbox':
                  checkout.form[input.fieldname] = false
                  break
                default:
                  break
              }
            }
          }

          if (!this.order.user.phone) {
            checkout.phone = ''
          }

          if (this.order.user.needs_parental_consent) {
            if (!this.order.user.parent_name) {
              checkout.parent_name = ''
            }

            if (!this.order.user.parent_phone) {
              checkout.parent_phone = ''
            }
          }

          if (!this.order.user.birthday) {
            checkout.birthday = ''
          }

          if (!this.order.user.adresse) {
            checkout.adresse = ''
          }

          if (!this.order.user.zip) {
            checkout.zip = ''
          }

          this.$set(this, 'checkout', checkout)
        }
      },

      mounted () {
        this.refreshSeatmap()

        setInterval(() => {
          this.now = new Date()
        }, 1000)

        this.loaded = true
      },

      methods: {
        async startMapRefresh (step) {
          if (!this.isRefreshing) {
            this.isRefreshing = true
            const refreshMap = () => {
              setTimeout(async () => {
                await this.refreshSeatmap()
                if (this.currentStep === step) {
                  refreshMap()
                }
              }, 5 * 1000)
            }

            await this.refreshSeatmap()
            refreshMap()
          }
        },

        async addDiscount () {
          if (this.discount.length) {
            const { status } = await window.fetch(`/api/v1/discount?code=${this.discount}`, {
              method: 'POST',
              credentials: 'include'
            })

            switch (status) {
              case 404:
                window.alert('Ugyldig rabattkode')
                break
              case 400:
                window.alert('Rabattkoden er brukt')
                break
              case 500:
                window.alert('Det oppstod en feil')
                break
              default:
                await this.getOrder()
                break
            }

            this.discount = ''
          }
        },

        async deleteReservation () {
          await window.fetch(`/api/v1/reservation_delete`, {
            method: 'DELETE',
            credentials: 'include'
          })
          await this.refreshSeatmap()
        },

        async checkoutStripe () {
          this.isBusy = true
          const root = new URL(window.location.href).origin

          const response = await window.fetch(`/api/v1/checkout`, {
            method: 'POST',
            credentials: 'include',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              ...this.checkout,
              callback: `${root}/${window._page.url}`
            })
          })

          const { data, stripe_public_key: stripePK } = await response.json()
          const stripe = new window.Stripe(stripePK)

          stripe.redirectToCheckout({
            sessionId: data.stripe_session_id
          })
        },

        async getOrder () {
          this.isBusy = true
          const response = await window.fetch(`/api/v1/order`, {
            method: 'GET',
            credentials: 'include'
          })

          const data = await response.json()
          this.order = data
          this.isBusy = false
        },

        switchTab (i) {
          if (i <= this.currentStep) {
            this.currentStep = i
          } else {
            if (this.formValidated && i === this.currentStep + 1) {
              this.currentStep = i
            }
          }

          if (this.currentStep === 1) {
            this.startMapRefresh(1)
          }
        },

        enableTooltips () {
          setTimeout(() => {
            $('[data-toggle="tooltip"]')
              .tooltip()
          }, 0)
        },

        nextStep () {
          this.enableTooltips()

          if (this.formValidated && this.currentStep + 1 < this.steps.length) {
            this.currentStep++
            if (this.currentStep === 1) {
              this.startMapRefresh(1)
            }
            window.scrollTo(0, 0)
          } else {
            this.checkoutStripe()
          }
        },

        async reserve (x, y) {
          if (!this.isBusy) {
            this.isBusy = true

            $('.ndt-seat').prop('disabled', true)

            await window.fetch(`/api/v1/reserve?ticket=${this.ticketType}&x=${x}&y=${y}`, {
              method: 'GET',
              credentials: 'include'
            })

            await this.refreshSeatmap()

            this.isBusy = false
            $('.ndt-seat').prop('disabled', false)

            setTimeout(() => {
              $('.tooltip').tooltip('hide')
            }, 0)
          }
        },

        async getTicketTypes () {
          const response = await window.fetch('/api/v1/tickets', {
            method: 'GET',
            credentials: 'include'
          })

          this.ticketTypes = await response.json()
        },

        async refreshSeatmap () {
          const response = await window.fetch('/api/v1/seatmap.json', {
            method: 'GET',
            credentials: 'include'
          })
          const config = await response.json()
          this.seating = config
          let reservation = null
          for (let row of this.seating.map) {
            if (row) {
              for (let seat of row) {
                if (seat) {
                  if (seat.type === 'myreservation') {
                    reservation = [seat.x, seat.y]
                    break
                  }
                }
              }
              if (reservation) {
                break
              }
            }
          }

          this.selectedSeat = reservation

          this.enableTooltips()
        },

        getSeatAlt ({ type, label }) {
          let alt = ''
          switch (type) {
            case 'taken':
              alt = 'Opptatt'
              break
            case 'seat':
              alt = 'Ledig'
              break
            case 'myseat':
              alt = 'Min plass'
              break
            case 'myreservation':
              alt = 'Min reservasjon'
              break
            default:
              alt = type
              break
          }

          return `${label} (${alt})`
        },

        seatClass (x, y) {
          if (this.seating.map[y][x]) {
            const seat = this.seating.map[y][x]

            if (seat) {
              return `ndt-seat--${seat.type}`
            }
          }
        },

        async selectSeat (x, y) {
          const seat = this.seating.map[y][x]
          if (seat) {
            if (seat.type === 'seat') {
              await this.reserve(x, y)
              await this.refreshSeatmap()
            }
          }

          await this.getOrder()
        }
      }
    })
  }
}
