<script setup>

import {defineProps, onMounted, onUpdated, ref} from 'vue';
import moment from "moment";
import {userStore} from "@/stores";
import axiosClient from "@/axios";

const emit = defineEmits(['deleteMessage']);
const messagesContainer = ref(null); // Reference to the messages container
const props = defineProps({
  user: Object,
  conversation: Object,
  messages: Array

});

// Method to scroll to the bottom
const scrollToBottom = () => {

  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
  }
};

onMounted(() => {
  scrollToBottom();

})

onUpdated(() => {
  scrollToBottom();
});


const isAuthUserMessage = (message) => {
  return message.user_id === userStore().user.data.id;
};


const formatFileSize = (fileSize) => {
  if (fileSize < 1024) {
    return `${fileSize} B`;
  } else if (fileSize < 1024 * 1024) {
    return `${(fileSize / 1024).toFixed(2)} KB`;
  } else {
    return `${(fileSize / (1024 * 1024)).toFixed(2)} MB`;
  }
}

const deleteMessage = (message_id) => {

  emit("deleteMessage",message_id);
}

</script>


<template>
  <div class="chat-body hide-scrollbar flex-1 h-100 mb-10" ref="messagesContainer">

    <div class="chat-body-inner">
      <div class="py-6 py-lg-12">
        <!-- Message -->
        <div v-for="(message, index) in messages" :key="message.id" class="message"
             :class="{ 'message-out': isAuthUserMessage(message) }">
          <!--          <template >-->
          <a href="#" data-bs-toggle="modal" data-bs-target="#modal-user-profile" class="avatar avatar-responsive">
            <img class="avatar-img" :src="message.user.avatar_url" alt="">
          </a>
          <!--          </template>-->

          <div class="message-inner">
            <div class="message-body">
              <div class="message-content">
                <div class="message-text" v-if="message.type === 'text'">
                  <p>{{ message.body }}</p>
                </div>
                <div v-else class="row">
                  <div v-if="!message.body.mimetype.match(/image\/.+/)" class="message-text">
                    <div class="row align-items-center gx-4">
                      <div class="col-auto">
                        <a :href="message.attachment_url" target="_blank"
                           class="avatar avatar-sm">
                          <div class="avatar-text bg-white text-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                 stroke-linejoin="round" class="feather feather-arrow-down">
                              <line x1="12" y1="5" x2="12" y2="19"></line>
                              <polyline points="19 12 12 19 5 12"></polyline>
                            </svg>
                          </div>
                        </a>
                      </div>
                      <div class="col overflow-hidden">
                        <h6 class="text-truncate text-reset">
                          <a href="#" class="text-reset">{{ message.body.file_name }}</a>
                        </h6>
                        <ul class="list-inline text-uppercase extra-small opacity-75 mb-0">
                          <li class="list-inline-item">{{ formatFileSize(message.body.file_size) }}</li>
                        </ul>
                      </div>
                    </div>
                  </div>

                  <div v-else class="col">
                    <img class="img-fluid rounded smaller-image"
                         :src="message.attachment_url" data-action="zoom" alt="">
                  </div>

                </div>
                <!-- Dropdown -->
                <div v-if="isAuthUserMessage(message)" class="message-action">
                  <div class="dropdown">
                    <a class="icon text-muted" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
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
                        <hr class="dropdown-divider">
                      </li>
                      <li>
                        <a class="dropdown-item d-flex align-items-center text-danger" href="#"
                           @click="deleteMessage(message.id)">
                          <span class="me-auto">Unsend</span>
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
              </div>

            </div>

            <div class="message-footer">
              <span class="extra-small text-muted">{{ moment(message.created_at).format('h:mm A') }}</span>
            </div>
          </div>
        </div>


      </div>
    </div>
  </div>
</template>


<style scoped>
.smaller-image {
  max-width: 150px; /* Set the maximum width */
  max-height: 150px; /* Set the maximum height */
}
</style>