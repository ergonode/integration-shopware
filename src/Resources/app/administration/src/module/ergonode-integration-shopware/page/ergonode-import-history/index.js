import template from './ergonode-import-history.html.twig';
import './ergonode-import-history.scss';

const { Component } = Shopware;

Component.register('ergonode-import-history', {
    template,

    data () {
        return {
            repository: {},
            isListingLoading: false,
            isDetailsLoading: false,
            // mock data
            data: (function () {
                let statuses = ['finished', 'processing'];
                let i = 0, len = 5, arr = [], date = new Date().toISOString();
                for (; i < len; i++) {
                    arr.push({
                        id: i + 1,
                        startDate: date,
                        endDate: date,
                        status: statuses[Math.round(Math.random())],
                    })
                }
                return arr;
            })(),
            ListingColumns: [
                {
                    property: 'status',
                    label: this.$t('ErgonodeIntegrationShopware.importHistory.grid.status'),
                    width: 'auto',
                },
                {
                    property: 'startDate',
                    label: this.$t('ErgonodeIntegrationShopware.importHistory.grid.startedOn'),
                    width: 'auto',
                },
                {
                    property: 'endDate',
                    label: this.$t('ErgonodeIntegrationShopware.importHistory.grid.endedOn'),
                    width: 'auto',
                },
            ],
            detailsColumns: [
                {
                    property: 'datetime',
                    label: this.$t('ErgonodeIntegrationShopware.importHistory.details.createdAt'),
                },
                {
                    property: 'message',
                    label: this.$t('ErgonodeIntegrationShopware.importHistory.details.message'),
                },
            ],
            detailsCache: {},
            detailsId: null,
        };
    },

    computed: {
        // mock data
        detailsEntity () {
            return {
                ...this.data[0],
                log: (function () {
                    let arr = [], i = 0, len = 10, messages = ['Lorem ipsum', 'dolor sit amet'];
                    for (; i < len; i++) {
                        arr.push({
                            message: messages[Math.round(Math.random())],
                            context: {
                                syncHistoryId: Math.random() * 10000,
                            },
                            level: Math.random() * 10000 % 1000,
                            level_name: ['ERROR', 'INFO'][Math.round(Math.random())],
                            channel: 'sync',
                            datetime: new Date().toISOString(),
                        })
                    }
                    return arr;
                })(),
            };
        },
    },

    methods: {
        formatDate (date) {
            return (new Date(date))?.toISOString().substr(0, 19).replace('T', ' ');
        },

        // TODO detail fetching method
    },

    // TODO add watcher on detailsId property that fetches details with memoization in the 'detailsCache'

    created () {
        // TODO prepare repository
    },
});
