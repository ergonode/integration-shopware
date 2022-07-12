import template from './strix-ergonode-api-key-validator.html.twig';

const { Component, Mixin } = Shopware;

Component.register('strix-ergonode-api-key-validator', {
    inject: ['ergonodeConfigurationService'],

    mixins: [
        Mixin.getByName('notification'),
    ],

    template,

    data: () => ({
        success: null,
        isLoading: false,
    }),

    computed: {
        pluginConfig () {
            let parent = this.$parent;

            while (!parent.actualConfigData) {
                parent = parent.$parent;
            }

            return parent?.actualConfigData?.null || {};
        },

        variant () {
            return (typeof this.success === 'boolean') ? (this.success ? 'success' : 'danger') : 'neutral';
        },

        color () {
            return (typeof this.success === 'boolean') ? (this.success ? 'lightgreen' : 'red') : 'gray';
        },

        buttonLabel () {
            return this.$t('StrixErgonode.configuration.verifyCredentials.verifyCredentials');
        },

        message () {
            return (typeof this.success === 'boolean')
                ?
                (
                    this.success
                    ?
                    this.$t('StrixErgonode.configuration.verifyCredentials.credentialsCorrect')
                    :
                        this.$t('StrixErgonode.configuration.verifyCredentials.credentialsIncorrect')
                )
                :
                this.$t('StrixErgonode.configuration.verifyCredentials.verifyCredentials');
        },
    },

    methods: {
        async verify() {
            this.isLoading = true;
            try {
                const config = {
                    baseUrl: this.pluginConfig['StrixErgonode.config.ergonodeBaseUrl'],
                    apiKey: this.pluginConfig['StrixErgonode.config.ergonodeApiKey'],
                };

                let result = await this.ergonodeConfigurationService.verifyCredentials(config);

                if (!result?.data || typeof result.data.success !== 'boolean') {
                    throw new Error(this.$t('StrixErgonode.configuration.verifyCredentials.couldNotVerify'));
                }
                this.success = result.data.success;
                this.notify();
            } catch ({ message }) {
                console.error(message);
                this.notify(this.$t('StrixErgonode.configuration.verifyCredentials.couldNotVerify'));
                this.success = undefined;
            } finally {
                this.isLoading = false;
            }
        },

        notify(message) {
            this[`createNotification${this.success ? 'Success' : 'Error'}`].call(this, {
                message: message || this.message,
            });
        },
    },
});