import template from './strix-ergonode-synchronisation.html.twig';

const { Component } = Shopware;

Component.register('strix-ergonode-synchronisation', {
    inject: ['acl'],

    template,

    data () {
        return {
            triggers: [
                {
                    endpoint: 'trigger-sync',
                    label: this.$t('StrixErgonode.synchronisation.synchronise'),
                },
            ],
        };
    },
});
