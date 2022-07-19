import template from './ergonode-synchronization.html.twig';

const { Component } = Shopware;

Component.register('ergonode-synchronization', {
    inject: ['acl'],

    template,

    data () {
        return {
            triggers: [
                {
                    endpoint: 'trigger-sync',
                    label: this.$t('ErgonodeIntegrationShopware.synchronization.synchronize'),
                },
            ],
        };
    },
});