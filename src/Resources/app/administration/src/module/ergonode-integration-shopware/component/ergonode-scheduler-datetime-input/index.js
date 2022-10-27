import template from './ergonode-scheduler-datetime-input.html.twig';

const { Component } = Shopware;

Component.register('ergonode-scheduler-datetime-input', {
    template,

    props: {
        value: {
            type: String,
            required: false,
            default: null,
        },
        config: {
            type: Object,
            default() {
                return {};
            },
        },
        placeholderText: {
            type: String,
            default: '',
            required: false,
        },

        required: {
            type: Boolean,
            default: false,
            required: false,
        },

        disabled: {
            type: Boolean,
            default: false,
            required: false,
        },
    },



    methods: {
        onChange(value) {
            this.$emit('change', value);
        },
    },

    mounted () {
        console.log('$props', this.$props);
        console.log('$attrs', this.$attrs);
        console.log('$listeners', this.$listeners);
    },
});