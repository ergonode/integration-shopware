import template from './ergonode-category-attribute-mapping.html.twig';
import './ergonode-category-attribute-mapping.scss';

const { Component, Context, Data: { Criteria }, Mixin } = Shopware;

Component.register('ergonode-category-attribute-mapping', {
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
            createShopwareCategoryAttribute: null,
            createErgonodeCategoryAttribute: null,
            shopwareCategoryAttributes: [],
            ergonodeCategoryAttributes: [],
            mappings: [],
        };
    },

    computed: {
        repository () {
            return this.repositoryFactory.create('ergonode_category_attribute_mapping');
        },

        columns () {
            return [
                {
                    property: 'shopwareId',
                    label: this.$t('ErgonodeIntegrationShopware.mappings.shopwareCategoryAttribute'),
                    inlineEdit: false,
                },
                {
                    property: 'ergonodeKey',
                    label: this.$t('ErgonodeIntegrationShopware.mappings.ergonodeCategoryAttribute'),
                    inlineEdit: false,
                },
            ];
        },

        criteria () {
            return new Criteria()
                .addSorting(Criteria.sort('createdAt', 'DESC'));
        },

        shopwareCategoryAttributesSelectOptions () {
            return this.shopwareCategoryAttributes?.map(attribute => ({
                label: `${this.$t(attribute?.translationKey)}${attribute?.type ? ` (${attribute.type})` : ''}`,
                value: attribute?.code,
            }));
        },

        ergonodeCategoryAttributesSelectOptions () {
            return this.ergonodeCategoryAttributes?.map(attribute => ({
                label: `${attribute?.code}${attribute?.type ? ` (${attribute.type})` : ''}`,
                value: attribute?.code,
            }));
        },

        buttonDisabled () {
            return !(this.createShopwareCategoryAttribute && this.createErgonodeCategoryAttribute) || this.mappingAlreadyExists;
        },

        mappingAlreadyExists () {
            return this.mappings.some(mapping =>
                mapping.shopwareKey.toLowerCase() === this.createShopwareCategoryAttribute?.toLowerCase() &&
                mapping.ergonodeKey.toLowerCase() === this.createErgonodeCategoryAttribute?.toLowerCase()
            );
        },

        mappingCategoryAttributeOccupied () {
            return this.createShopwareCategoryAttribute &&
                this.mappings.some(mapping =>
                    mapping.shopwareKey.toLowerCase() === this.createShopwareCategoryAttribute?.toLowerCase()
                );
        },
    },

    methods: {
        type (set = 'shopwareCategoryAttributes', fieldName) {
            return this[set]?.find(field =>
                field?.code?.toLowerCase() === fieldName?.toLowerCase())?.type || '?';
        },

        translation (shopwareKey) {
            return this.shopwareCategoryAttributes.find(field => field?.code === shopwareKey)?.label || shopwareKey;
        },

        clearForm () {
            this.createShopwareCategoryAttribute = null;
            this.createErgonodeCategoryAttribute = null;
        },

        async fetchMappings () {
            this.isMappingLoading = true;
            this.mappings = await this.repository.search(this.criteria, Context.Api);
            this.isMappingLoading = false;
        },

        async addMapping () {
            this.isCreateLoading = true;
            try {
                if (this.mappingCategoryAttributeOccupied) {
                    throw new Error(this.$t('ErgonodeIntegrationShopware.mappings.messages.shopwareAttributesMustBeUnique'));
                }
                let createdMapping = this.repository.create(Context.Api);
                createdMapping.shopwareKey = this.createShopwareCategoryAttribute;
                createdMapping.ergonodeKey = this.createErgonodeCategoryAttribute;
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
            this.ergonodeMappingService.getShopwareCategoryAttributes().then(result => {
                this.shopwareCategoryAttributes = result.data.data;
            });

            this.ergonodeMappingService.getErgonodeCategoryAttributes().then(result => {
                this.ergonodeCategoryAttributes = result.data.data;
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
