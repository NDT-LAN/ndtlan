<div class="container ndt-seating-container p-4">
  <div class="row ndt-seating-row" v-for="(row, i) in seating.map" :key="`row_${i}`">
    <template
      v-for="(seat, j) in row"
    >
      <button
        :key="`seat_${j}`"
        class="ndt-seat btn"
        :class="seatClass(seat.x, seat.y)"
        data-toggle="tooltip"
        :title="seat.label"
        :aria-label="getSeatAlt(seat)"
        :style="`${ seat.reserved ? 'opacity: 0.5' : '' }`"
        @click="selectSeat(seat.x, seat.y)"
      ></button>
    </template>
  </div>
</div>
