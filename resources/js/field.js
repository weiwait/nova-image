Nova.booting((Vue, router, store) => {
  Vue.component('index-nova-image', require('./components/IndexField'))
  Vue.component('detail-nova-image', require('./components/DetailField'))
  Vue.component('form-nova-image', require('./components/FormField'))
})
