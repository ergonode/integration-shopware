import template from './ergonode-custom-field-mapping.html.twig';
import './ergonode-custom-field-mapping.scss';

const { Component, Context, Data: { Criteria }, Mixin } = Shopware;

Component.register('ergonode-custom-field-mapping', {
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
            createShopwareCustomField: null,
            createErgonodeAttribute: null,
            createCastToBool: null,
            shopwareCustomFields: [],
            ergonodeAttributes: [],
            mappings: [],
        };
    },

    computed: {
        repository () {
            return this.repositoryFactory.create('ergonode_custom_field_mapping');
        },

        columns () {
            return [
                {
                    property: 'shopwareKey',
                    label: this.$t('ErgonodeIntegrationShopware.mappings.shopwareAttribute'),
                    inlineEdit: false,
                },
                {
                    property: 'ergonodeKey',
                    label: this.$t('ErgonodeIntegrationShopware.mappings.ergonodeAttribute'),
                    inlineEdit: false,
                },
                {
                    property: 'castToBool',
                    label: this.$t('ErgonodeIntegrationShopware.mappings.castToBool'),
                    inlineEdit: false,
                },
            ];
        },

        criteria () {
            return new Criteria()
                .addSorting(Criteria.sort('createdAt', 'DESC'));
        },

        shopwareCustomFieldsSelectOptions () {
            return this.shopwareCustomFields?.map(field => ({
                label: `${field?.label}${field?.type ? ` (${field.type})` : ''}`,
                value: field?.code,
            }));
        },

        ergonodeAttributesSelectOptions () {
            return this.ergonodeAttributes?.map(attribute => ({
                label: `${attribute?.code}${attribute?.type ? ` (${attribute.type})` : ''}`,
                value: attribute?.code,
            }));
        },

        buttonDisabled () {
            return !(this.createShopwareCustomField && this.createErgonodeAttribute) || this.mappingAlreadyExists;
        },

        mappingAlreadyExists () {
            return this.mappings.some(mapping =>
                mapping.shopwareKey.toLowerCase() === this.createShopwareCustomField?.toLowerCase() &&
                mapping.ergonodeKey.toLowerCase() === this.createErgonodeAttribute?.toLowerCase()
            );
        },

        mappingCustomFieldOccupied () {
            return this.createShopwareCustomField &&
                this.mappings.some(mapping =>
                    mapping.shopwareKey.toLowerCase() === this.createShopwareCustomField?.toLowerCase()
                );
        },
    },

    methods: {
        type (set = 'shopwareCustomFields', fieldName) {
            return this[set]?.find(field =>
                field?.code?.toLowerCase() === fieldName?.toLowerCase())?.type || '?';
        },

        translation (shopwareKey) {
            return this.shopwareCustomFields.find(field => field?.code === shopwareKey)?.label || shopwareKey;
        },

        clearForm () {
            this.createShopwareCustomField = null;
            this.createErgonodeAttribute = null;
            this.castToBool = null;
        },

        async fetchMappings () {
            this.isMappingLoading = true;
            this.mappings = await this.repository.search(this.criteria, Context.Api);
            this.isMappingLoading = false;
        },

        async addMapping () {
            this.isCreateLoading = true;
            try {
                if (this.mappingCustomFieldOccupied) {
                    throw new Error(this.$t('ErgonodeIntegrationShopware.mappings.messages.shopwareAttributesMustBeUnique'));
                }
                let createdMapping = this.repository.create(Context.Api);
                createdMapping.shopwareKey = this.createShopwareCustomField;
                createdMapping.ergonodeKey = this.createErgonodeAttribute;
                createdMapping.castToBool = this.createCastToBool;
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
            this.ergonodeMappingService.getShopwareCustomFields().then(result => {
                this.shopwareCustomFields = result.data.data;
            });

            this.ergonodeMappingService.getErgonodeAttributes().then(result => {
                this.ergonodeAttributes = result.data.data;
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
