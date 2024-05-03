import template from './sw-product-list.html.twig';

const { Component } = Shopware;

Component.override('sw-product-list', {
    template,

    created() {
        this.createdComponent();
    },

    computed: {
        productColumns() {
            let columns = this.$super('productColumns');

            let alreadyExists = false;

            for (let i = 0; i < columns.length; ++i) {
                if (columns[i].property == 'customFields.isGrouped') {
                    alreadyExists = true;

                    break;
                }
            }

            if (alreadyExists) {
                return columns;
            }

            let start = -1;

            for (let i = 0; i < columns.length; ++i) {
                if (columns[i].property == 'active') {
                    start = i;

                    break;
                }
            }

            if (start >= 0) {
                columns.splice(++start, 0, {
                    property: 'customFields.isGrouped',
                    label: this.$tc('grouped-products.grouped'),
                    allowResize: true,
                    align: 'center',
                });
            }

            return columns;
        },
    },

    methods: {
        createdComponent() {
            try {
                this.$super('createdComponent');
            } catch { }
        }
    },
});
