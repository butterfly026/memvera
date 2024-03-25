<template>
  <div v-for="(attribute, index) in customAttributes" :key="index" class="mb-3">
    <label
      :for="attribute.code"
      class="form-label"
      :class="{ required: attribute.is_required }"
    >
      {{ attribute.name }}
      <span v-if="attribute.type == 'price'" class="currency-code"
        >({{ currencyCode }})</span
      >
    </label>
    <component
      :is="getComponent(attribute.type)"
      :attribute="attribute"
      :rules="validationRules(attribute)"
      :errors="errors"
      :data="data ? data[attribute.code] : null"
    />
  </div>
</template>

<script>
import { defineComponent, reactive, toRefs } from "vue";
import Text from "@/Components/attributes/edit/text.vue";
import Checkbox from "@/Components/attributes/edit/checkbox.vue";
import Textarea from "@/Components/attributes/edit/textarea.vue";
import Price from "@/Components/attributes/edit/price.vue";
import Select from "@/Components/attributes/edit/select.vue";
import Date from "@/Components/attributes/edit/date.vue";
import Lookup from "@/Components/attributes/edit/lookup.vue";
import Address from "@/Components/attributes/edit/address.vue";
import Email from "@/Components/attributes/edit/email.vue";
import Phone from "@/Components/attributes/edit/phone.vue";
// Import other components as needed

export default defineComponent({
  components: {
    Text,
    Checkbox,
    Textarea,
    Price,
    Select,
    Date,
    Lookup,
    Address,
    Email,
    Phone,
  },
  data() {
    return {
      values: {},
    };
  },
  methods: {
    handleInput(value, id) {
      this.values[id] = value;
      this.$emit("update:values", this.values);
    },
  },
  props: {
    customAttributes: Array,
    currencyCode: String,
    errors: Object,
    data: Object,
    customValidations: Object,
  },
  setup(props) {
    const { customAttributes } = toRefs(props);
    const formValues = reactive({});
    const componentsRefs = reactive({});

    const setAttributeRef = (el, index) => {
      componentsRefs[customAttributes.value[index].code] = el;
    };
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
        case "lookup":
          return "Lookup";
        case "address":
          return "Address";
        case "email":
          return "Email";
        case "phone":
          return "Phone";
        // Add other cases for different types
        default:
          return "div"; // Default or unknown types render nothing
      }
    };
    const validate = async () => {
      let isValid = true;
      for (const ref in componentsRefs) {
        if (componentsRefs[ref] && componentsRefs[ref].validate) {
          const valid = await componentsRefs[ref].validate();
          isValid = isValid && valid;
        }
      }
      return isValid;
    };
    const validationRules = (attribute) => {
      if (props.customValidations && props.customValidations[attribute.code]) {
        return props.customValidations[attribute.code].join("|");
      }
      let rules = "";
      if (attribute.is_required) {
        rules += "required|";
      }
      if (attribute.rules) {
        // Assuming validation is a string like 'required|email'
        rules += attribute.rules;
      }
      if (attribute.type == "price") rules += "decimal|";
      // Remove trailing '|'
      rules = rules.endsWith("|") ? rules.slice(0, -1) : rules;
      return rules;
    };
    return { formValues, setAttributeRef, getComponent, validationRules, validate };
  },
});
</script>
