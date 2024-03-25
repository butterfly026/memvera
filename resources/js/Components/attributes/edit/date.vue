<template>
  <div class="position-relative">
    <VField
      :name="attribute.code"
      :rules="rules"
      :label="attribute.name"
      v-model="dateValue"
      v-slot="{ errors }"
    >
      <flat-pickr
        :name="attribute.code"
        :modelValue="dateValue"
        @update:modelValue="(value) => (dateValue = value)"
        :state="errors[attribute.code]"
        class="form-control flatpickr-input"
        :class="{ 'is-invalid': errors.length }"
      ></flat-pickr>
      <i
        class="ri-calendar-2-line fs-20 position-absolute"
        style="right: 10px; top: 50%; transform: translateY(-50%)"
      ></i>
    </VField>
  </div>
  <BFormInvalidFeedback v-if="errors[attribute.code]">{{
    errors[attribute.code]
  }}</BFormInvalidFeedback>
</template>

<script>
import flatPickr from "vue-flatpickr-component";
import "flatpickr/dist/flatpickr.css";
import { useField } from "vee-validate";
export default {
  components: {
    flatPickr,
  },
  props: ["attribute", "rules", "errors", "data"],
  data() {
    return {
      dateValue: this.data ?? null,
    };
  },
  computed: {},
};
</script>
