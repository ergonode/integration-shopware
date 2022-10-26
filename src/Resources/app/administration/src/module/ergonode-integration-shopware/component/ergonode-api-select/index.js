import template from './ergonode-api-select.html.twig'

const { Component, Mixin } = Shopware;

Component.register('ergonode-api-select', {
    inject: ['ergonodeMappingService'],
    
    mixins: [
        Mixin.getByName('notification'),
    ],
    
    template,
    
    props: {
        value: {
            required: true
        },
    },
    
    data() {
        return {
            isLoading: false,
            ergonodeOptions: [],
        };
    },
    
    computed: {
        filteredValue() {
            return this.value;
        },
        
        options() {
            return this.ergonodeOptions.map(value => {
                return {
                    value,
                    label: value,
                };
            });
        },
    },
    
    methods: {
        fetchOptionsRequest() {
            throw new Error(`Method fetchOptionsRequest not implemented in Component: ${this.$options._componentTag}`);
        },
        
        fetchOptions() {
            this.isLoading = true;
            this.fetchOptionsRequest()
            .then(({ data: { data: values } }) => {
                this.ergonodeOptions = values;
            })
            .catch(() => {
                this.createNotificationError({
                    message: this.$t('ErgonodeIntegrationShopware.mappings.messages.ergonodeOptionsFetchFailure', {
                        label: this.label
                    }),
                });
            })
            .finally(() => this.isLoading = false);
        },
        
        onChange(value) {
            this.$emit('change', value);
        },
    },
    
    created() {
        this.fetchOptions();
    },
});
