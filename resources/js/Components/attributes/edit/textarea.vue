<template>
  <VField
    :name="attribute.code"
    :rules="rules"
    :label="attribute.name"
    v-model="textValue"
    v-slot="{ errors }"
  >
  <BFormTextarea
      v-model="textValue"
      :state="errors[attribute.code]"
      class="form-control"
      :class="{ 'is-invalid': errors.length }"
    ></BFormTextarea>
  </VField>
  <div class="invalid-feedback">{{ errors[attribute.code] }}</div>
</template>

<script>
export default {
  props: ["attribute", "rules", "errors", "data"],
  data() {
    return {
      textValue: this.data ?? ''
    }
  },
  computed: {},
};
</script>
