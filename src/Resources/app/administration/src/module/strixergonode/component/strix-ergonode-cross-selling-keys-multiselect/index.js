import template from './strix-ergonode-cross-selling-keys-multiselect.html.twig'

const { Component, Mixin } = Shopware;

Component.register('strix-ergonode-cross-selling-keys-multiselect', {
    inject: ['ergonodeAttributeService'],

    mixins: [
        Mixin.getByName('notification'),
    ],

    template,

    props: {
        value: {
            required: true,
            validator (value) {
                return Array.isArray(value) || value === null || value === undefined;
            },
        },
    },

    data () {
        return {
            isLoading: false,
            ergoAttributes: [],
        };
    },

    computed: {
        filteredValue () {
            return (Array.isArray(this.value) ? this.value : [this.value]).filter(value => this.ergoAttributes.includes(value));
        },

        options () {
            return this.ergoAttributes.map(attribute => {
                return {value: attribute, label: attribute};
            });
        },
    },

    methods: {
        fetchErgoCrossSellingOptions () {
            this.isLoading = true;
            this.ergonodeAttributeService.getErgonodeAttributes(['type_relation'])
                .then(({ data: { data: attributes } }) => {
                    this.ergoAttributes = attributes;
                })
                .catch(() => {
                    this.createNotificationError({
                        message: this.$tc('StrixErgonode.mappings.messages.ergonodeAttributeFetchFailure'),
                    });
                })
                .finally(() => this.isLoading = false);
        },

        onChange (value) {
            this.$emit('change', value);
        },
    },

    created () {
        this.fetchErgoCrossSellingOptions();
    },
});
