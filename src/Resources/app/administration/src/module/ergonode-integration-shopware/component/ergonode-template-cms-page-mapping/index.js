import template from './ergonode-template-cms-page-mapping.html.twig';
import './ergonode-template-cms-page-mapping.scss';

const { Component } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('ergonode-template-cms-page-mapping', {
    template,

    props: {
        value: {
            type: Array,
            required: false,
            default: () => [],
        },
    },

    data() {
        return {
            fields: {
                templateName: {
                    valueKey: 'templateName',
                },
                cmsPage: {
                    name: 'cms_page',
                    valueKey: 'cmsPageId',
                },
            },
        };
    },

    computed: {
        cmsPageCriteria() {
            const criteria = new Criteria();

            criteria.addFilter(
                Criteria.equals('type', 'product_detail')
            );

            return criteria;
        },
    },

    methods: {
        emitChanges() {
            this.$emit('change', this.value);
        },

        addEntry() {
            this.value.push({});
        },

        removeEntry(index) {
            this.value.splice(index, 1);
        },

        onSelected(index, field, item) {
            // in case empty object was saved, it gets converted to empty array; convert it back to object
            if (Array.isArray(this.value[index])) {
                this.$set(this.value, index, { ...this.value[index] });
            }

            this.$set(this.value[index], field, item);

            this.emitChanges();
        },
    },
});