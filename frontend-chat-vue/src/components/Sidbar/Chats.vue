<script setup>
import {computed, defineEmits, defineProps, inject, onMounted, onUpdated, ref, watchEffect} from "vue";
import axiosClient from "@/axios";
import moment from "moment";
// import { echo } from "@/echo";

import {userStore} from "@/stores";
import {useChatStore} from "@/stores/chat";

const emit = defineEmits();
const conversations = ref([]);
const searchQuery = ref("");
import {initializeEcho} from '@/services/echo';

const echo = initializeEcho(userStore().user.token);
let currentConversaton = ref({});
const presenceChannels = ref({});
const emitter = inject('emitter');
const chatStore = useChatStore();


onMounted(() => {
  getConversations();
  initializeUnreadMessageEcho();
  initializeUserOnlineStatusEcho();
  refreshConversation();
});
const getConversations = () => {
  axiosClient.get("conversations")
      .then((res) => {
        conversations.value = res.data.data;
        console.log(res.data.data);
      });
};
const openConversation = (user, conversation) => {
  conversation.new_messages = null;
  currentConversaton.value = conversation
  fireEmitOpenConversation(conversation)

};
const filteredUsersOrMessages = () => {
  if (!searchQuery.value) {
    return conversations.value;
  }
  const lowerCaseQuery = searchQuery.value.toLowerCase();
  return conversations.value.filter(conversation =>
      conversation.participants[0].name.toLowerCase().includes(lowerCaseQuery)
  );
};
const fireEmitOpenConversation = (conversation) => {

  emitter.emit("open-conversation", [conversation, conversation.participants[0]]);
}

function initializeUnreadMessageEcho() {
  echo.join(`unread-message.${userStore().user.data.id}`)
      .here(users => {
        console.log('users unread-message', users);
      })
      .joining(user => {
        console.log('User joining:', user);
      })
      .leaving(user => {
        console.log('User leaving:', user);
      })
      .listen('.UnreadMessage', (data) => {
        console.log('Received event:', data);
        const conversationToUpdate = conversations.value.find(conversation => conversation.id === data.message.conversation_id);
        if (conversationToUpdate) {
          // Update the last_message property with the new message
          conversationToUpdate.last_message = data.message;

          // Reorder conversations based on last message timestamp
          conversations.value.sort((a, b) => {
            const aTimestamp = new Date(a.last_message.created_at).getTime();
            const bTimestamp = new Date(b.last_message.created_at).getTime();
            return bTimestamp - aTimestamp;
          });

          if (currentConversaton.value && conversationToUpdate.id !== currentConversaton.value.id) {
            conversationToUpdate.new_messages++;
          }
        }
      });
}

function initializeUserOnlineStatusEcho() {
  echo.join(`user-online-status`)
      .here(users => {
        // Handle initial online users
        users.forEach(user => {
          const conversationToUpdate = filteredUsersOrMessages().find(conversation =>
              conversation.type === 'peer' && conversation.participants[0].id === user.id
          );
          if (conversationToUpdate) {
            conversationToUpdate.participants[0].is_online = true;
          }
        });
      })
      .joining(user => {
        console.log({user}, 'joined')
        const conversationToUpdate = filteredUsersOrMessages().find(conversation =>
            conversation.type === 'peer' && conversation.participants[0].id === user.id
        );
        if (conversationToUpdate) {
          conversationToUpdate.participants[0].is_online = true;
        }
      })
      .leaving(user => {
        console.log({user}, 'leaving')
        const conversationToUpdate = filteredUsersOrMessages().find(conversation =>
            conversation.type === 'peer' && conversation.participants[0].id === user.id
        );
        if (conversationToUpdate) {
          conversationToUpdate.participants[0].is_online = false;
        }
      })

}

function refreshConversation() {
  emitter.on("refresh-conversation", async (conversation) => {
    conversations.value.push(conversation);
  });
}

</script>

<template>
  <div class="tab-pane fade h-100 show active" id="tab-content-chats" role="tabpanel">
    <div class="d-flex flex-column h-100 position-relative">
      <div class="hide-scrollbar">

        <div class="container py-8">
          <!-- Title -->
          <div class="mb-8">
            <h2 class="fw-bold m-0">Chats</h2>
          </div>

          <!-- Search -->
          <div class="mb-6">
            <form action="#">
              <div class="input-group">
                <div class="input-group-text">
                  <div class="icon icon-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                         class="feather feather-search">
                      <circle cx="11" cy="11" r="8"></circle>
                      <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                  </div>
                </div>

                <input type="text" class="form-control form-control-lg ps-0" placeholder="Search messages or users"
                       aria-label="Search for messages or users..." v-model="searchQuery">
              </div>
            </form>
          </div>

          <!-- Chats -->
          <div class="card-list">

            <!-- Card -->

            <a v-for="conversation in filteredUsersOrMessages()" href="#"
               @click="openConversation(conversation.participants[0], conversation)" class="card border-0 text-reset">

              <div class="card-body">
                <div class="row gx-5">
                  <div class="col-auto">
                    <div :class="{'avatar': true, 'avatar-online': conversation.participants[0].is_online}">

                      <img :src="conversation.image_url" alt="#" class="avatar-img">
                    </div>
                  </div>

                  <div class="col">
                    <div class="d-flex align-items-center mb-3">
                      <h5 class="me-auto mb-0">{{
                          conversation.type === 'peer' ? conversation.participants[0].name : conversation.label.name
                        }}</h5>
                      <span class="text-muted extra-small ms-2">{{
                          moment(conversation.last_message.created_at).format('h:mm A')
                        }}</span>
                    </div>

                    <div class="d-flex align-items-center">
                      <div class="line-clamp me-auto" v-if="conversation.last_message.type === 'text'">
                        {{ conversation.last_message.body }}
                      </div>
                      <div class="line-clamp me-auto" v-else>
                        {{ conversation.last_message.body.file_name }}
                      </div>
                      <div v-if="conversation.new_messages" class="badge badge-circle bg-primary ms-5">
                        <span>{{ conversation.new_messages }}</span>
                      </div>
                    </div>

                  </div>
                </div>
              </div>
              <div v-if="conversation.type==='group'" class="card-footer">
                <div class="row align-items-center gx-4">
                  <div class="col-auto">
                    <!--                    <div class="avatar avatar-xs">-->
                    <!--                      <img class="avatar-img" src="@/assets/img/group/teamwork.png" alt="Community">-->
                    <!--                    </div>-->
                  </div>

                  <div class="col">
                    <h6 class="mb-0">Group Chat</h6>
                  </div>

                  <div class="col-auto">
                    <div class="avatar-group">

                      <div v-for="(participant, index) in conversation.participants.slice(0, 3)" :key="index"
                           class="avatar avatar-xs">
                        <img :src="participant.avatar_url" alt="#" class="avatar-img">
                      </div>

                      <!-- Check if there are more avatars to display -->
                      <div v-if="conversation.participants.length > 3" class="avatar avatar-xs">
                        <span class="avatar-text">+{{ conversation.participants.length - 3 }}</span>
                      </div>
                    </div>
                  </div>
                </div><!-- .row -->
              </div><!-- .card-footer -->
            </a>


          </div>
          <!-- Chats -->
        </div>

      </div>
    </div>
  </div>
</template>

<style scoped>

</style>