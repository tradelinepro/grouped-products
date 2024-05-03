import template from './sw-product-detail.html.twig';

const { Component } = Shopware;
const { Criteria } = Shopware.Data;
const { mapState, mapGetters } = Shopware.Component.getComponentHelper();

Component.override('sw-product-detail', {
    template,

    data() {
        return {
            isGrouped: false,
        };
    },

    computed: {
        ...mapState('swProductDetail', [
            'product',
        ]),

        ...mapGetters('swProductDetail', [
            'isLoading',
        ]),

        productCriteria() {
            const criteria = this.$super('productCriteria');
            criteria.addAssociation('groups.assignedProducts');

            criteria.getAssociation('groups')
                .addSorting(Criteria.sort('position', 'ASC'))
                .getAssociation('assignedProducts')
                .addSorting(Criteria.sort('position', 'ASC'))
                .addAssociation('product');

            return criteria;
        },
    },

    watch: {
        product() {
            if (!this.productId) {
                this.isGrouped = !!this.$route.params.isGrouped;
            }

            if (!this.product) {
                return;
            }

            if (!this.product.customFields) {
                this.product.customFields = {};
            }

            if (this.product.customFields.isGrouped) {
                this.isGrouped = this.product.customFields.isGrouped;
            }

            if (this.isGrouped) {
                this.$set(this.product.customFields, 'isGrouped', this.isGrouped);
            }
        },
    },

    methods: {
        onSave() {
            if (this.isGrouped && !this.productId) {
                return this.saveProduct().then(this.onSaveFinished);
            }

            return this.$super('onSave');
        },
    },
});
