const { Component, Mixin } = Shopware;

Component.extend('ergonode-product-multiselect', 'ergonode-api-entity-multiselect', {
    props: {
        productType: {
            type: String,
        }
    },
    computed: {
        filteredValue() {
            return (Array.isArray(this.value)
                    ?
                    this.value
                    :
                    [this.value]
            ).filter(value => this.ergonodeOptions.some(product => product?.sku === value));
        },

        options() {
            return this.ergonodeOptions.map(product => {
                return {
                    value: product?.sku,
                    label: `${product?.sku}`,
                };
            });
        },
    },

    methods: {
        fetchOptionsRequest() {
            return this.ergonodeMappingService.getErgonodeProducts(this.productType )
        }
    }
});
