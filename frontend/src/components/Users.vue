<template>
  <template v-if="users.length < 1">
    <div class="w-full text-center font-bold text-2xl">
      Waiting....
    </div>
  </template>
  <div v-else class="w-[80%] flex justify-around mx-auto">
    <table class="w-full border border-2 border-border-gray-700">
      <thead>
        <tr class="border border-2 font-bold text-1xl text-left">
          <th>Name</th>
          <th>Email</th>
          <th>Location</th>
          <th>Updated</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(user) in users" :key="user.id" @click="showModal(user)">
          <td>{{ user.name }}</td>
          <td>{{ user.email }}</td>
          <td>Lati: {{ user.latitude }}, Logi: {{ user.longitude}}</td>
          <td>{{ changeFormat(user.updated) }}</td>
        </tr>
      </tbody>
    </table>

    <div v-if="showUserModal">
      <div class="modal-background fixed top-0 left-0 w-screen h-screen z-10 bg-[#000000aa] backdrop-sepia-[.2]" @click="closeModal"></div>
      <div class="modal fixed w-[400px] h-[300px] rounded-lg p-3 z-20 bg-white shadow-[3px_2px_9px_2px_rgba(111, 111, 11, 0.8)]">
        <h2 class="font-bold text-lg pb-2 border-b-2">{{ selectedUser.name }}'s Weather Report</h2>
        <template v-if="isValid">
          <div class="w-full h-full flex flex-col justify-around">
            <p><span class="font-bold">Last Updated:</span> {{ selectedUser.updated }}</p>
            <p><span class="font-bold">Location:</span> {{ selectedUser.location }}</p>
            <p><span class="font-bold">Temperature:</span> {{ selectedUser.temp }}</p>
            <p><span class="font-bold">Wind Speed:</span> {{ selectedUser.wind }}</p>
            <p><span class="font-bold">Current Weather:</span> {{ selectedUser.forecast }}</p>
            <p><span class="font-bold">Detailed:</span> {{ selectedUser.detailed }}</p>
          </div>
        </template>
        <template v-else>
          <p class="text-red-500">Location is not correct</p>
        </template>
      </div>
    </div>
  </div>
</template>

<script>
import moment from 'moment';
export default {
  data() {
    return {
      users: [],
      isValid: true,
      showUserModal: false,
      weatherDetails: null,
      selectedUser: null
    }
  },
  mounted() {
    this.getUsers()
  },
  methods: {
    changeFormat(val) {
      return !val ? '' : moment(val).format('D/M/YYYY H:m:s');
    },
    async getUsers() {
      const response = await (await fetch('http://localhost')).json();
      if (!response?.users?.length) return;

      this.users = response.users;
      // this.users = 
    },
    async showModal(user) {
      let isUpdatable = true;
      if (user.updated) {
        isUpdatable = (new Date() - user.updated) / (1000 * 60 * 60) > 1;
      }
      
      this.selectedUser = user;

      if (!isUpdatable) {
        this.showUserModal = true;
        return;
      }

      let {status, data} = await (await fetch(`http://localhost/${user.latitude}/${user.longitude}`)).json();
      if (status === 'failed') {
        this.isValid = false;
        this.showUserModal = true;
        return;
      }

      user.temp = data.main.temp;
      user.humidity = data.main.humidity;
      user.wind = data.wind.speed;
      user.forecast = data.weather[0].main;
      user.detailed = data.weather[0].description;
      user.updated = new Date(+data.dt * 1000);
      user.location = !data.name ? 'unknown' : `${data.name}${data.name} ${data.sys.country}`;
      this.showUserModal = true;
    },
    closeModal() {
      this.isValid = true;
      this.showUserModal = false;
      this.selectedUser = null;
    }
  }
};
</script>