const { Component, Mixin } = Shopware;

Component.extend('ergonode-timezone-select', 'ergonode-api-select', {
    methods: {
        fetchOptionsRequest() {
            return this.ergonodeMappingService.getTimezones()
        }
    }
});
