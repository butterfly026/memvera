<template>
  <BOffcanvas
    class="border-0 sidebar-filter"
    id="filter-settings-offcanvas"
    header-class="d-flex align-items-center bg-primary bg-gradient p-3"
    body-class="p-0"
    z-index="1005"
    footer-class="offcanvas-footer border-top p-3 text-center"
    placement="end"
    v-model="sidebarFilter"
  >
    <template #header>
      <div class="me-2">
        <h5 class="m-0 me-2 text-white">{{ $t("ui.datagrid.filter.title") }}</h5>
      </div>
      <div class="right">
        <label @click="removeAll">
          {{ $t("ui.datagrid.filter.remove_all") }}
        </label>

        <button
          type="button"
          class="btn-close btn-close-white ms-auto"
          id="customizerclose-btn"
          @click="toggleSidebarFilter"
        ></button>
      </div>
    </template>
    <simplebar class="h-100"> 
      <template v-for="(data, key) in columns || tableData.columns">
        <div
          :class="`form-group  px-4 px-n4 mt-2 ${data.type == 'date_range' ? 'date' : ''}`"
          :key="key"
          v-if="data.type"
        >
          <label v-if="data.filterable">{{ data.label }}</label>
  
          <div class="field-container">
            <template v-if="data.filterable && data.type == 'integer_range'">
              <input
                type="text"
                placeholder="Start"
                class="control half"
                v-model="filterData[key].values[0]"
              />
  
              <span class="middle-text">to</span>
  
              <input
                type="text"
                placeholder="End"
                class="control half"
                v-model="filterData[key].values[1]"
              />
            </template>
  
            <template v-else-if="data.filterable && data.type == 'date_range'">
              <date-range-basic
                :date-range-key="key"
                :start-date="data.values[0]"
                :end-date="data.values[1]"
                @onChange="changeDateRange(key, $event)"
              ></date-range-basic>
            </template>
  
            <template v-else-if="data.filterable && data.type == 'dropdown'">
              <select class="control" @change="pushFieldValue(key, $event, data.index)">
                <option
                  :value="option.value"
                  :key="index"
                  v-for="(option, index) in data.dropdown_options"
                  :selected="option.selected"
                  :disabled="option.disabled"
                >
                  {{ option.label }}
                </option>
              </select>
  
              <div class="selected-options">
                <span
                  :key="index"
                  v-for="(value, index) in data.values"
                  class="badge badge-md badge-pill badge-secondary"
                >
                  {{ getFilteredValue(value, data) }}
  
                  <i
                    class="icon close-icon ri-close-circle-line ml-10"
                    @click="removeFieldValue(key, index, data.index)"
                  ></i>
                </span>
              </div>
            </template>
  
            <template v-else>
              <template v-if="data.filterable">
                <span
                  class="control"
                  @click="toggleInput(data.index)"
                  v-if="!addField[data.index]"
                >
                  <i class="icon add-icon ri-add-fill"></i> {{ data.label }}
                </span>
  
                <div class="enter-new" v-else>
                  <input
                    type="text"
                    class="control mb-10"
                    :placeholder="data.label"
                    :id="`enter-new-${data.index}`"
                    @keyup.enter="pushFieldValue(key, $event, data.index)"
                  />
                </div>
  
                <div class="selected-options">
                  <span
                    :key="index"
                    v-for="(value, index) in data.values"
                    class="badge badge-md badge-pill badge-secondary"
                  >
                    {{ getFilteredValue(value, data) }}
  
                    <i
                      class="icon close-icon ri-close-circle-line ml-10"
                      @click="removeFieldValue(key, index, data.index)"
                    ></i>
                  </span>
                </div>
              </template>
            </template>
  
            <i
              class="icon close-icon ri-close-circle-line"
              @click="
                removeFilter({
                  type: data.type,
                  key,
                  index: data.index,
                })
              "
              v-if="data.filterable"
            ></i>
          </div>
        </div>
      </template>
    </simplebar>
  </BOffcanvas>
</template>

<script>
import { mapState, mapActions } from "vuex";
import dateRangeBasic from "@/Components/date-range-basic.vue";
export default {
  props: ["columns"],
  components: {
    dateRangeBasic,
  },
  data: function () {
    return {
      addField: {},
    };
  },

  computed: {
    ...mapState("data", {
      filterData: (state) => state.filterData,
      tableData: (state) => state.tableData,
      sidebarFilter: (state) => state.sidebarFilter,
      filters: (state) => state.filters,
    }),
  },

  methods: {
    ...mapActions("data", ["toggleSidebarFilter", "updateFilterValues", "updatefilters"]),

    toggleInput: function (key, event) {
      this.addField[key] = !this.addField[key];

      //this.$forceUpdate();

      setTimeout(() => {
        document.getElementById(`enter-new-${key}`).focus();
      });
    },

    pushFieldValue: function (key, { target }, indexKey) {
      let targetValue = target.value.trim();

      this.addField[indexKey] = false;

      let values = (this.columns || this.tableData.columns)[key].values || [];

      if (values.indexOf(targetValue) == -1) {
        values.push(targetValue);

        this.updateFilterValues({
          key: indexKey,
          values,
        });

        target.value = "";
      }

      //this.$forceUpdate();
    },

    removeFieldValue: function (key, index, indexKey) {
      const values = (this.columns || this.tableData.columns)[key].values;

      values.splice(index, 1);

      this.updateFilterValues({
        key: indexKey,
        values,
      });

      //this.$forceUpdate();
    },

    removeAll: function () {
      if (this.filters.length !== undefined) {
        /**
         * For default table case.
         */
        this.updatefilters(
          this.filters.filter(
            (filter) => filter.column == "view_type" && filter.val == "table"
          )
        );
      } else {
        /**
         * For kanban case.
         *
         * To Do (@devansh-webkul): This needs to be supported by
         * all types present in the kanban columns. Currently added
         * for `created_at` because in `kanban-filter` need to
         * do some changes.
         */
        this.updateFilterValues({
          key: "created_at",
          values: ["", ""],
        });
      }

      this.resetAllDateRangePickers();

      //this.$forceUpdate();
    },

    removeFilter: function ({ type, key, index }) {
      let values = (this.columns || this.tableData.columns)[key].values;
      values = "";

      if (type === "date_range") this.resetSpecificDateRangePicker(key);

      this.updateFilterValues({
        key,
        values,
      });

      //this.$forceUpdate();
    },

    getFilteredValue: function (value, data) {
      if (data.type == "dropdown" && data.dropdown_options) {
        let dropdown_option = data.dropdown_options.filter(
          (option) => option.value == value
        );

        if (dropdown_option.length > 0) {
          return dropdown_option[0].label;
        }
      }

      return value;
    },

    changeDateRange: function (key, event) {
      setTimeout(() => {
        this.updateFilterValues({
          key,
          values: event,
          condition: "bw",
        });
      }, 0);
    },

    resetAllDateRangePickers: function () {
      let allDatePickers = document.querySelectorAll(".flatpickr-input");

      allDatePickers.forEach((datePicker) => {
        let fp = datePicker._flatpickr;

        fp.set("minDate", "");

        fp.set("maxDate", "");
      });

      $(allDatePickers).val("");
    },

    resetSpecificDateRangePicker: function (key) {
      let specificRangeDiv = document.querySelector(`#dateRange${key}`);

      let datePickers = specificRangeDiv.querySelectorAll(".flatpickr-input");

      datePickers.forEach((datePicker) => {
        let fp = datePicker._flatpickr;

        fp.set("minDate", "");

        fp.set("maxDate", "");
      });

      $(datePickers).val("");
    },
  },
};
</script>
