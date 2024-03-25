<template>
  <VField
    :name="attribute.code"
    :rules="rules"
    :label="attribute.name"
    v-model="textValue"
    v-slot="{ errors }"
  >
    <BFormInput
      v-model="textValue"
      :state="errors[attribute.code]"
      class="form-control"
      :class="{ 'is-invalid': errors.length }"
    ></BFormInput>
  </VField>
  <BFormInvalidFeedback v-if="errors[attribute.code]">{{ errors[attribute.code] }}</BFormInvalidFeedback>
</template>

<script>
export default {
  props: ["attribute", "rules", "errors", "data"],
  computed: {},
  data() {
    return {
      textValue: this.data ?? ''
    }
  },
};
</script>
