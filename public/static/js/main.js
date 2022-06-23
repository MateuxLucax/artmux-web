const store = {
  user: {
    token: null
  }
}

const storage = {
  setToken: (token, persist) => {
    store.user.token = token;
    if (persist) {
      localStorage.setItem('token', token);
    } else {
      sessionStorage.setItem('token', token);
    }
  },

  removeToken: () => {
    store.user.token = null;
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

  /**
   * @param {RequestInfo} url 
   * @param {RequestInit} init 
   * @returns Response
   */
  fetch: (url, init = {}) => {
    return fetch(request.baseUrl + url, init);
  },

  /**
   * @param {RequestInfo} url 
   * @param {RequestInit} init 
   * @returns Response
   */
  authFetch: (url, init = {}) => {
    init.headers = init.headers ?? {};
    Object.assign(init.headers, {
      'Authorization': `Bearer ${storage.getToken()}` 
    });
    /**
     * TODO: fix this, response is a Promise, so we can't use status.
     * Maybe migrate this to the auth methods below?
     * And make a custom Promise, with resolve/reject adding status code validation, 
     * and resolve/reject response.
     */ 
    return request.fetch(url, init);
  },

  treatResponse: async (response) => {
    if (response.status === 401) {
      logout();
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
    get: async (url, headers = {}) => {
      return request.treatResponse(await request.get(url, {
        'Authorization': `Bearer ${storage.getToken()}`,
        ...headers
      }));
    },

    post: async (url, data, headers = {}) => {
      return request.treatResponse(await request.post(url, data, {
        'Authorization': `Bearer ${storage.getToken()}`,
        ...headers
      }));
    }
  }
}

const logout = () => {
  storage.removeToken();
  window.location.replace('/entrar');
}

window.onload = async () => {
  const safePages = ['/', '/entrar/', '/cadastrar/'];
  if (!safePages.includes(window.location.pathname)) await request.auth.get('users/me');
};