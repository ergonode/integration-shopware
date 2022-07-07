import template from './strix-ergonode-trigger-button.html.twig';

const { Component, Mixin } = Shopware;

Component.register('strix-ergonode-trigger-button', {
    inject: ['ergonodeSynchronisationService'],

    mixins: [
        Mixin.getByName('notification'),
    ],

    inheritAttrs: false,

    template,

    props: {
        isLoading: {
            type: Boolean,
            required: false,
            default: false,
        },
        endpoint: {
            type: String,
            required: true,
        },
    },

    data: () => ({
        isLoadingInternal: false,
    }),

    computed: {
        isLoadingMerged () {
            return this.isLoading || this.isLoadingInternal;
        },
    },

    methods: {
        async trigger () {
            this.isLoadingInternal = true;
            try {
                let result = await this.ergonodeSynchronisationService.triggerSynchronisation(this.endpoint);
                if (!result?.status === 200) {
                    throw new Error(result.statusText);
                }
                if (!result?.data?.success) {
                    throw new Error(result?.data?.errors[0]?.title);
                }
                this.createNotificationSuccess({
                    message: this.$t('StrixErgonode.synchronisation.messages.executed'),
                });
            } catch ({ message }) {
                console.error(message);
                this.createNotificationError({
                    message,
                });
            } finally {
                this.isLoadingInternal = false;
            }
        }
    },
})
