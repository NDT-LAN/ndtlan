<div v-if="!ticketTypes.length" class="p-3">
  <h1 class="text-center" class="p-3">
    <i class="fa fa-spinner fa-spin" aria-hidden="true"></i> Laster...
  </h1>
</div>
<div v-for="type in ticketTypes" :key="type.id" class="p-3" :class="{ 'ticket--selected': type.id === ticketType }">
  <div class="d-flex align-items-center" style="cursor: pointer">
    <input class="cursor" type="radio" :id="'ticket_' + type.id" :value="type.id" v-model="ticketType">
    <label class="pl-3 cursor" :for="'ticket_' + type.id">
      <div :class="{ lime: type.id === ticketType }">
        <h2>{{ type.name }}</h2>
        <h4>Kr. {{ (type.price / 100).toLocaleString('nb') }}
      </div>
      <p v-html="type.description"></p>
    </label>
  </div>
  <hr>
</div>

