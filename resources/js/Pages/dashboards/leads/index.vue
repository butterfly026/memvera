<script setup>
import { onMounted, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { getCurrentDateString, getEndMonthDateString } from '@/common/utils';
import animationData from "@/Components/widgets/json_data/msoeawqm.json";
import animationData1 from "@/Components/widgets/json_data/gsqxdxog.json";
// Assuming you have "pageLoaded" state you want to track
const pageLoaded = ref(true);
const leads_data = ref({
  blocks: [],
  stage_names: [],
  stages: [],
});
const blocks_table_data = ref([]);
const { props: pageProps } = usePage();
const searchQuery = ref('');
const filterDateFrom = ref(null);
const filterDateTo = ref(null);

const startPicker = ref(null);
const endPicker = ref(null);
const leadDisplayMode = ref(1);//1: layout , 2: table

// Define getLeads function
const getLeads = (searchedKeyword = '', filterValues = '') => {
  pageLoaded.value = false;

  // Assuming "pipeline_id" is a prop passed to the page component
  const pipelineId = pageProps.pipeline_id;

  // Construct the URL with optional search parameters  
  const baseUrl = route('dashboards.leads.get', pipelineId);
  let url = baseUrl;
  if (leadDisplayMode.value == 2)
    url = `${baseUrl}?view_type='table'`;
  if (url == baseUrl)
    url = url + `${searchedKeyword ? `?search=${searchedKeyword}` : ''}${filterValues ? `${searchedKeyword ? '&' : '?'}${filterValues}` : ''}`;
  else
    url = url + `${searchedKeyword ? `&search=${searchedKeyword}` : ''}${filterValues ? `${searchedKeyword ? '&' : '&'}${filterValues}` : ''}`;


  axios.get(url)
    .then(response => {
      if (leadDisplayMode.value == 1) {
        if (response.status == 200) {
          leads_data.value = response.data;
        } else {
          leads_data.value = {
            blocks: [],
            stage_names: [],
            stages: [],
          }
        }
      } else if (leadDisplayMode.value == 2) {
        if (response.status == 200) {
          blocks_table_data.value = response.data.records.data;
        } else {
          blocks_table_data.value = [];
        }
      }

    })
    .catch(error => {
      console.error(error);
      pageLoaded.value = true; // Ensure page is set as loaded even if fetching fails
    });
};

const debounce = (func, wait) => {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
};

const debouncedGetLeads = debounce(getLeads, 2000);

const getTotalValueOfBlocks = (blocks, stage) => {
  let total_price = 0;
  if (blocks.leads && blocks.leads.length > 0) {
    blocks.forEach((lead) => {
      console.log(lead.lead_value);
      if (lead.stage.id == stage.id)
        total_price += parseFloat(lead.lead_value);
    });
  }
  return total_price == 0 ? '' : ('$' + total_price.toFixed(2));
};
const getBlocksByStage = (stageId) => {
  if (!stageId || leads_data.value.blocks.length == 0) return [];
  return leads_data.value.blocks.filter(item => item.stage.id == stageId);
};
const dragMove = (event) => {
  const previousStageId = event.from.getAttribute('datastageid');
  const newStageId = event.to.getAttribute('datastageid');
  const blocks = getBlocksByStage(previousStageId);
  const movedLead = blocks[event.oldIndex];
  const newStage = leads_data.value.stages.find(stage => stage.id == newStageId);
  movedLead.stage = newStage;
  //dashboards.leads.update
  axios.put(route('dashboards.leads.update', movedLead.id), {
    lead_pipeline_stage_id: newStageId
  }).then((res) => {
    console.log(res);
  });
};

const updatePickerOptions = () => {
  if (startPicker.value && startPicker.value.fp) {
    startPicker.value.fp.set("maxDate", filterDateTo.value);
  }
  if (endPicker.value && endPicker.value.fp) {
    endPicker.value.fp.set("minDate", filterDateFrom.value);
  }
  if (filterDateFrom.value != null || filterDateTo.value != null) {
    const filterValue = `created_at[bw]=${filterDateFrom.value == null ? getCurrentDateString() : filterDateFrom.value},${filterDateTo.value == null ? getEndMonthDateString() : filterDateTo.value}`;
    getLeads(searchQuery.value, filterValue);
  }

};

const resetFilter = () => {
  filterDateFrom.value = null;
  filterDateTo.value = null;
}

const changeDisplayMode = (leadMode) => {
  leadDisplayMode.value = leadMode; 
  getLeads();
}

// Call getLeads when component is mounted/created
onMounted(() => {
  getLeads();
});

watch(searchQuery, (newValue) => {
  debouncedGetLeads(newValue);
});

// Watchers to react to changes in filterDateFrom and filterDateTo
watch(filterDateFrom, (newValue, oldValue) => {
  if (newValue !== oldValue) {
    updatePickerOptions();
  }
});

watch(filterDateTo, (newValue, oldValue) => {
  if (newValue !== oldValue) {
    updatePickerOptions();
  }
});

</script>

<script>
import { Head, Link } from '@inertiajs/vue3';
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";

import { VueDraggableNext } from 'vue-draggable-next';
import flatPickr from "vue-flatpickr-component";
import "flatpickr/dist/flatpickr.css";

import Lottie from "@/Components/widgets/lottie.vue";
import simplebar from "simplebar-vue"

export default {
  data() {
    return {
      modalShow: false,
      modalShow1: false,
      modalShow2: false,
      modalShow3: false,
      config: {
        wrap: true, // set wrap to true only when using 'input-group'
        altFormat: "M j, Y",
        altInput: true,
        dateFormat: "d M, Y",
      },
      enabled: true,
      dragging: false,
      addNewTask: null,
      pipelineType: 'Default Pipeline',
      pipelines: ['Default Pipeline'],
      defaultOptions: {
        animationData: animationData
      },
      defaultOptions1: {
        animationData: animationData1
      },
      showFilterWidget: false
    };
  },
  watch: {
  },
  methods: {
    toggleFilterWidget() {
      this.showFilterWidget = !this.showFilterWidget;
    },
  },
  components: {
    Layout,
    PageHeader,
    draggable: VueDraggableNext,
    lottie: Lottie,
    flatPickr,
    simplebar,
    Head, Link,
  },
};
</script>

<template>
  <Layout>

    <Head title="Leads Board" />
    <PageHeader title="Leads" pageTitle="Dashboards" />

    <BCard no-body>
      <BCardBody>
        <BRow class="g-2">
          <BCol lg="auto">
            <div class="hstack gap-2">

              <BButton variant="primary"><i class="ri-add-line align-bottom me-1"></i>
                <Link href="/apps/leads/create" class="text-reset">Create Lead
                </Link>
              </BButton>
            </div>
          </BCol>
          <BCol lg="3" class="col-auto">
            <div class="search-box">
              <input type="text" class="form-control search" placeholder="Search Here..." v-model="searchQuery"
                @keyup.enter="getLeads(searchQuery)">
              <i class="ri-search-line search-icon"></i>
              <i v-if="searchQuery" class="ri ri-close-line fs-24 search-close-icon" @click="searchQuery = ''"></i>
            </div>
          </BCol>
          <div class="col-auto ms-sm-auto">
            <BDropdown :text="pipelineType" variant="light" id="dropdownPipeline">
              <BDropdownItem v-for="(pl, index) in pipelines" :key="index">
                {{ pl }}
              </BDropdownItem>
            </BDropdown>

            <BButton-group role="group" aria-label="Basic radio toggle button group" class="lead-dispaly-mode">
              <input type="radio" class="btn-check" name="btnradio" id="dispModeLayout" autocomplete="off"
                :checked="leadDisplayMode == 1">
              <label class="btn btn-outline-secondary btn-lead-display-mode" for="dispModeLayout"
                @click="changeDisplayMode(1)">
                <i class="ri-layout-column-line fs-24"></i>
              </label>

              <input type="radio" class="btn-check" name="btnradio" id="dispModeTable" autocomplete="off"
                :checked="leadDisplayMode == 2">
              <label class="btn btn-outline-secondary btn-lead-display-mode" for="dispModeTable"
                @click="changeDisplayMode(2)
                ">
                <i class="ri-table-line fs-24"></i>
              </label>
            </BButton-group>

            <BButton variant="primary" class="btn-suffix-label waves-effect waves-light  lead-dispaly-mode"
              @click="toggleFilterWidget">
              <div class="" data-bs-toggle="offcanvas" data-bs-target="#theme-settings-offcanvas"
                aria-controls="theme-settings-offcanvas" id="mdi-cog1">

              </div>
              Filter
              <i class="ri-add-circle-line label-icon align-middle fs-16 me-2"></i>
            </BButton>
          </div>
        </BRow>
        <BRow>

        </BRow>
      </BCardBody>
    </BCard>

    <div class="tasks-board mb-3" id="kanbanboard" v-if="leadDisplayMode == 1">
      <div class="tasks-list" v-for="stage in leads_data.stages" :key="stage">
        <div class="task-header">
          <div class="d-flex mb-2">
            <div class="flex-grow-1">
              <span class="fs-18 fw-semibold pipeline-title mb-0"> {{ stage.name }}</span>
            </div>
            <div class="flex-shrink-0">
              <span style='color: #53c41a; font-size: 18px;'>{{ getTotalValueOfBlocks(leads_data.blocks, stage)
                }}</span>
            </div>
          </div>

          <div class="my-2">
            <Link :href="'/apps/leads/create?stage_id=' + stage.id"
              class="btn btn-soft-info btn-create-lead w-100 fs-18 text-reset">Create Lead
            </Link>
          </div>
        </div>

        <simplebar data-simplebar class="tasks-wrapper px-3 mx-n3">
          <div class="tasks">
            <draggable :list="getBlocksByStage(stage.id)" class="dragArea" group='test' @end="dragMove"
              :dataStageId="stage.id">
              <BCard no-body class="tasks-box" v-for="(lead, index) of getBlocksByStage(stage.id)" :key="index">
                <BCardBody>
                  <div class="d-flex mb-2 align-items-center">
                    <h6 class="fs-15 mb-0 flex-grow-1 text-truncate task-title"><a href="apps-tasks-details"
                        class="d-block">{{ lead.title }}</a></h6>
                    <div class="view-drag">
                      <a href="javascript:void(0);" class="text-muted" aria-expanded="false"><i
                          class="ri-eye-line fs-20"></i></a>
                      <a href="javascript:void(0);" class="text-muted" aria-expanded="false"><i
                          class="bx bx-crosshair fs-20"></i></a>
                    </div>
                  </div>
                  <div class="d-flex mb-2 align-items-center">
                    <i class="ri ri-user-line fs-24"></i>
                    <span class='fs-15 mx-2 lead-name'>{{ lead.name }}</span>
                  </div>
                  <div class="d-flex mb-2 align-items-center">
                    <i class="ri ri-currency-fill fs-24"></i>
                    <span class="fs-15 mx-2">{{ lead.lead_value }}</span>
                  </div>
                </BCardBody>
                <BCardFooter class="border-top-dashed">
                </BCardFooter>
              </BCard>
            </draggable>
          </div>
        </simplebar>
      </div>
    </div>

    <div class="table-responsive table-card" v-if="leadDisplayMode == 2">
      <table class="table align-middle" id="customerTable">
        <thead class="table-light">
          <tr>
            <th scope="col" style="width: 50px">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="checkAll" value="option" />
              </div>
            </th>
            <th class="sort" data-sort="name" @click="onSort('name')">ID</th>
            <th class="sort" data-sort="company_name" @click="onSort('company')">Sales Person</th>
            <th class="sort" data-sort="leads_score" @click="onSort('score')">Subject</th>
            <th class="sort" data-sort="phone" @click="onSort('phone')">Lead Value</th>
            <th class="sort" data-sort="location" @click="onSort('location')">Contact Person</th>
            <th class="sort" data-sort="tags" @click="onSort('tags')">Stage</th>
            <th class="sort" data-sort="date" @click="onSort('date')">Expected Close Date</th>
            <th class="sort" data-sort="action">Created Date</th>
            <th class="sort" data-sort="action">Actions</th>
          </tr>
        </thead>
        <tbody class="list form-check-all">
          <tr v-for="(data, index) of blocks_table_data" :key="index">
            <th scope="row">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="chk_child" value="option1" />
              </div>
            </th>
            <td class="id" style="display: none">
              <BLink href="javascript:void(0);" class="fw-medium link-primary">{{ data.id }}</BLink>
            </td>
            <td class="">
              {{ data.id }}
            </td>
            <td class="company_name" v-html="data.sales_person"></td>
            <td class="leads_score">{{ data.title }}</td>
            <td class="phone">{{ data.lead_value }}</td>
            <td class="location" v-html="data.person_name"></td>
            <td class="tags"  v-html="data.stage">
            </td>
            <td class="date">{{ data.expected_close_date }}</td>
            <td class="date">{{ data.created_at }}</td>
            <td>
              <ul class="list-inline hstack gap-2 mb-0">
                <li class="list-inline-item" @click="editDetails(data)">
                  <BLink class="edit-item-btn"><i class="ri-pencil-fill align-bottom text-muted"></i>
                  </BLink>
                </li>
                <li class="list-inline-item" @click="deleteModalToggle(data)">
                  <BLink class="remove-item-btn">
                    <i class="ri-delete-bin-fill align-bottom text-muted"></i>
                  </BLink>
                </li>
              </ul>
            </td>
          </tr>
        </tbody>
      </table>
      <div class="noresult" v-if="blocks_table_data.length < 1">
        <div class="text-center">
          <lottie class="avatar-xl" colors="primary:#121331,secondary:#08a88a" :options="defaultOptions" :height="75"
            :width="75" />
          <h5 class="mt-2">Sorry! No Result Found</h5>
          <p class="text-muted mb-0">
            We've searched more than 150+ Orders We did not find any
            orders for you search.
          </p>
        </div>
      </div>
      <div class="d-flex justify-content-end p-3" v-if="blocks_table_data.length >= 1">
        <div class="pagination-wrap hstack gap-2">
          <BLink class="page-item pagination-prev" href="#" :disabled="page <= 1" @click="page--"> Previous
          </BLink>
          <ul class="pagination listjs-pagination mb-0">
            <li :class="{ active: pageNumber == page, disabled: pageNumber == '...', }"
              v-for="(pageNumber, index) in pages" :key="index" @click="page = pageNumber">
              <BLink class="page" href="#" data-i="1" data-page="8">{{ pageNumber }}</BLink>
            </li>
          </ul>
          <!-- <BLink class="page-item pagination-next" href="#" :disabled="page >= pages.length" @click="page++">
            Next
          </BLink> -->
          <BLink class="page-item pagination-next" href="#" :disabled="true" @click="page++">
            Next
          </BLink>
        </div>
      </div>
    </div>


    <BOffcanvas class="border-0" id="filter-settings-offcanvas"
      header-class="d-flex align-items-center bg-primary bg-gradient p-3" body-class="p-0" z-index="1005"
      footer-class="offcanvas-footer border-top p-3 text-center" placement="end" v-model="showFilterWidget">
      <template #header>
        <div class="me-2">
          <h5 class="m-0 me-2 text-white">Filter</h5>
        </div>
        <button type="button" class="btn-close btn-close-white ms-auto" id="customizerclose-btn"
          @click="toggleFilterWidget"></button>
      </template>
      <simplebar class="h-100">
        <div class="p-4">
          <h6 class="mb-3 fw-bold">Created Date</h6>
          <div class="d-flex align-items-center">
            <div class="position-relative">
              <flat-pickr ref="startPicker" v-model="filterDateFrom"
                class="form-control flatpickr-input flatpickr-disabled"></flat-pickr>
              <i class="ri-calendar-2-line fs-20 position-absolute"
                style="right: 10px; top: 50%; transform: translateY(-50%);"></i>
            </div>
            <span class="mx-2">to</span>
            <div class="position-relative">
              <flat-pickr ref="endPicker" v-model="filterDateTo"
                class="form-control flatpickr-input flatpickr-disabled"></flat-pickr>
              <i class="ri-calendar-2-line fs-20 position-absolute"
                style="right: 10px; top: 50%; transform: translateY(-50%);"></i>
            </div>
          </div>

        </div>
      </simplebar>

      <template #footer>
        <BRow>
          <BCol cols="6">
            <BButton type="button" variant="light" class="w-100" id="reset-filter" @click="resetFilter()"> Reset
            </BButton>
          </BCol>
          <BCol cols="6">
            <BButton type="button" variant="primary" class="w-100"> Preview </BButton>
          </BCol>
        </BRow>
      </template>
    </BOffcanvas>
  </Layout>

</template>

<style lang="scss">
.search-close-icon {
  position: absolute;
  top: 0px;
  bottom: 0px;
  right: 18px;
  display: flex;
  align-items: center;
  color: var(--vz-secondary-color);
}

.lead-dispaly-mode {
  margin-left: 10px;
}

.btn-lead-display-mode {
  margin-bottom: 0px;
  padding: 1px 12px;
}

.btn-suffix-label {
  position: relative;
  padding-right: 44px;

  .label-icon {
    position: absolute;
    width: 35.5px;
    right: calc(var(--vz-border-width)* -1);
    top: calc(var(--vz-border-width)* -1);
    bottom: calc(var(--vz-border-width)* -1);
    background-color: rgba(255, 255, 255, 0.1);
    font-size: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-left: 5px;
    margin-right: 0px !important;
  }
}

.flatpickr-calendar .flatpickr-day.flatpickr-disabled {
  background: #D3D3D3 !important;
  /* Light gray background */
  color: #A9A9A9 !important;
  /* Dark gray text */
  cursor: not-allowed;
}
</style>