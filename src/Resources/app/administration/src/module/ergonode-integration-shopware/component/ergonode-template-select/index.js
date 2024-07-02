const { Component, Mixin } = Shopware;

Component.extend('ergonode-template-select', 'ergonode-api-select', {
    methods: {
        getStateKey() {
            return 'templates';
        },

        fetchOptionsRequest() {
            return this.ergonodeMappingService.getErgonodeTemplates();
        }
    }
});
