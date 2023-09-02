<script setup>

import {inject, onMounted, ref} from "vue";
import axiosClient from "@/axios";

let conversationRef = ref(null);
let non_participants = ref([]);
let selectedUserIds = ref([]);

const emitter_open_add_member = inject('emitter');

onMounted(() => {
  emitter_open_add_member.on("open-add_member", async (conversation) => {
    conversationRef.value = conversation;
    getUsersNotParticipants();
  })
})

const getUsersNotParticipants = () => {
  axiosClient.get(`conversation/${conversationRef.value.id}/non-participants`)
      .then((response) => {
        console.log(response.data.nonParticipants)
        non_participants.value = response.data.nonParticipants;
      }).catch((error) => {
    console.log(error)
  })
}

const addMemberToConversation = () =>{

  // console.log(selectedUserIds.value)
  axiosClient.post(`/conversation/${conversationRef.value.id}/add-participants`, { user_ids: selectedUserIds.value })
      .then(response => {
        console.log(response.data.message);
        toastr.success(response.data.message);
        // Refresh the array of non-participants
        getUsersNotParticipants();
      })
      .catch(error => {
        console.error('Error adding participants:', error);
      });
}

</script>

<template>
  <div class="offcanvas offcanvas-end offcanvas-aside" data-bs-scroll="true" data-bs-backdrop="false" tabindex="-1"
       id="offcanvas-add-members" style="visibility: hidden;" aria-hidden="true">
    <!-- Offcanvas Header -->
    <div class="offcanvas-header py-4 py-lg-7 border-bottom ">
      <a class="icon icon-lg text-muted" href="#" data-bs-dismiss="offcanvas">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
             class="feather feather-chevron-left">
          <polyline points="15 18 9 12 15 6"></polyline>
        </svg>
      </a>

      <div class="visibility-xl-invisible overflow-hidden text-center">
        <h5 class="text-truncate">Members</h5>
        <p class="text-truncate">Add new members</p>
      </div>

      <a class="icon icon-lg text-muted collapsed" data-bs-toggle="collapse" href="#search-members" role="button"
         aria-expanded="false" aria-controls="search-members" onclick="event.preventDefault();">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
             class="feather feather-filter">
          <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
        </svg>
      </a>
    </div>
    <!-- Offcanvas Header -->

    <!-- Offcanvas Body -->
    <div class="offcanvas-body hide-scrollbar py-0">

      <!-- Search -->
      <div class="collapse" id="search-members" style="">
        <div class="border-bottom py-6">

          <form action="#">
            <div class="input-group">
              <div class="input-group-text" id="search-user">
                <div class="icon icon-lg">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                       class="feather feather-search">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                  </svg>
                </div>
              </div>

              <input type="text" class="form-control form-control-lg ps-0" placeholder="User name or phone"
                     aria-label="User name or phone" aria-describedby="search-user">
            </div>
          </form>

        </div>
      </div>
      <!-- Search -->

      <!-- Members -->
      <ul class="list-group list-group-flush">

        <li v-for="user in non_participants" :key="user.id"  class="list-group-item">
          <div class="row align-items-center gx-5">
            <div class="col-auto">
              <div class="avatar ">
                <img class="avatar-img" :src="user.avatar_url" alt="">
              </div>
            </div>
            <div class="col">
              <h5>{{user.name}}</h5>
<!--              <p>last seen 3 days ago</p>-->
            </div>
            <div class="col-auto">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" :value="user.id" :id="'id-add-user-'+user.id" v-model="selectedUserIds">
                <label class="form-check-label" :for="'id-add-user-'+user.id"></label>
              </div>
            </div>
          </div>
          <label class="stretched-label" :for="'id-add-user-'+user.id"></label>
        </li>


      </ul>
      <!-- Members -->
    </div>
    <!-- Offcanvas Body -->

    <!-- Offcanvas Footer -->
    <div class="offcanvas-footer border-top py-4 py-lg-7">
      <a href="#" @click.prevent="addMemberToConversation()" class="btn btn-lg btn-primary w-100 d-flex align-items-center">
        Add members

        <span class="icon ms-auto">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                 stroke-linejoin="round" class="feather feather-chevron-right"><polyline
                                points="9 18 15 12 9 6"></polyline></svg>
                        </span>
      </a>
    </div>
    <!-- Offcanvas Footer -->
  </div>
</template>


<style scoped>

</style>