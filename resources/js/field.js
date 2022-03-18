Nova.booting((Vue, router, store) => {
  Vue.component('index-flexible-url-field', require('./components/IndexField'))
  Vue.component('detail-flexible-url-field', require('./components/DetailField'))
  Vue.component('form-flexible-url-field', require('./components/FormField'))
})
