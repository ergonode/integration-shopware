import template from './ergonode-api-select.html.twig'

const { Component, Mixin, State, Utils } = Shopware;
const { capitalizeString } = Utils.string;

Component.register('ergonode-api-select', {
    inject: ['ergonodeMappingService'],

    mixins: [
        Mixin.getByName('notification'),
    ],

    template,

    props: {
        value: {
            required: true,
        },
        disabled: {
            required: false,
            default: false,
        },
    },

    data() {
        return {
            isLoading: false,
            responseValues: [],
        };
    },

    computed: {
        filteredValue() {
            return this.value;
        },

        ergonodeOptions() {
            const stateKey = this.getStateKey();
            if (stateKey) {
                return State.get(`ergonodeApiSelect`)[stateKey] || [];
            }

            return this.responseValues;
        },

        options() {
            return this.ergonodeOptions.map(value => {
                return {
                    value: typeof value === 'object' ? value.code : value,
                    label: typeof value === 'object' ? value.code : value,
                };
            });
        },
    },

    methods: {
        /**
         * set key in parent component to use state
         * allows to cache values for multiple components
         * prevents multiple requests for the same stateKey when using multiple components on the same view
         *
         * @returns {string|null}
         */
        getStateKey() {
            return null;
        },

        fetchOptionsRequest() {
            throw new Error(`Method fetchOptionsRequest not implemented in Component: ${this.$options._componentTag}`);
        },

        fetchOptions() {
            const stateKey = this.getStateKey();
            if (
                this.ergonodeOptions.length > 0
                || (stateKey && State.get(`ergonodeApiSelect`).lock[stateKey])
            ) {
                return;
            }

            this.isLoading = true;
            if (stateKey) {
                State.commit(`ergonodeApiSelect/setLock`, stateKey);
            }

            this.fetchOptionsRequest()
                .then(({ data: { data: values } }) => {
                    if (stateKey) {
                        State.commit(`ergonodeApiSelect/set${capitalizeString(stateKey)}`, values);
                    } else {
                        this.responseValues = values;
                    }
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
            this.$emit('update:value', value);
        },
    },

    created() {
        this.fetchOptions();
    },
});
