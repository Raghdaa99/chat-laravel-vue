<script setup>

import {userStore} from "@/stores";
import axiosClient from "@/axios";
import router from "@/router";

const currentUser = userStore().user.data;
let user = {
  current_password: '',
  new_password: '',
  new_password_confirmation: '',
}
const logout = () => {
  axiosClient.post('/logout').then((res) => {
    userStore().logout();
    router.push({
      name: 'Login'
    })
  })
}
function changePassword() {
  axiosClient.post('/change-password', user).then((res) => {
    // console.log(res.data.data)
    toastr.success('Password changed successfully');
    user = null;
  }).catch(function (error) {
    console.log(error.response.data.message);
    toastr.error(error.response.data.message);
  });

}
const handleImageChange = async (event) => {
  const selectedFile = event.target.files[0];
  if (selectedFile) {
    const formData = new FormData();
    formData.append("avatar", selectedFile);
    try {
      const response = await axiosClient.post("/update-avatar", formData);
      currentUser.avatar_url = response.data.avatar_url;
      console.log("Avatar updated successfully:", response.data);
    } catch (error) {
      console.error("Error updating avatar:", error);
    }
  }
};

</script>
<template>
  <div class="tab-pane fade h-100" id="tab-content-settings" role="tabpanel">
    <div class="d-flex flex-column h-100">
      <div class="hide-scrollbar">
        <div class="container py-8">

          <!-- Title -->
          <div class="mb-8">
            <h2 class="fw-bold m-0">Settings</h2>
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
                       aria-label="Search for messages or users...">
              </div>
            </form>
          </div>

          <!-- Profile -->
          <div class="card border-0">
            <div class="card-body">
              <div class="row align-items-center gx-5">
                <div class="col-auto">
                  <div class="avatar">
                    <img :src="currentUser.avatar_url" alt="#" class="avatar-img">
                    <div class="badge badge-circle bg-secondary border-outline position-absolute bottom-0 end-0">
                      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                           stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                           class="feather feather-image">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                        <circle cx="8.5" cy="8.5" r="1.5"></circle>
                        <polyline points="21 15 16 10 5 21"></polyline>
                      </svg>
                    </div>
                    <input id="upload-profile-photo" class="d-none" type="file" @change="handleImageChange">
                    <label class="stretched-label mb-0" for="upload-profile-photo"></label>
                  </div>
                </div>
                <div class="col">
                  <h5>{{ currentUser.name }}</h5>
                  <p>{{ currentUser.email }}</p>
                </div>
                <div class="col-auto">
                  <a @click="logout" class="text-muted">
                    <div class="icon">
                      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                           stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                           class="feather feather-log-out">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                      </svg>
                    </div>
                  </a>
                </div>
              </div>
            </div>
          </div>
          <!-- Profile -->

          <!-- Account -->
          <div class="mt-8">
            <div class="d-flex align-items-center mb-4 px-6">
              <small class="text-muted me-auto">Account</small>
            </div>

            <div class="card border-0">
              <div class="card-body py-2">
                <!-- Accordion -->
                <div class="accordion accordion-flush" id="accordion-profile">
                  <div class="accordion-item">
                    <div class="accordion-header" id="accordion-profile-1">
                      <a href="#" class="accordion-button text-reset collapsed" data-bs-toggle="collapse"
                         data-bs-target="#accordion-profile-body-1" aria-expanded="false"
                         aria-controls="accordion-profile-body-1">
                        <div>
                          <h5>Profile settings</h5>
                          <p>Change your profile settings</p>
                        </div>
                      </a>
                    </div>

                    <div id="accordion-profile-body-1" class="accordion-collapse collapse"
                         aria-labelledby="accordion-profile-1" data-parent="#accordion-profile">
                      <div class="accordion-body">
                        <div class="form-floating mb-6">
                          <input type="text" class="form-control" id="profile-name" placeholder="Name">
                          <label for="profile-name">Name</label>
                        </div>

                        <div class="form-floating mb-6">
                          <input type="email" class="form-control" id="profile-email" placeholder="Email address">
                          <label for="profile-email">Email</label>
                        </div>

                        <div class="form-floating mb-6">
                          <input type="text" class="form-control" id="profile-phone" placeholder="Phone">
                          <label for="profile-phone">Phone</label>
                        </div>

                        <div class="form-floating mb-6">
                          <textarea class="form-control" placeholder="Bio" id="profile-bio" data-autosize="true"
                                    style="min-height: 120px;"></textarea>
                          <label for="profile-bio">Bio</label>
                        </div>

                        <button type="button" class="btn btn-block btn-lg btn-primary w-100">Save</button>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
            </div>
          </div>
          <!-- Account -->

          <!-- Security -->
          <div class="mt-8">
            <div class="d-flex align-items-center my-4 px-6">
              <small class="text-muted me-auto">Security</small>
            </div>

            <div class="card border-0">
              <div class="card-body py-2">
                <!-- Accordion -->
                <div class="accordion accordion-flush" id="accordion-security">
                  <div class="accordion-item">
                    <div class="accordion-header" id="accordion-security-1">
                      <a href="#" class="accordion-button text-reset collapsed" data-bs-toggle="collapse"
                         data-bs-target="#accordion-security-body-1" aria-expanded="false"
                         aria-controls="accordion-security-body-1">
                        <div>
                          <h5>Password</h5>
                          <p>Change your password</p>
                        </div>
                      </a>
                    </div>

                    <div id="accordion-security-body-1" class="accordion-collapse collapse"
                         aria-labelledby="accordion-security-1" data-parent="#accordion-security">
                      <div class="accordion-body">
                        <form @submit.prevent="changePassword" method="post" autocomplete="on">
                          <div class="form-floating mb-6">
                            <input type="password" class="form-control" id="profile-current-password"
                                   placeholder="Current Password" autocomplete="" v-model="user.current_password">
                            <label for="profile-current-password">Current Password</label>
                          </div>

                          <div class="form-floating mb-6">
                            <input type="password" class="form-control" id="profile-new-password"
                                   placeholder="New password" autocomplete="" v-model="user.new_password">
                            <label for="profile-new-password">New password</label>
                          </div>

                          <div class="form-floating mb-6">
                            <input type="password" class="form-control" id="profile-verify-password"
                                   placeholder="Verify Password" autocomplete=""
                                   v-model="user.new_password_confirmation">
                            <label for="profile-verify-password">Verify Password</label>
                          </div>
                          <button type="submit" class="btn btn-block btn-lg btn-primary w-100">Save</button>

                        </form>
                      </div>
                    </div>
                  </div>


                </div>
              </div>
            </div>
          </div>
          <!-- Security -->


          <!-- Devices -->
          <div class="mt-8">
            <div class="d-flex align-items-center my-4 px-6">
              <small class="text-muted me-auto">Devices</small>

              <div class="flex align-items-center text-muted">
                <a href="#" class="text-muted small">End all sessions</a>

                <div class="icon icon-xs">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                       class="feather feather-log-out">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16 17 21 12 16 7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                  </svg>
                </div>
              </div>
            </div>

            <div class="card border-0">
              <div class="card-body py-3">

                <ul class="list-group list-group-flush">
                  <li class="list-group-item">
                    <div class="row align-items-center">
                      <div class="col">
                        <h5>Windows ⋅ USA, Houston</h5>
                        <p>Today at 2:48 pm ⋅ Browser: Chrome</p>
                      </div>
                      <div class="col-auto">
                        <span class="text-primary extra-small">active</span>
                      </div>
                    </div>
                  </li>

                  <li class="list-group-item">
                    <div class="row align-items-center">
                      <div class="col">
                        <h5>iPhone ⋅ USA, Houston</h5>
                        <p>Yesterday at 2:48 pm ⋅ Browser: Chrome</p>
                      </div>
                    </div>
                  </li>
                </ul>

              </div>
            </div>

          </div>
          <!-- Devices -->

        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "Settings"
}
</script>

<style scoped>

</style>