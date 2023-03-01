<template>
  <template v-if="users.length < 1">
    Waiting....
  </template>
  <div v-else class="users">
    <table>
      <thead>
        <tr>
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
      <div class="modal-background" @click="closeModal"></div>
      <div class="modal">
        <h2>{{ selectedUser.name }}'s Weather Report</h2>
        <template v-if="isValid">
          <p>Last Updated: {{ selectedUser.updated }}</p>
          <p>Location: {{ selectedUser.location }}</p>
          <p>Temperature: {{ selectedUser.temp }}</p>
          <p>Wind Speed: {{ selectedUser.wind }}</p>
          <p>Current Weather: {{ selectedUser.forecast }}</p>
          <p>Detailed: {{ selectedUser.detailed }}</p>
        </template>
        <template v-else>
          <p>Location is not correct</p>
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