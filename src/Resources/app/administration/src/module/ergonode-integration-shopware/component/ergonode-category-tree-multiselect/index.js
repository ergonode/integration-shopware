const { Component, Mixin } = Shopware;

Component.extend('ergonode-category-tree-multiselect', 'ergonode-api-entity-multiselect', {
    methods: {
        fetchOptionsRequest() {
            return this.ergonodeMappingService.getErgonodeCategoryTrees()
        }
    }
});
