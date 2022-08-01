import template from './ergonode-import-history.html.twig';
import './ergonode-import-history.scss';

const { Component, Context, Data: { Criteria }, Mixin } = Shopware;

Component.register('ergonode-import-history', {
    inject: ['acl', 'repositoryFactory', 'ergonodeImportHistoryService'],

    mixins: [
        Mixin.getByName('notification'),
    ],

    template,

    data () {
        return {
            isListingLoading: false,
            isDetailsLoading: false,
            imports: [],
            detailsId: null,
        };
    },

    computed: {
        repository () {
            return this.repositoryFactory.create('ergonode_sync_history');
        },

        detailsIndex () {
            return this.detailsId ? this.imports.findIndex(entry => entry.id === this.detailsId) : null;
        },

        detailsEntity () {
            return this.imports[this.detailsIndex];
        },

        listingColumns () {
            return [
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
            ];
        },

        detailsColumns () {
            return [
                {
                    property: 'datetime',
                    label: this.$t('ErgonodeIntegrationShopware.importHistory.details.createdAt'),
                },
                {
                    property: 'level_name',
                    label: this.$t('ErgonodeIntegrationShopware.importHistory.details.level'),
                },
                {
                    property: 'message',
                    label: this.$t('ErgonodeIntegrationShopware.importHistory.details.message'),
                },
            ];
        },
    },

    methods: {
        formatDate (date) {
            if (!date) {
                return '—';
            }
            try {
                const dateObject = new Date(date);
                return dateObject?.toISOString()?.substring(0, 19)?.replace('T', ' ');
            } catch (e) {
                console.error(`Error occurred while parsing date string '${date}'`);
                return this.$t('global.default.error');
            }
        },

        async fetchImports () {
            this.isListingLoading = true;
            const criteria = new Criteria()
                .setLimit(null);
            try {
                this.imports = await this.repository.search(criteria, Context.Api);
                // initialize logs arrays
                this.imports.forEach((_, index) => this.imports[index].logs = []);
            } catch ({ message }) {
                console.error(message);
                this.createNotificationError({
                    message,
                });
            } finally {

                this.isListingLoading = false;
            }
        },

        async fetchLogs () {
            this.isDetailsLoading = true;
            try {
                let result = await this.ergonodeImportHistoryService.fetchImportLog(this.detailsId);
                if (result.status !== 200 || !result.data) {
                    throw result;
                }
                this.imports[this.detailsIndex].logs = result.data;
            } catch ({ message }) {
                console.error(message);
                this.createNotificationError({
                    message,
                });
                this.detailsId = null; // hide the modal on failure
            } finally {
                this.isDetailsLoading = false;
            }
        },

        showDetails (id) {
            this.detailsId = id;
            if (!this.imports[this.detailsIndex]?.logs.length) {
                return this.fetchLogs();
            }
        },
    },

    created () {
        return this.fetchImports();
    },
});
