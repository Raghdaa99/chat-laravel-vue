<script setup>
import {inject, onMounted, onUpdated, ref, watch, watchEffect} from "vue";
import axiosClient from "@/axios";

let conversationRef = ref(null);
let participants = ref([]);
let images = ref([]);
let files = ref([]);
let offcanvas = ref('');
const emitter_open_add_member = inject('emitter');
const isAdmin = ref(false);

onMounted(() => {
  emitter_open_add_member.on("open-chat-info", async (conversation) => {
    conversationRef.value = conversation;
    // participants.value = conversation.participants;
    console.log("open-add_member", conversation)
    getParticipants();
    imagesConversation();
    checkIfAdmin();
  })
})

const imagesConversation = () => {
  axiosClient.get('get-images-conversation/' + conversationRef.value.id)
      .then((response) => {
        console.log(response.data)
        images.value = response.data.attachmentImages;
        files.value = response.data.attachmentFiles;

      }).catch((error) => {
    console.log(error)
  })
}

const getParticipants = () => {
  axiosClient.get(`conversation/${conversationRef.value.id}/participants`)
      .then((response) => {
        participants.value = response.data.participants;
        console.log(response.data.participants)
      }).catch((error) => {
    console.log(error)
  })
}

const checkIfAdmin = () => {

  axiosClient.get(`check-admin/${conversationRef.value.id}`)
      .then((response) => {
        isAdmin.value = response.data.is_admin;
        console.log(response.data.is_admin)
      })
      .catch((error) => {
        console.error('Error checking admin status:', error);

      });

};

const deleteParticipant = (participant) => {
  axiosClient.delete(`/conversation/${conversationRef.value.id}/participants/${participant.id}`)
      .then((res) => {
        toastr.success(res.data.message)
        getParticipants()
      }).catch((error) => {
    console.log(error.data.message)
  })
}

const leaveGroupConversation = () => {
  axiosClient.post(`/conversation/${conversationRef.value.id}/leave`)
      .then((res) => {
        toastr.success(res.data.message)
        getParticipants()
      }).catch((error) => {
    console.log(error.data.message)
  })
}
</script>

