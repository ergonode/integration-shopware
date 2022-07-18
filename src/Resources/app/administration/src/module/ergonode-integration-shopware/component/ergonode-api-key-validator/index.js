import template from './ergonode-api-key-validator.html.twig';

const { Component, Mixin } = Shopware;

Component.register('ergonode-api-key-validator', {
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
            return this.$t('ErgonodeIntegrationShopware.configuration.verifyCredentials.verifyCredentials');
        },

        message () {
            return (typeof this.success === 'boolean')
                ?
                (
                    this.success
                    ?
                    this.$t('ErgonodeIntegrationShopware.configuration.verifyCredentials.credentialsCorrect')
                    :
                        this.$t('ErgonodeIntegrationShopware.configuration.verifyCredentials.credentialsIncorrect')
                )
                :
                this.$t('ErgonodeIntegrationShopware.configuration.verifyCredentials.verifyCredentials');
        },
    },

    methods: {
        async verify() {
            this.isLoading = true;
            try {
                const config = {
                    baseUrl: this.pluginConfig['ErgonodeIntegrationShopware.config.ergonodeBaseUrl'],
                    apiKey: this.pluginConfig['ErgonodeIntegrationShopware.config.ergonodeApiKey'],
                };

                let result = await this.ergonodeConfigurationService.verifyCredentials(config);

                if (!result?.data || typeof result.data.success !== 'boolean') {
                    throw new Error(this.$t('ErgonodeIntegrationShopware.configuration.verifyCredentials.couldNotVerify'));
                }
                this.success = result.data.success;
                this.notify();
            } catch ({ message }) {
                console.error(message);
                this.notify(this.$t('ErgonodeIntegrationShopware.configuration.verifyCredentials.couldNotVerify'));
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