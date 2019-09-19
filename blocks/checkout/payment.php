<div v-if="order" class="p-3">
  <h1>{{ order.event.name }}</h1>
  <ul class="list-group list-group-cart mb-3 pt-3">
    <li v-for="item in order.cart.items" :key="item.id" class="list-group-item d-flex justify-content-between align-items-center">
      <h5>{{ item.no_of_entries > 1 ? `${item.no_of_entries} x ` : '' }}{{ item.entry_name }}</h5>
        <template v-if="item.original_entries_total">
          <h5>
            Kr. {{ Number(item.original_entries_total).toLocaleString('nb') }}
          </h5>
        </template>
        <template v-else>
          <h5>
            Kr. {{ Number(item.entries_total).toLocaleString('nb') }}
          </h5>
        </template>
      </h5>
    </li>
    <li v-for="discount in order.discounts" :key="discount.id" class="list-group-item d-flex justify-content-between align-items-center">
      <h5>{{ discount.label }}</h5>
      <h5>
        -{{ Number(discount.discount) * 100 }} %
      </h5>
    </li>
    <li v-if="order.discounts.length" class="list-group-item d-flex justify-content-between align-items-center">
      <h5>Totalt</h5>
      <h5>
      Kr. {{ Number(order.order_total).toLocaleString('nb') }}
      </h5>
    </li>
  </ul>

  <form>
    <template v-if="order.event.form" v-for="input in order.event.form">
      <div class="form-group">
      <template v-if="input.type === 'textfield'">
        <label :for="input.fieldname">{{ input.fieldname }}</label>
        <input :id="input.fieldname" type="text" :name="input.fieldname" class="form-control" v-model="checkout.form[input.fieldname]" required>
      </template>
      <div v-if="input.type === 'checkbox'" class="form-check">
        <label class="form-check-label" :for="input.fieldname">{{ input.fieldname }}</label>
        <input type="checkbox" class="form-check-input" :id="input.fieldname" :name="input.fieldname" v-model="checkout.form[input.fieldname]" required>
      </div>
      <template v-if="input.type === 'select'">
        <label :for="input.fieldname">{{ input.fieldname }}</label>
        <select v-if="input.type === 'select'" :id="input.fieldname" :name="input.fieldname" class="form-control" v-model="checkout.form[input.fieldname]" required>
          <option v-for="(option, i) in input.options.split(',')" :key="option" :value="option" :selected="!checkout.form[input.fieldname] && i === 0">
            {{ option }}
          </option>
        </select>
      </template>
      </div>
    </template>
    <div v-if="!order.user.phone" class="form-group">
      <label class="form-check-label" for="phone">Telefonnummer</label>
      <input type="tel" class="form-control" id="phone" name="phone" v-model="checkout.phone">
    </div>
    <template v-if="order.user.needs_parental_consent">
      <div v-if="!order.user.parent_name" class="form-group">
        <label class="form-check-label" for="parent_name">Navn på foresatt / kontaktperson</label>
        <input type="text" class="form-control" id="parent_name" name="parent_name" v-model="checkout.parent_name">
      </div>
      <div v-if="!order.user.parent_phone" class="form-group">
        <label class="form-check-label" for="parent_phone">Telefonnummer foresatt / kontaktperson</label>
        <input type="tel" class="form-control" id="parent_phone" name="parent_phone" v-model="checkout.parent_phone">
      </div>
    </template>
    <div v-if="!order.user.birthday" class="form-group">
      <label class="form-check-label" for="birthday">Fødelsdag</label>
      <input type="text" class="form-control datepicker" id="birthday" name="birthday" v-model="checkout.birthday">
    </div>
    <div v-if="!order.user.adresse" class="form-group">
      <label class="form-check-label" for="address">Addresse</label>
      <input type="tel" class="form-control" id="address" name="address" v-model="checkout.adresse" placeholder="Din addresse">
    </div>
    <div v-if="!order.user.zip" class="form-group">
      <label class="form-check-label" for="zip">Postnummer</label>
      <input type="tel" class="form-control" id="zip" name="zip" v-model="checkout.zip" placeholder="Ditt postnummer">
    </div>
    <div class="form-check" v-if="order.user.no_newsletter !== '0'">
      <input type="checkbox" class="form-check-input" id="newsletters" name="newsletters" v-model="checkout.newsletter">
      <label class="form-check-label" for="newsletters">
        Jeg ønsker å motta nyheter fra NDT-LAN
      </label>
      </div>
  </form>
  <p v-if="order.user.needs_parental_consent">
    <br>
    Husk å laste ned og fyll ut foreldre ut <a href="/foreldreskriv" target="_blank">Foreldreskriv</a>. Husk å ta dette med deg når du ankommer arrangementet.<br>
    Om du ikke gjør dette kan du ikke være i lokalet etter kl. 00.00
  </p>
  <div v-if="!order.discounts.length" class="form-group">
    <label for="discount">Legg til rabattkode</label>
    <input id="discount" type="text" class="form-control border-radius-bottom-none" v-model="discount" placeholder="Rabattkode">
    <button @click="addDiscount" :disabled="!discount.length" class="btn btn-success btn-block border-radius-top-none">+ Legg til</button>
  </div>
</div>

