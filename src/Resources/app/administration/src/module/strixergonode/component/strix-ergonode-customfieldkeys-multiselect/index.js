import template from './strix-ergonode-customfieldkeys-multiselect.html.twig'

const {Component, Mixin} = Shopware;

Component.register('strix-ergonode-customfieldkeys-multiselect', {
    template,
    inject: ['ergonodeAttributeService'],
    mixins: [
        Mixin.getByName('notification'),
    ],
    props: {
        value: {
            required: true,
            validator(value) {
                return Array.isArray(value) || value === null || value === undefined;
            },
        },
    },
    computed: {
        filteredValue() {
            return this.value.filter(value => this.ergoAttributes.includes(value));
        },
        options(){
            return this.ergoAttributes.map(attribute => {
                return {value: attribute, label: attribute};
            })
        }
    },
    data() {
        return {
            isLoading: false,
            ergoAttributes: [],
        }
    },
    methods: {
        fetchErgoCustomAttributesOptions() {
            this.isLoading = true;
            this.ergonodeAttributeService.getErgonodeAttributes()
                .then(({data: {data: attributes}}) => {
                    this.ergoAttributes = attributes;
                })
                .catch(() => {
                    this.createNotificationError({
                        message: this.$tc('StrixErgonode.mappings.messages.ergonodeAttributeFetchFailure'),
                    });
                })
                .finally(() => this.isLoading = false);
        },
        onChange(value) {
            this.$emit('change', value);
        }
    },
    created() {
        this.fetchErgoCustomAttributesOptions();
    }
});
