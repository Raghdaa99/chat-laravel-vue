<script setup>
import Navigation from '@/components/Navigation.vue';
import Sidebar from '@/components/Sidebar.vue';
import ChatInfo from '@/components/Chat/ChatInfo.vue';
import InviteModal from "@/components/modals/InviteModal.vue";
import ProfileModal from "@/components/modals/ProfileModal.vue";
import UserProfileModal from "@/components/modals/UserProfileModal.vue";
import PreviewModal from "@/components/modals/PreviewModal.vue";
import MainChat from "@/components/MainChat.vue";
import AddMember from "@/components/modals/AddMember.vue";


</script>

<template>
  <!-- Layout -->
  <div class="layout overflow-hidden">
    <!-- Navigation -->
    <Navigation></Navigation>

    <!-- Navigation -->

    <!-- Sidebar -->
    <Sidebar></Sidebar>
    <!-- Sidebar -->

    <!-- Chat -->

    <main class="main">
      <div class="container h-100">
        <!--        {{ userStore().user.data.id }}-->
        <!--        <MainChat :selectedUser="selectedUser"></MainChat>-->
        <MainChat></MainChat>

      </div>
    </main>
    <!-- Chat -->

    <!-- Chat: Info -->
    <ChatInfo></ChatInfo>

    <AddMember></AddMember>
  </div>
  <!-- Layout -->


  <!-- Modal: Invite -->
  <InviteModal></InviteModal>

  <!-- Modal: Profile -->
  <ProfileModal></ProfileModal>

  <!-- Modal: User profile -->
  <UserProfileModal></UserProfileModal>

  <!-- Modal: Media Preview -->
  <PreviewModal></PreviewModal>


</template>

<style>
.cls-2 {
  fill: #fff;
  opacity: 0.1;
}
</style>

