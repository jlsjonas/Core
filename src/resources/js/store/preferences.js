export const state = {
    global: {},
    local: {},
};

const setPreferences = (payload) => {
    axios.patch(route('core.preferences.setPreferences'), payload)
        .catch(error => this.handleError(error));
};

const updateGlobal = () => {
    setPreferences({ global: state.global });
};

const updateLocal = (payload) => {
    setPreferences({ route: payload.route, value: payload.value });
};

export const getters = {
    global: state => state.global,
    local: state => route => state.local && state.local[route],
    lang: state => state.global && state.global.lang,
    theme: state => state.global && state.global.theme,
    layout: state => state.global && state.global.layout,
    expandedMenu: state => state.global && state.global.expandedMenu,
    toastrPosition: state => state.global && state.global.toastrPosition,
    history: state => state.global && state.global.history,
};

export const mutations = {
    set: (state, preferences) => {
        state.global = preferences.global;
        state.local = preferences.local;
    },
    global: (state, payload) => (state.global = payload),
    lang: (state, lang) => (state.global.lang = lang),
    theme: (state, theme) => (state.global.theme = theme),
    layout: (state, mode) => (state.global.layout = mode),
    toastrPosition: (state, position) => (state.global.toastrPosition = position),
    expandedMenu: (state, expandedMenu) => (state.global.expandedMenu = expandedMenu),
    history: (state, history) => (state.global.history = history),
    local: (state, payload) => (state.local[payload.route] = payload.value),
};

export const actions = {
    setGlobal: ({ commit }, payload) => {
        commit('global', payload);
        updateGlobal();
    },
    setLocal: ({ commit }, payload) => {
        commit('local', payload);
        updateLocal(payload);
    },
    setLang: ({ commit }, lang) => {
        commit('lang', lang);
        updateGlobal();
    },
    setTheme: ({ commit, dispatch, getters }, theme) => {
        if (theme === getters.theme) {
            return;
        }

        commit('theme', theme);

        dispatch('layout/switchTheme', null, { root: true })
            .then(() => updateGlobal());
    },
    setLayout: ({ commit }, mode) => {
        commit('layout', mode);
        updateGlobal();
    },
    setToastrPosition: ({ commit }, position) => {
        commit('toastrPosition', position);
        updateGlobal();
    },
    setHistoryState: ({ commit }, history) => {
        commit('history', history);
        updateGlobal();
    },
    setMenuState: ({ commit }, state) => {
        commit('expandedMenu', state);
        commit('layout/menu/update', state, { root: true });
        updateGlobal();
    },
};
