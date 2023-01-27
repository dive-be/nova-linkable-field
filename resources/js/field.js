import IndexField from "./components/IndexField.vue";
import DetailField from "./components/DetailField.vue";
import FormField from "./components/FormField.vue";

Nova.booting((Vue, router, store) => {
  Vue.component("index-flexible-url-field", IndexField);
  Vue.component("detail-flexible-url-field", DetailField);
  Vue.component("form-flexible-url-field", FormField);
});
