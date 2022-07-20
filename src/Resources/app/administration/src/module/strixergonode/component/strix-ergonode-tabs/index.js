import template from './strix-ergonode-tabs.html.twig';

const { Component } = Shopware;

Component.register('strix-ergonode-tabs', {
    inject: ['acl'],

    template,

    data () {
        return {
            tabs: [
                {
                    routeObject: {
                        path: 'mapping',
                    },
                    label: this.$t('StrixErgonode.tabs.attributeMappings'),
                },
                {
                    routeObject: {
                        path: 'synchronisation',
                    },
                    label: this.$t('StrixErgonode.tabs.synchronisation'),
                },
                {
                    routeObject: {
                        path: 'imports',
                    },
                    label: this.$t('StrixErgonode.tabs.importHistory'),
                },
            ],
        };
    },
});