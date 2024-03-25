<template>
  <div class="d-flex align-items-center" :id="`dateRange${dateRangeKey}`">
    <div class="position-relative">
      <flat-pickr
        ref="startPicker"
        v-model="filterDateFrom"
        :config="config"
        class="form-control flatpickr-input flatpickr-disabled"
      ></flat-pickr>
      <i
        class="ri-calendar-2-line fs-20 position-absolute"
        style="right: 10px; top: 50%; transform: translateY(-50%)"
      ></i>
    </div>
    <span class="mx-2">to</span>
    <div class="position-relative">
      <flat-pickr
        ref="endPicker"
        v-model="filterDateTo"
        :config="config"
        class="form-control flatpickr-input flatpickr-disabled"
      ></flat-pickr>
      <i
        class="ri-calendar-2-line fs-20 position-absolute"
        style="right: 10px; top: 50%; transform: translateY(-50%)"
      ></i>
    </div>
  </div>
</template>

<script>
import flatPickr from "vue-flatpickr-component";
import "flatpickr/dist/flatpickr.css";
import { ref, watch } from "vue";
import { getCurrentDateString, getEndMonthDateString } from "@/common/utils";

export default {
  props: ["dateRangeKey", "startDate", "endDate"],
  components: {
    flatPickr,
  },
  data: function () {
    return {
      config: {
        allowInput: true,
        altFormat: "Y-m-d",
        dateFormat: "Y-m-d",
        weekNumbers: true,
      },
    };
  },

  setup(props) {
    const filterDateFrom = ref(null);
    const filterDateTo = ref(null);
    const dates = ref([props.startDate, props.endDate]);

    const startDatePicker = ref(null);
    const endDatePicker = ref(null);
    const updatePickerOptions = () => {
      if (startDatePicker.value && startDatePicker.value.fp) {
        startDatePicker.value.fp.set("maxDate", filterDateTo.value);
      }
      if (endDatePicker.value && endDatePicker.value.fp) {
        endDatePicker.value.fp.set("minDate", filterDateFrom.value);
      }
      if (filterDateFrom.value != null || filterDateTo.value != null) {
        const filterValue = `created_at[bw]=${
          filterDateFrom.value == null ? getCurrentDateString() : filterDateFrom.value
        },${filterDateTo.value == null ? getEndMonthDateString() : filterDateTo.value}`;
        getLeads(searchQuery.value, filterValue);
      }
    };
    const resetFilter = () => {
      filterDateFrom.value = null;
      filterDateTo.value = null;
    };
    watch(filterDateFrom, (newValue, oldValue) => {
      if (newValue !== oldValue) {
        updatePickerOptions();
        dates.value[0] = newValue;
        this.$emit("onChange", self.dates.value);
      }
    });

    watch(filterDateTo, (newValue, oldValue) => {
      if (newValue !== oldValue) {
        updatePickerOptions();
        dates.value[1] = newValue;
        this.$emit("onChange", self.dates.value);
      }
    });
    return {
      filterDateFrom,
      filterDateTo,
      startDatePicker,
      endDatePicker,
      dates,
      resetFilter,
      updatePickerOptions,
    };
  },
};
</script>
<style scoped>
.flatpickr-input {
    line-height: 2.3;
}
</style>