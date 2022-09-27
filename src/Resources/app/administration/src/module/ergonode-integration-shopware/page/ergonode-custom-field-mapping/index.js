import template from './ergonode-custom-field-mapping.html.twig';
import './ergonode-custom-field-mapping.scss';

const { Component, Context, Data: { Criteria }, Mixin } = Shopware;

Component.register('ergonode-custom-field-mapping', {
    inject: ['acl', 'repositoryFactory', 'ergonodeAttributeService'],

    mixins: [
        Mixin.getByName('notification'),
    ],

    template,

    data () {
        return {
            isLoading: false,
            isMappingLoading: false,
            isCreateLoading: false,
            createShopwareAttribute: null,
            createErgonodeAttribute: null,
            shopwareAttributes: [],
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
            ];
        },

        criteria () {
            return new Criteria()
                .addSorting(Criteria.sort('createdAt', 'DESC'));
        },

        shopwareAttributesSelectOptions () {
            return this.shopwareAttributes?.map(attribute => ({
                label: `${attribute?.code} - ${this.$tc(attribute?.translationKey)} ${attribute?.type ? ` (${attribute.type})` : ''}`,
                value: attribute?.code,
            }));
        },

        ergonodeAttributesSelectOptions () {
            return this.ergonodeAttributes?.map(attribute => ({
                label: `${attribute?.code}${attribute?.type ? ` (${attribute.type})` : ''}`,
                value: attribute?.code,
            }));
        },

        buttonDisabled () {
            return !(this.createShopwareAttribute && this.createErgonodeAttribute) || this.mappingAlreadyExists;
        },

        mappingAlreadyExists () {
            return this.mappings.some(mapping =>
                mapping.shopwareKey.toLowerCase() === this.createShopwareAttribute?.toLowerCase() &&
                mapping.ergonodeKey.toLowerCase() === this.createErgonodeAttribute?.toLowerCase()
            );
        },

        mappingAttributeOccupied () {
            return this.createShopwareAttribute &&
                this.mappings.some(mapping =>
                    mapping.shopwareKey.toLowerCase() === this.createShopwareAttribute?.toLowerCase()
                );
        },
    },

    methods: {
        attributeType (attributeSet = 'shopware', attributeName) {
            return this[`${attributeSet}Attributes`]?.find(attribute =>
                attribute?.code?.toLowerCase() === attributeName?.toLowerCase())?.type || '?';
        },
    
        attributeTranslation (shopwareKey) {
            const foundAttributes = this.shopwareAttributes.filter(attribute => attribute?.code === shopwareKey);
            
            if (!foundAttributes?.length) {
                return shopwareKey;
            }
            
            return this.$t(foundAttributes[0].translationKey);
        },
        
        clearForm () {
            this.createShopwareAttribute = null;
            this.createErgonodeAttribute = null;
        },

        async fetchMappings () {
            this.isMappingLoading = true;
            this.mappings = await this.repository.search(this.criteria, Context.Api);
            this.isMappingLoading = false;
        },

        async addMapping () {
            this.isCreateLoading = true;
            try {
                if (this.mappingAttributeOccupied) {
                    throw new Error(this.$t('ErgonodeIntegrationShopware.mappings.messages.shopwareAttributesMustBeUnique'));
                }
                let createdMapping = this.repository.create(Context.Api);
                createdMapping.ergonodeKey = this.createErgonodeAttribute;
                createdMapping.shopwareKey = this.createShopwareAttribute;
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
            this.ergonodeAttributeService.getShopwareCustomFields().then(result => {
                this.shopwareAttributes = result.data.data;
            });

            this.ergonodeAttributeService.getErgonodeAttributes().then(result => {
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
