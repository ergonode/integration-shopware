import template from './strix-ergonode-attribute-mapping.html.twig';
import './strix-ergonode-attribute-mapping.scss';

const { Component, Context, Data: { Criteria }, Mixin } = Shopware;

Component.register('strix-ergonode-attribute-mapping', {
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
            return this.repositoryFactory.create('strix_ergonode_attribute_mapping');
        },

        columns () {
            return [
                {
                    property: 'shopwareKey',
                    label: this.$t('StrixErgonode.mappings.shopwareAttribute'),
                    inlineEdit: 'string',
                },
                {
                    property: 'ergonodeKey',
                    label: this.$t('StrixErgonode.mappings.ergonodeAttribute'),
                    inlineEdit: 'string',
                },
            ];
        },

        shopwareAttributesSelectOptions () {
            return this.shopwareAttributes?.map((attributeName, index) => ({
                label: attributeName,
                value: attributeName,
            }));
        },

        ergonodeAttributesSelectOptions () {
            return this.ergonodeAttributes?.map((attributeName, index) => ({
                label: attributeName,
                value: attributeName,
            }));
        },

        buttonDisabled () {
            return !(this.createShopwareAttribute && this.createErgonodeAttribute) || this.mappingAlreadyExists;
        },

        mappingAlreadyExists () {
            return this.mappings?.some(mapping =>
                mapping.shopwareKey == this.createShopwareAttribute &&
                mapping.ergonodeKey == this.createErgonodeAttribute
            );
        },

        mappingAttributeOccupied () {
            return this.createShopwareAttribute &&
                this.mappings?.some(mapping => mapping.shopwareKey == this.createShopwareAttribute);
        },
    },

    methods: {
        clearForm () {
            this.createShopwareAttribute = null;
            this.createErgonodeAttribute = null;
        },

        async fetchMappings () {
            this.isMappingLoading = true;
            const criteria = new Criteria()
                .setLimit(null);
            this.mappings = await this.repository?.search(criteria, Context.Api);
            this.isMappingLoading = false;
        },

        async addMapping () {
            this.isCreateLoading = true;
            try {
                if (this.mappingAttributeOccupied) {
                    throw new Error(this.$t('StrixErgonode.mappings.messages.shopwareAttributesMustBeUnique'));
                }
                let createdMapping = this.repository?.create(Context.Api);
                createdMapping.ergonodeKey = this.createErgonodeAttribute;
                createdMapping.shopwareKey = this.createShopwareAttribute;
                await this.repository.save(createdMapping, Context.Api);

                this.clearForm();
                await this.fetchMappings();

                this.createNotificationSuccess({
                    message: this.$t('StrixErgonode.mappings.messages.mappingCreationSuccessful'),
                });
            } catch (e) {
                this.createNotificationError({
                    message: e?.message || this.$t('StrixErgonode.mappings.messages.mappingCreationFailure')
                });
                console.error(e);
            } finally {
                this.isCreateLoading = false;
            }
        },

    },

    async created () {
        this.isLoading = true;

        try {
            let result;

            result = await this.ergonodeAttributeService.getShopwareAttributes();
            this.shopwareAttributes = result.data.data;

            result = await this.ergonodeAttributeService.getErgonodeAttributes();
            this.ergonodeAttributes = result.data.data;

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
