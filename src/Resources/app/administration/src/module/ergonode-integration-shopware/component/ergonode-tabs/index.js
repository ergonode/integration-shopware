import template from './ergonode-tabs.html.twig';

const { Component } = Shopware;

Component.register('ergonode-tabs', {
    inject: ['acl'],

    template,

    data () {
        return {
            tabs: [
                {
                    routeObject: {
                        path: 'mapping',
                    },
                    label: this.$t('ErgonodeIntegrationShopware.tabs.attributeMappings'),
                },
                {
                    routeObject: {
                        path: 'synchronization',
                    },
                    label: this.$t('ErgonodeIntegrationShopware.tabs.synchronization'),
                },
                {
                    routeObject: {
                        path: 'imports',
                    },
                    label: this.$t('ErgonodeIntegrationShopware.tabs.importHistory'),
                },
            ],
        };
    },
});
