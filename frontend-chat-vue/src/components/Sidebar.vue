<script setup>

import Create from "@/components/Sidbar/Create.vue";
import Friends from "@/components/Sidbar/Friends.vue";
import Chats from "@/components/Sidbar/Chats.vue";
import Notifications from "@/components/Sidbar/Notifications.vue";
import Support from "@/components/Sidbar/Support.vue";
import Settings from "@/components/Sidbar/Settings.vue";



</script>

<template>
<aside class="sidebar bg-light">
                <div class="tab-content h-100" role="tablist">
                    <!-- Create -->
                   <Create></Create>

                    <!-- Friends -->
                   <Friends></Friends>

                    <!-- Chats -->
                    <Chats></Chats>

                    <!-- Notifications - Notices -->
                   <Notifications></Notifications>

                    <!-- Support -->
                   <Support></Support>

                    <!-- Settings -->
                    <Settings></Settings>
                </div>
            </aside>
</template>