<script setup>

import {computed, defineProps, inject, onMounted, onUpdated, ref, watchEffect} from 'vue';
// import {echo} from "@/echo";
import {initializeEcho} from '@/services/echo';
import {userStore} from '@/stores';
import axiosClient from "@/axios";

const echo = initializeEcho(userStore().user.token);
const currentUser = userStore().user.data;
const isAdmin = ref(false); // Whether the user is an admin
const props = defineProps({
  user: {type: Object, default: null},
  conversation: {type: Object, default: null},
  isTyping: Boolean
});
const emitter_open_add_member = inject('emitter');


const updateUserOnlineStatus = (isOnline) => {
  props.user.is_online = isOnline;
};

onMounted(() => {

  echo.channel('user-online-status').listen('OnlineStatusUpdated', (event) => {
    const userId = event.user_id;
    const isOnline = event.is_online;
    updateUserOnlineStatus(isOnline);

  });
  checkIfAdmin();
});



const name = computed(() => {
  if (props.conversation && props.conversation.type === 'group') {
    return props.conversation.label.name;
  }
  return props.user.name;
})

const checkIfAdmin = () => {

  if (props.conversation && props.conversation.type === 'group') {
    axiosClient.get(`check-admin/${props.conversation.id}`)
        .then((response) => {
          isAdmin.value = response.data.is_admin;
          console.log(response.data.is_admin)
        })
        .catch((error) => {
          console.error('Error checking admin status:', error);
        });
  }
};

const openAddMember = () => {
  emitter_open_add_member.emit("open-add_member", props.conversation);
}
const openChatInfo = () => {
  emitter_open_add_member.emit("open-chat-info", props.conversation);

}
// console.log(props.user)
</script>

<template>
  <div class="chat-header border-bottom py-4 py-lg-7">
    <div class="row align-items-center">

      <!--      Object.keys( props.conversation).length === 0-->
      <!-- Mobile: close -->
      <div class="col-2 d-xl-none">
        <a class="icon icon-lg text-muted" href="#" data-toggle-chat="">
          <svg
              xmlns="http://www.w3.org/2000/svg"
              width="24"
              height="24"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
              class="feather feather-chevron-left"
          >
            <polyline points="15 18 9 12 15 6"></polyline>
          </svg>
        </a>
      </div>
      <!-- Mobile: close -->

      <!-- Content -->
      <div class="col-8 col-xl-12">
        <div class="row align-items-center text-center text-xl-start">
          <!-- Title -->
          <div class="col-12 col-xl-6">
            <div class="row align-items-center gx-5">
              <div class="col-auto">
                <div class="avatar  d-none d-xl-inline-block" :class="{'avatar-online' : user.is_online}">
                  <img
                      :src="Object.keys( props.conversation).length !== 0 ? conversation.image_url : user.avatar_url"
                      alt="#" class="avatar-img">
                </div>
              </div>

              <div class="col overflow-hidden">
                <h5 class="text-truncate">{{ name }}</h5>
                <p class="text-truncate" v-if="isTyping">
                  is typing<span class="typing-dots"
                ><span>.</span><span>.</span><span>.</span></span
                >
                </p>
              </div>
            </div>
          </div>
          <!-- Title -->

          <div v-if="conversation?.type === 'group'" class="col-xl-6 d-none d-xl-block">
            <div class="row align-items-center justify-content-end gx-6">
              <div class="col-auto">
                <!--                <a @click="openAddMember" href="#" class="icon icon-lg text-muted">-->
                <a @click="openChatInfo" href="#" class="icon icon-lg text-muted" data-bs-toggle="offcanvas"
                   data-bs-target="#offcanvas-more-group" aria-controls="offcanvas-more-group">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                       class="feather feather-more-horizontal">
                    <circle cx="12" cy="12" r="1"></circle>
                    <circle cx="19" cy="12" r="1"></circle>
                    <circle cx="5" cy="12" r="1"></circle>
                  </svg>
                </a>
              </div>

              <div class="col-auto">
                <div class="avatar-group">
                  <div v-for="(participant, index) in conversation.participants.slice(0, 3)" :key="index"
                       class="avatar avatar-sm">
                    <img :src="participant.avatar_url" alt="#" class="avatar-img">
                  </div>

                  <!-- Check if there are more avatars to display -->
                  <div v-if="conversation.participants.length > 3" class="avatar avatar-xs">
                    <span class="avatar-text">+{{ conversation.participants.length - 3 }}</span>
                  </div>

                  <a v-if="isAdmin" @click="openAddMember" href="#" class="avatar avatar-sm" data-bs-toggle="offcanvas"
                     data-bs-target="#offcanvas-add-members" aria-controls="offcanvas-add-members">
                                                            <span class="avatar-text" data-bs-toggle="tooltip"
                                                                  data-bs-placement="bottom" title=""
                                                                  data-bs-original-title="<strong>Add People</strong><p>Invite friends to group</p>">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                                     height="24" viewBox="0 0 24 24" fill="none"
                                                                     stroke="currentColor" stroke-width="2"
                                                                     stroke-linecap="round" stroke-linejoin="round"
                                                                     class="feather feather-plus"><line x1="12" y1="5"
                                                                                                        x2="12"
                                                                                                        y2="19"></line><line
                                                                    x1="5" y1="12" x2="19" y2="12"></line></svg>
                                                            </span>
                  </a>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
      <!-- Content -->

      <!-- Mobile: more -->
      <div class="col-2 d-xl-none text-end">
        <a
            href="#"
            class="icon icon-lg text-muted"
            data-bs-toggle="offcanvas"
            data-bs-target="#offcanvas-more"
            aria-controls="offcanvas-more"
        >
          <svg
              xmlns="http://www.w3.org/2000/svg"
              width="24"
              height="24"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
              class="feather feather-more-vertical"
          >
            <circle cx="12" cy="12" r="1"></circle>
            <circle cx="12" cy="5" r="1"></circle>
            <circle cx="12" cy="19" r="1"></circle>
          </svg>
        </a>
      </div>
      <!-- Mobile: more -->
    </div>
  </div>
</template>
