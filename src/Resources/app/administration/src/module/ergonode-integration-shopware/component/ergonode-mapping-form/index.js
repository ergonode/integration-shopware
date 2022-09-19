import template from './ergonode-mapping-form.html.twig'

const { Component, Mixin } = Shopware;

Component.register('ergonode-mapping-form', {
    inject: ['acl', 'repositoryFactory', 'ergonodeAttributeService'],

    mixins: [
        Mixin.getByName('notification'),
    ],

    template,

    props: {

    },

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
            return this.repositoryFactory.create('ergonode_attribute_mapping');
        },

        shopwareAttributesSelectOptions () {
            return this.shopwareAttributes?.map(attribute => ({
                label: `${this.$tc(attribute?.translationKey)} ${attribute?.type ? ` (${attribute.type})` : ''}`,
                value: attribute?.code,
            }));
        },

        ergonodeAttributesSelectOptions () {
            return this.ergonodeAttributes?.map(attribute => ({
                label: `${attribute?.code}${attribute?.type ? ` (${attribute.type})` : ''}`,
                value: attribute?.code,
            }));
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
    },

    async created () {
        this.isLoading = true;

        try {
            this.ergonodeAttributeService.getShopwareAttributes().then(result => {
                this.shopwareAttributes = result.data.data
            });

            this.ergonodeAttributeService.getErgonodeAttributes().then(result => {
                this.ergonodeAttributes = result.data.data
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
