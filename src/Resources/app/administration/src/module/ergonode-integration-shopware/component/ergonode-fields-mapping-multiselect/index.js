import template from './ergonode-fields-mapping-multiselect.html.twig'

const { Component, Mixin } = Shopware;

Component.register('ergonode-fields-mapping-multiselect', {
    inject: ['ergonodeAttributeService'],

    mixins: [
        Mixin.getByName('notification'),
    ],

    template,

    props: {
        attributesType: {
            type: String,
        },
        value: {
            required: true,
            validator (value) {
                return Array.isArray(value) || value === null || value === undefined;
            },
            default: () => [],
        },
    },

    data () {
        return {
            isLoading: false,
            ergoAttributes: [],
        };
    },

    computed: {
        filteredValue() {
            return (Array.isArray(this.value)
                    ?
                    this.value
                    :
                    [this.value]
                ).filter(value => this.ergoAttributes.some(attribute => attribute?.code === value));
        },

        options () {
            return this.ergoAttributes.map(attribute => {
                return {
                    value: attribute?.code,
                    label: `${attribute?.code}${attribute?.type ? ` (${attribute.type})` : ''}`,
                };
            });
        },
    },

    methods: {
        fetchErgoAttributesOptions () {
            this.isLoading = true;
            this.ergonodeAttributeService.getErgonodeAttributes(this.attributesType ? [this.attributesType] : [])
                .then(({ data: { data: attributes } }) => {
                    this.ergoAttributes = attributes;
                })
                .catch(() => {
                    this.createNotificationError({
                        message: this.$tc('ErgonodeIntegrationShopware.mappings.messages.ergonodeAttributeFetchFailure'),
                    });
                })
                .finally(() => this.isLoading = false);
        },

        onChange (value) {
            this.$emit('change', value);
        },
    },

    created () {
        this.fetchErgoAttributesOptions();
    },
});
