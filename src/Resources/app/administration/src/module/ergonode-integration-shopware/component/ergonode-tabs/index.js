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
                        path: 'attribute-mapping',
                    },
                    label: this.$t('ErgonodeIntegrationShopware.tabs.attributeMappings'),
                },
                {
                    routeObject: {
                        path: 'custom-field-mapping',
                    },
                    label: this.$t('ErgonodeIntegrationShopware.tabs.customFieldMappings'),
                },
                {
                    routeObject: {
                        path: 'category-mapping',
                    },
                    label: this.$t('ErgonodeIntegrationShopware.tabs.categoryMappings'),
                },
                {
                    routeObject: {
                        path: 'category-attribute-mapping',
                    },
                    label: this.$t('ErgonodeIntegrationShopware.tabs.categoryAttributeMappings'),
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
