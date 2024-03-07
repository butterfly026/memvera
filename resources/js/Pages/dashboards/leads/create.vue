<script setup>
import { ref, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
const { props: pageProps } = usePage();
const req_stage_id = ref(pageProps.stage_id);
const getAttributeValues = (attr_name) => {
  if (!pageProps || !pageProps.customAttributes) return [];
  let retOptions = [];
  pageProps.customAttributes.forEach((attr) => {
    if (attr.lookup_type == attr_name) {
      retOptions = attr.options ?? [];
    }
  });
  return retOptions;
}
const getOrganizationValues = (attr_name) => {
  if (!pageProps || !pageProps.organizationAttributes) return [];
  let retOptions = [];
  pageProps.organizationAttributes.forEach((attr) => {
    if (attr.lookup_type == attr_name) {
      retOptions = attr.options ?? [];
    }
  });
  return retOptions;
}
</script>

<script>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";

import { VueDraggableNext } from 'vue-draggable-next';
import flatPickr from "vue-flatpickr-component";
import "flatpickr/dist/flatpickr.css";

import Lottie from "@/Components/widgets/lottie.vue";
import { leads } from "@/common/data";
import simplebar from "simplebar-vue"
import InputSearchList from '@/Components/InputSearchList.vue';
import ProductInfo from '@/Components/products/ProuctItemInput.vue';

export default {
  data() {
    return {
      config: {
        wrap: true, // set wrap to true only when using 'input-group'
        altFormat: "M j, Y",
        altInput: true,
        dateFormat: "d M, Y",
      },
      date: null,
      date1: null,
      enabled: true,
      dragging: false,
      customValidation: false,
      leads_data: leads,
      lead_sources: [],
      products: [],
      detail_info: {
        title: '',
        description: '',
        lead_value: '',
        lead_source_id: 0,
        lead_type_id: 0,
        lead_pipeline_id: 0,
        user_id: 0,
        lead_pipeline_stage_id: 1,
        expected_close_date: null,
      },
      person_info: {
        name: '',
        emails: [{ value: '', label: 'work' }],
        phones: [{ value: '', label: 'work' }],
        organization_id: null
      }
    };
  },
  mounted: () => {
  },
  methods: {
    submitCustomForm(req_stage_id) {
      this.customValidation = true;
      this.detail_info.lead_pipeline_stage_id = req_stage_id;
      const form = useForm({});
      form.transform(data => ({
        ...this.detail_info,
        person: this.person_info,
        entity_type: 'leads',
        status: 1,
        lead_pipeline_id: 1,
      })).post(route('dashboards.leads.store'), {
        onFinish: () => {
          // window.location.href = '/apps/leads';
        },
        onError: (error) => {

        }
      });

      // router.post(route('dashboards.leads.store'), {
      //   ...this.detail_info,
      //   person: this.person_info,
      //   entity_type: 'leads',
      //   status: 1,
      //   lead_pipeline_id: 1,
      // }, {
      //   preserveScroll: true,
      //   onSuccess: () => {

      //   },
      //   onFinish: () => {
      //   },
      // });
    },

    getTotalValueOfPipeline(lead_data) {
      let total_price = 0;
      if (lead_data.leads && lead_data.leads.length > 0) {
        lead_data.leads.forEach((lead) => {
          total_price += parseFloat(lead.lead_value);
        });
      }
      return total_price == 0 ? '' : ('$' + total_price.toFixed(2));
    },
    addEmail() {
      this.emails.push({ value: '', type: 'work' });
    },
    removeEmail(index) {
      this.emails.splice(index, 1);
    },
    addPhone() {
      this.phones.push({ number: '', type: 'work' });
    },
    removePhone(index) {
      this.phones.splice(index, 1);
    },
    addProduct() {
      this.products.push({ name: '', price: 0, quantity: 0 });
    },
    removeProduct(index) {
      this.products.splice(index, 1);
    },
    // async submitForm() {
    //   const formData = {
    //     emails: this.emails,
    //     phones: this.phones,
    //   };
    //   try {
    //     const response = await axios.post('YOUR_API_ENDPOINT', formData);
    //     console.log(response.data);
    //     // Handle response or redirect, etc.
    //   } catch (error) {
    //     console.error(error);
    //     // Handle error, show message, etc.
    //   }
    // },
  },
  components: {
    Layout,
    PageHeader,
    draggable: VueDraggableNext,
    lottie: Lottie,
    flatPickr,
    simplebar,
    Head, Link,
    InputSearchList,
    ProductInfo
  },
};
</script>

<template>
  <Layout>

    <Head title="Create Lead" />
    <PageHeader title="Create Lead" pageTitle="Leads" />
    <BForm @submit="() => { submitCustomForm(req_stage_id) }" class="row g-3" :validated="customValidation"
      :novalidate="true">
      <BContainer>
        <BRow class="justify-content-center">
          <BCol col lg="10">
            <BCard no-body>
              <BCardBody>
                <BCardHeader class="align-items-center d-flex border-bottom-0">
                  <BCardTitle class="mb-0 flex-grow-1"></BCardTitle>
                  <div class="flex-shrink-0">
                    <BButton type="submit" variant="primary"><i class="ri-add-line align-bottom me-1"></i>
                      Save as Lead</BButton>
                  </div>
                  <div class="flex-shrink-0">
                    <Link href="/apps/leads" class="btn btn-link waves-effect waves-light mx-2">Back</Link>
                  </div>
                </BCardHeader>
                <BTabs nav-class="nav-border-top nav-border-top-primary mb-3" content-class="text-muted">
                  <BTab title="Details" active>
                    <div class="live-preview p-3">
                      <div class="mb-3">
                        <label for="title" class="form-label required">Title</label>
                        <BFormInput type="text" class="form-control" id="title" required v-model="detail_info.title" />
                        <BFormInvalidFeedback>Please provide a Title.</BFormInvalidFeedback>
                      </div>
                      <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <BFormTextarea class="form-control" id="description" rows="5" v-model="detail_info.description">
                        </BFormTextarea>
                      </div>
                      <div class="mb-3">
                        <label for="leadValue" class="form-label required">Lead Value (US$)</label>
                        <BFormInput type="number" class="form-control" id="leadValue" required
                          v-model="detail_info.lead_value" />
                        <BFormInvalidFeedback>Please provide Lead Value.</BFormInvalidFeedback>
                      </div>
                      <div class="mb-3">
                        <label for="source" class="form-label required">Source</label>
                        <BFormSelect class="form-select" id="source" required v-model="detail_info.lead_source_id">
                          <option selected disabled value="0">Choose...</option>
                          <option v-for="(source, index) in getAttributeValues('lead_sources')" :value="source.id"
                            :key="index">
                            {{ source.name }}
                          </option>
                        </BFormSelect>
                        <BFormInvalidFeedback>Please select a valid Source.</BFormInvalidFeedback>
                      </div>
                      <div class="mb-3">
                        <label for="lead_type" class="form-label required">Type</label>
                        <BFormSelect class="form-select" id="lead_type" required v-model="detail_info.lead_type_id">
                          <option selected disabled value="0">Choose...</option>
                          <option v-for="(source, index) in getAttributeValues('lead_types')" :value="source.id"
                            :key="index">
                            {{ source.name }}
                          </option>
                        </BFormSelect>
                        <BFormInvalidFeedback>Please select a valid Type.</BFormInvalidFeedback>
                      </div>
                      <div class="mb-3">
                        <label for="salesOwner" class="form-label">Sales Owner</label>
                        <BFormSelect class="form-select" id="salesOwner" v-model="detail_info.user_id">
                          <option selected disabled value="0">Choose...</option>
                          <option v-for="(source, index) in getAttributeValues('users')" :value="source.id"
                            :key="index">
                            {{ source.name }}
                          </option>
                        </BFormSelect>
                      </div>
                      <div class="mb-3">
                        <label for="closeDate" class="form-label">Expected Close Date</label>
                        <flat-pickr v-model="date1" class="form-control"></flat-pickr>
                        <BFormInvalidFeedback>Please select a valid Date.</BFormInvalidFeedback>
                      </div>
                    </div>
                  </BTab>
                  <BTab title="Contact Person">
                    <div class="live-preview p-3">
                      <div class="mb-3">
                        <label for="contactName" class="form-label required">Name</label>
                        <BFormInput type="text" class="form-control" id="contactName" required
                          v-model="person_info.name" />
                        <BFormInvalidFeedback>Please provide a Name.</BFormInvalidFeedback>
                      </div>

                      <div class="mb-3">
                        <label for="contactEmails" class="form-label">Email</label>
                        <BInputGroup v-for="(email, index) in person_info.emails" :key="index"
                          class="mb-2 d-flex align-items-center">
                          <BFormInput type="tel" id="contactEmails" class="form-control me-2" v-model="email.value"
                            aria-label="Text input with 2 dropdown buttons" required>
                          </BFormInput>
                          <BDropdown variant="outline-secondary" :text="email.label == 'work' ? 'Work' : 'Home'"
                            class="me-2 dropdown-menu-end" style="min-width: 120px;">
                            <BDropdownItem @click="(e) => { email.label = 'work' }">Work</BDropdownItem>
                            <BDropdownItem @click="(e) => { email.label = 'home' }">Home</BDropdownItem>
                          </BDropdown>
                          <BButton variant="danger" class="btn-icon waves-effect waves-light"
                            v-if="person_info.emails.length > 1" @click="removeEmail(index)"><i
                              class="ri-delete-bin-5-line"></i></BButton>
                        </BInputGroup>
                        <BButton type="button" variant="link" @click="addEmail">
                          + Add More
                        </BButton>
                      </div>

                      <div class="mb-3">
                        <label for="contactNumbers" class="form-label">Contact Numbers</label>
                        <BInputGroup v-for="(phone, index) in person_info.phones" :key="index"
                          class="mb-2 d-flex align-items-center">
                          <BFormInput type="tel" id="contactNumbers" class="form-control me-2" v-model="phone.value"
                            aria-label="Text input with 2 dropdown buttons" required>
                          </BFormInput>
                          <BDropdown variant="outline-secondary" :text="phone.label == 'work' ? 'Work' : 'Home'"
                            class="me-2 dropdown-menu-end" style="min-width: 120px;">
                            <BDropdownItem @click="(e) => { phone.label = 'work' }">Work</BDropdownItem>
                            <BDropdownItem @click="(e) => { phone.label = 'home' }">Home</BDropdownItem>
                          </BDropdown>
                          <BButton variant="danger" class="btn-icon waves-effect waves-light"
                            v-if="person_info.phones.length > 1" @click="removePhone(index)"><i
                              class="ri-delete-bin-5-line"></i></BButton>
                        </BInputGroup>
                        <BButton type="button" variant="link" @click="addPhone">
                          + Add More
                        </BButton>
                      </div>

                      <div class="mb-3">
                        <label for="organization" class="form-label">Organization</label>
                        <InputSearchList placeholderText="Start typing to such records"
                          :arrays="getOrganizationValues('organizations')"
                          noSuggestionMsg="Records not found with the same name"
                          v-model="person_info.organization_id" />
                      </div>
                    </div>
                  </BTab>
                  <BTab title="Products">
                    <div class="live-preview p-3">
                      <ProductInfo v-for="(product, index) in products" :key="index" v-model="products[index]"
                        @remove="removeProduct(index)" />
                      <BButton type="button" variant="link" @click="addProduct">
                        + Add More
                      </BButton>
                    </div>
                  </BTab>

                </BTabs>
              </BCardBody>
            </BCard>
          </BCol>
        </BRow>
      </BContainer>
    </BForm>

  </Layout>
</template>