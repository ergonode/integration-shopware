import template from './strix-ergonode-trigger-button.html.twig';

const { Component, Mixin } = Shopware;

Component.register('strix-ergonode-trigger-button', {
    inject: ['ergonodeAttributeService'],

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
                let result = await this.ergonodeAttributeService.triggerSynchronisation(this.endpoint);
                if (!result.ok) {
                    throw new Error(result);
                }
                result = await result.json();
                if (!result?.success) {
                    throw new Error(result);
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
