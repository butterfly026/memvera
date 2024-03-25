<template>
  <div class="attribute-value-row" v-for="(attribute, index) in customAttributes" :key="index">
    <div class="label">
      {{ attribute.name }}
    </div>
    <div class="value">
      <component
        :is="getComponent(attribute.type)"
        :attribute="attribute"
        :value="entity ? entity[attribute.code] : null"
      />
    </div>
  </div>
</template>

<script>
import { defineComponent, reactive, toRefs } from "vue";
import Text from "@/Components/attributes/view/text.vue";
import Checkbox from "@/Components/attributes/view/checkbox.vue";
import Textarea from "@/Components/attributes/view/textarea.vue";
import Price from "@/Components/attributes/view/price.vue";
import Select from "@/Components/attributes/view/select.vue";
import Date from "@/Components/attributes/view/date.vue";
// Import other components as needed

export default defineComponent({
  components: {
    Text,
    Checkbox,
    Textarea,
    Price,
    Select,
    Date,
  },
  data() {
    return {
      values: {},
    };
  },
  methods: {
  },
  props: {
    customAttributes: Array,
    currencyCode: String,
    entity: Object,
  },
  setup(props) {
    const getComponent = (type) => {
      switch (type) {
        case "text":
          return "Text";
        case "checkbox":
          return "Checkbox";
        case "textarea":
          return "Textarea";
        case "price":
          return "Price";
        case "select":
          return "Select";
        case "date":
          return "Date";
        // Add other cases for different types
        default:
          return "div"; // Default or unknown types render nothing
      }
    };
   
    return { getComponent };
  },
});
</script>
