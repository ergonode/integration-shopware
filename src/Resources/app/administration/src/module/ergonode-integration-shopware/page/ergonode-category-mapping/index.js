import template from './ergonode-category-mapping.html.twig';
import './ergonode-category-mapping.scss';

const { Component, Context, Data: { Criteria }, Mixin } = Shopware;

Component.register('ergonode-category-mapping', {
    inject: ['acl', 'repositoryFactory', 'ergonodeMappingService'],

    mixins: [
        Mixin.getByName('notification'),
    ],

    template,

    data () {
        return {
            isLoading: false,
            isMappingLoading: false,
            isCreateLoading: false,
            createShopwareCategory: null,
            createErgonodeCategory: null,
            shopwareCategories: [],
            ergonodeCategories: [],
            mappings: [],
        };
    },

    computed: {
        repository () {
            return this.repositoryFactory.create('ergonode_category_mapping');
        },

        columns () {
            return [
                {
                    property: 'shopwareId',
                    label: this.$t('ErgonodeIntegrationShopware.mappings.shopwareCategory'),
                    inlineEdit: false,
                },
                {
                    property: 'ergonodeKey',
                    label: this.$t('ErgonodeIntegrationShopware.mappings.ergonodeCategory'),
                    inlineEdit: false,
                },
            ];
        },

        criteria () {
            return new Criteria()
                .addAssociation('category')
                .addFilter(Criteria.not('AND',[Criteria.equals('category.id', null)]))
                .addSorting(Criteria.sort('createdAt', 'DESC'));
        },

        shopwareCategoriesSelectOptions () {
            return this.shopwareCategories?.map(field => ({
                label: field?.name,
                value: field?.id,
            }));
        },

        ergonodeCategoriesSelectOptions () {
            return this.ergonodeCategories?.map(attribute => ({
                label: `${attribute?.code}${attribute?.type ? ` (${attribute.type})` : ''}`,
                value: attribute?.code,
            }));
        },

        buttonDisabled () {
            return !(this.createShopwareCategory && this.createErgonodeCategory) || this.mappingAlreadyExists;
        },

        mappingAlreadyExists () {
            return this.mappings.some(mapping =>
                mapping.shopwareId.toLowerCase() === this.createShopwareCategory?.toLowerCase() &&
                mapping.ergonodeKey.toLowerCase() === this.createErgonodeCategory?.toLowerCase()
            );
        },

        mappingCategoryOccupied () {
            return this.createShopwareCategory &&
                this.mappings.some(mapping =>
                    mapping.shopwareId.toLowerCase() === this.createShopwareCategory?.toLowerCase()
                );
        },
    },

    methods: {
        type (set = 'shopwareCategories', fieldName) {
            return this[set]?.find(field =>
                field?.code?.toLowerCase() === fieldName?.toLowerCase())?.type || '?';
        },

        translation (shopwareKey) {
            return this.shopwareCategories.find(field => field?.code === shopwareKey)?.label || shopwareKey;
        },

        clearForm () {
            this.createShopwareCategory = null;
            this.createErgonodeCategory = null;
        },

        async fetchMappings () {
            this.isMappingLoading = true;
            this.mappings = await this.repository.search(this.criteria, Context.Api);
            this.isMappingLoading = false;
        },

        async addMapping () {
            this.isCreateLoading = true;
            try {
                if (this.mappingCategoryOccupied) {
                    throw new Error(this.$t('ErgonodeIntegrationShopware.mappings.messages.shopwareAttributesMustBeUnique'));
                }
                let createdMapping = this.repository.create(Context.Api);
                createdMapping.shopwareId = this.createShopwareCategory;
                createdMapping.ergonodeKey = this.createErgonodeCategory;
                await this.repository.save(createdMapping, Context.Api);

                this.clearForm();
                await this.fetchMappings();

                this.createNotificationSuccess({
                    message: this.$t('ErgonodeIntegrationShopware.mappings.messages.mappingCreationSuccessful'),
                });
            } catch (e) {
                console.error(e);
                this.createNotificationError({
                    message: e?.message || this.$t('ErgonodeIntegrationShopware.mappings.messages.mappingCreationFailure')
                });
            } finally {
                this.isCreateLoading = false;
            }
        },

    },

    async created () {
        this.isLoading = true;

        try {
            this.ergonodeMappingService.getShopwareCategories().then(result => {
                this.shopwareCategories = result.data.data;
            });

            this.ergonodeMappingService.getErgonodeCategories().then(result => {
                this.ergonodeCategories = result.data.data;
            });

            await this.fetchMappings();
        } catch (e) {
            console.error(e);
            this.createNotificationError({
                message: e.message,
            });
        } finally {
            this.isLoading = false;
        }
    }
});
