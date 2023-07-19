const { Component, Mixin } = Shopware;

Component.extend('ergonode-attribute-select', 'ergonode-api-entity-select', {
    props: {
        attributesType: {
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
            ).filter(value => this.ergonodeOptions.some(option => option?.code === value));
        },
        
        options() {
            return this.ergonodeOptions.map(option => {
                return {
                    value: option?.code,
                    label: `${option?.code}${option?.type ? ` (${option.type})`:''}`,
                };
            });
        },
    },
    
    methods: {
        fetchOptionsRequest() {
            let types = []
            if (this.attributesType) {
                types = this.attributesType.split(',');
            }
            
            return this.ergonodeMappingService.getErgonodeAttributes(types)
        }
    }
});
