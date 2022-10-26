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
            listingPage: 1,
            listingLimit: 25,
            detailsPage: 1,
            detailsLimit: 25,
            steps: [25, 50, 100, 200], // same as in ergonode
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
                    property: 'name',
                    label: this.$t('ErgonodeIntegrationShopware.importHistory.grid.name'),
                },
                {
                    property: 'status',
                    label: this.$t('ErgonodeIntegrationShopware.importHistory.grid.status'),
                },
                {
                    property: 'startDate',
                    label: this.$t('ErgonodeIntegrationShopware.importHistory.grid.startedOn'),
                },
                {
                    property: 'endDate',
                    label: this.$t('ErgonodeIntegrationShopware.importHistory.grid.endedOn'),
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
                {
                    property: 'context',
                    label: this.$t('ErgonodeIntegrationShopware.importHistory.details.context'),
                },
            ];
        },

        detailsListing () {
            const start = (this.detailsPage - 1) * this.detailsLimit;
            const items = this.detailsEntity?.logs?.slice(start, start + this.detailsLimit);
            if (this.isDetailsLoading) {
                this.isDetailsLoading = false;
            }
            return items;
        }
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
                .setTotalCountMode(1)
                .addSorting(
                    {
                        field: 'endDate',
                        order: 'DESC',
                    },
                    {
                        field: 'startDate',
                        order: 'DESC',
                    },
                )
                .setPage(this.listingPage)
                .setLimit(this.listingLimit);
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

        hideDetails () {
            this.detailsId = null;
            this.detailsPage = 1;
        },

        paginateListing ({ page, limit }) {
            this.listingPage = page;
            this.listingLimit = limit;
            return this.fetchImports();
        },

        paginateDetails ({ page, limit }) {
            this.isDetailsLoading = true;
            this.detailsPage = page;
            this.detailsLimit = limit;
            this.isDetailsLoading = false;
        },
    },

    created () {
        return this.fetchImports();
    },
});
