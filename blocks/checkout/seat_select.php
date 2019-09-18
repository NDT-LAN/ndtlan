<div v-if="!seating.map.length" class="p-3">
  <h1 class="text-center" class="p-3">
    <i class="fa fa-spinner fa-spin" aria-hidden="true"></i> Laster...
  </h1>
</div>
<? get_block('seating_explanation') ?>
<hr>
<div v-if="reservationExpires" class="alert alert-warning alert-dismissible fade show" role="alert">
  <strong>Reservasjon utløper om {{ reservationExpires }}</strong>
  <p>
    For å sikre denne plassen må kjøpet gjennomføres før tiden går ut.
  </p>
  <p>
    <button @click="deleteReservation" class="btn btn-danger"><i class="fa fa-trash"></i> Slett reservasjon</button>
  </p>
</div>
<? get_block('seatmap') ?>
