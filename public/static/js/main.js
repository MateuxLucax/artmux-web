const store = {
  user: {
    name: null,
    token: null
  },

  setUser: (name, token) => {
    console.log([name, token]);
    store.user = { name, token };
    console.log(store.user);
  },

  clearUser: () => {
    store.user = {
      name: null,
      token: null
    };
  }
}

const storage = {
  setToken: (token, persist) => {
    if (persist) {
      localStorage.setItem('token', token);
    } else {
      sessionStorage.setItem('token', token);
    }
  },

  removeToken: () => {
    localStorage.removeItem('token');
    sessionStorage.removeItem('token');
  },

  getToken: () => {
    if (store.user.token) {
      return store.user.token;
    }

    const token = localStorage.getItem('token') || sessionStorage.getItem('token');
    if (token) {
      store.user.token = token;
      return token;
    }

    return null;
  }
}

const request = {

  baseUrl: 'https://api.artmux.gargantua.one/',

  treatResponse: async (response) => {
    if (response.status === 401) {
      store.clearUser();
      storage.removeToken();
      window.location.replace('/entrar');
      return null;
    }
    return await response.json();
  },

  get: (url, headers = {}) => {
    return fetch(request.baseUrl + url, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        ...headers
      }
    });
  },

  post: (url, data, headers = {}) => {
    return fetch(request.baseUrl + url, {
      method: 'POST',
      body: JSON.stringify(data),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        ...headers
      }
    });
  },

  auth: {
    get: async (url) => {
      return request.treatResponse(await request.get(url, {
        'Authorization': `Bearer ${storage.getToken()}`
      }));
    },

    post: async (url, data) => {
      return request.treatResponse(await request.post(url, data, {
        'Authorization': `Bearer ${storage.getToken()}`
      }));
    }
  }
}