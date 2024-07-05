export default {
    namespaced: true,

    state() {
        return {
            lock: {
                templates: false,
            },
            templates: [],
        };
    },

    mutations: {
        setTemplates(state, templates) {
            state.templates = templates;
        },

        setLock(state, key) {
            state.lock[key] = true;
        },
    },
};