<template>

  <div class="offcanvas offcanvas-end offcanvas-aside" data-bs-scroll="true"
       data-bs-backdrop="false" tabindex="-1"
       id="offcanvas-more-group" style="visibility: hidden;" aria-hidden="true" ref="offcanvas">

    <!--    <div class="offcanvas offcanvas-end offcanvas-aside show" data-bs-scroll="true" data-bs-backdrop="false"-->
    <!--         tabindex="-1" id="offcanvas-more-group" style="visibility: visible;" aria-modal="true" role="dialog">-->


    <!-- Offcanvas Header -->
    <div v-if="conversationRef" class="offcanvas-header py-4 py-lg-7 border-bottom">
      <a class="icon icon-lg text-muted" href="#" data-bs-dismiss="offcanvas">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
             class="feather feather-chevron-left">
          <polyline points="15 18 9 12 15 6"></polyline>
        </svg>
      </a>

      <div class="visibility-xl-invisible overflow-hidden text-center">
        <h5 class="text-truncate">Bootstrap Community</h5>
        <p class="text-truncate">45 members, 9 online</p>
      </div>

      <!-- Dropdown -->
      <div class="dropdown">
        <a class="icon icon-lg text-muted" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
               class="feather feather-more-vertical">
            <circle cx="12" cy="12" r="1"></circle>
            <circle cx="12" cy="5" r="1"></circle>
            <circle cx="12" cy="19" r="1"></circle>
          </svg>
        </a>

        <ul class="dropdown-menu">
          <li>
            <a href="#" class="dropdown-item d-flex align-items-center">
              Edit
              <div class="icon ms-auto">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     class="feather feather-edit-3">
                  <path d="M12 20h9"></path>
                  <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                </svg>
              </div>
            </a>
          </li>
          <li>
            <a href="#" class="dropdown-item d-flex align-items-center">
              Mute
              <div class="icon ms-auto">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     class="feather feather-bell">
                  <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                  <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                </svg>
              </div>
            </a>
          </li>
          <li>
            <hr class="dropdown-divider">
          </li>
          <li>
            <a href="#" class="dropdown-item d-flex align-items-center text-danger" @click="leaveGroupConversation">
              Leave
              <div class="icon ms-auto">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     class="feather feather-log-out">
                  <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                  <polyline points="16 17 21 12 16 7"></polyline>
                  <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
              </div>
            </a>
          </li>
        </ul>
      </div>
    </div>
    <!-- Offcanvas Header -->

    <!-- Offcanvas Body -->
    <div v-if="conversationRef" class="offcanvas-body hide-scrollbar">
      <!-- Avatar -->
      <div class="text-center py-10">
        <div class="row gy-6">
          <div class="col-12">
            <div class="avatar avatar-xl mx-auto">
              <img :src="conversationRef.image_url" alt="#"
                   class="avatar-img">
            </div>
          </div>

          <div class="col-12">
            <h4>{{ conversationRef.label.name }}</h4>
            <p>{{ conversationRef.label?.desc }}</p>
          </div>
        </div>
      </div>
      <!-- Avatar -->

      <!-- Tabs -->
      <ul class="nav nav-pills nav-justified" role="tablist">
        <li class="nav-item">
          <a class="nav-link active" data-bs-toggle="pill" href="#offcanvas-group-tab-members" role="tab"
             aria-controls="offcanvas-group-tab-members" aria-selected="true">
            People
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link" data-bs-toggle="pill" href="#offcanvas-group-tab-media" role="tab"
             aria-controls="offcanvas-group-tab-media" aria-selected="false">
            Media
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link" data-bs-toggle="pill" href="#offcanvas-group-tab-files" role="tab"
             aria-controls="offcanvas-group-tab-files" aria-selected="false">
            Files
          </a>
        </li>
      </ul>
      <!-- Tabs -->

      <!-- Tabs: Content -->
      <div class="tab-content py-2" role="tablist">
        <!-- Members -->
        <div class="tab-pane fade active show" id="offcanvas-group-tab-members" role="tabpanel">
          <ul class="list-group list-group-flush">
            <li v-for="participant in participants" :key="participant.id" class="list-group-item">
              <div class="row align-items-center gx-5">
                <!-- Avatar -->
                <div class="col-auto">
                  <a href="#" class="avatar">
                    <img class="avatar-img" :src="participant.avatar_url" alt="">
                  </a>
                </div>
                <!-- Avatar -->

                <!-- Text -->
                <div class="col">
                  <h5><a href="#">{{ participant.name }}</a></h5>
                  <!--                  <p>online</p>-->
                </div>
                <!-- Text -->
                <!--{{participant.pivot.role}}-->
                <!-- Owner -->
                <div v-if="participant.pivot.role === 'admin'" class="col-auto">
                  <span class="extra-small text-primary">owner</span>
                </div>

                <!-- Dropdown -->
                <div v-if="isAdmin" class="col-auto">
                  <div class="dropdown">
                    <a class="icon text-muted" href="#" role="button" data-bs-toggle="dropdown"
                       aria-expanded="false">
                      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                           stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                           class="feather feather-more-vertical">
                        <circle cx="12" cy="12" r="1"></circle>
                        <circle cx="12" cy="5" r="1"></circle>
                        <circle cx="12" cy="19" r="1"></circle>
                      </svg>
                    </a>

                    <ul class="dropdown-menu">
                      <li>
                        <a class="dropdown-item d-flex align-items-center" href="#">
                          Promote
                          <div class="icon ms-auto">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                 stroke-linejoin="round" class="feather feather-trending-up">
                              <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                              <polyline points="17 6 23 6 23 12"></polyline>
                            </svg>
                          </div>
                        </a>
                      </li>
                      <li>
                        <a class="dropdown-item d-flex align-items-center" href="#">
                          Restrict
                          <div class="icon ms-auto">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                 stroke-linejoin="round" class="feather feather-trending-down">
                              <polyline points="23 18 13.5 8.5 8.5 13.5 1 6"></polyline>
                              <polyline points="17 18 23 18 23 12"></polyline>
                            </svg>
                          </div>
                        </a>
                      </li>
                      <li>
                        <hr class="dropdown-divider">
                      </li>
                      <li>
                        <a class="dropdown-item d-flex align-items-center text-danger" href="#"
                           @click="deleteParticipant(participant)">
                          Delete
                          <div class="icon ms-auto">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                 stroke-linejoin="round" class="feather feather-trash-2">
                              <polyline points="3 6 5 6 21 6"></polyline>
                              <path
                                  d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                              <line x1="10" y1="11" x2="10" y2="17"></line>
                              <line x1="14" y1="11" x2="14" y2="17"></line>
                            </svg>
                          </div>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
                <!-- Dropdown -->
              </div>
            </li>
          </ul>
        </div>
        <!-- Members -->

        <!-- Media -->
        <div class="tab-pane fade" id="offcanvas-group-tab-media" role="tabpanel">
          <div class="row row-cols-3 g-3 py-6">
            <div v-for="image in images" class="col">
              <!--              <a :href="image" data-bs-toggle="modal" data-bs-target="#modal-media-preview"-->
              <!--                 :data-theme-img-url="image">-->
              <img class="img-fluid rounded" :src="image.file_path" alt="" data-action="zoom">
              <!--              </a>-->
            </div>
          </div>
        </div>
        <!-- Media -->

        <!-- Files -->
        <div class="tab-pane fade" id="offcanvas-group-tab-files" role="tabpanel">
          <ul class="list-group list-group-flush">

            <!-- Item -->
            <li v-for="file in files" class="list-group-item">
              <div class="row align-items-center gx-5">
                <!-- Icons -->
                <div class="col-auto">
                  <div class="avatar-group">


                    <a href="#" class="avatar avatar-sm">
                                                    <span class="avatar-text bg-primary">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                             viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                             stroke-width="2" stroke-linecap="round"
                                                             stroke-linejoin="round" class="feather feather-file-text"><path
                                                            d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline
                                                            points="14 2 14 8 20 8"></polyline><line x1="16" y1="13"
                                                                                                     x2="8"
                                                                                                     y2="13"></line><line
                                                            x1="16" y1="17" x2="8" y2="17"></line><polyline
                                                            points="10 9 9 9 8 9"></polyline></svg>
                                                    </span>
                    </a>
                  </div>
                </div>
                <!-- Icons -->

                <!-- Text -->
                <div class="col overflow-hidden">
                  <h5 class="text-truncate">
                    <a href="#">{{ file.file_name }}</a>
                  </h5>
                  <ul class="list-inline m-0">
                    <li class="list-inline-item">
                      <small class="text-uppercase text-muted">79.2 KB</small>
                    </li>

                  </ul>
                </div>
                <!-- Text -->

                <!-- Dropdown -->
                <div class="col-auto">
                  <div class="dropdown">
                    <a class="icon text-muted" href="#" role="button" data-bs-toggle="dropdown"
                       aria-expanded="false">
                      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                           stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                           class="feather feather-more-vertical">
                        <circle cx="12" cy="12" r="1"></circle>
                        <circle cx="12" cy="5" r="1"></circle>
                        <circle cx="12" cy="19" r="1"></circle>
                      </svg>
                    </a>

                    <ul class="dropdown-menu">
                      <li>
                        <a class="dropdown-item d-flex align-items-center" href="#">
                          Download
                          <div class="icon ms-auto">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                 stroke-linejoin="round" class="feather feather-download">
                              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                              <polyline points="7 10 12 15 17 10"></polyline>
                              <line x1="12" y1="15" x2="12" y2="3"></line>
                            </svg>
                          </div>
                        </a>
                      </li>
                      <li>
                        <a class="dropdown-item d-flex align-items-center" href="#">
                          Share
                          <div class="icon ms-auto">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                 stroke-linejoin="round" class="feather feather-share-2">
                              <circle cx="18" cy="5" r="3"></circle>
                              <circle cx="6" cy="12" r="3"></circle>
                              <circle cx="18" cy="19" r="3"></circle>
                              <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line>
                              <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line>
                            </svg>
                          </div>
                        </a>
                      </li>
                      <li>
                        <hr class="dropdown-divider">
                      </li>
                      <li>
                        <a class="dropdown-item d-flex align-items-center text-danger" href="#">
                          <span class="me-auto">Delete</span>
                          <div class="icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                 stroke-linejoin="round" class="feather feather-trash-2">
                              <polyline points="3 6 5 6 21 6"></polyline>
                              <path
                                  d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                              <line x1="10" y1="11" x2="10" y2="17"></line>
                              <line x1="14" y1="11" x2="14" y2="17"></line>
                            </svg>
                          </div>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
                <!-- Dropdown -->
              </div>
            </li>
          </ul>
        </div>
        <!-- Files -->
      </div>
      <!-- Tabs: Content -->
    </div>
    <!-- Offcanvas Body -->
  </div>

</template>