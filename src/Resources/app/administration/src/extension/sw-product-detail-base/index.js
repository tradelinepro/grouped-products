import template from './sw-product-detail-base.html.twig';

const { Component } = Shopware;
const { mapState, mapGetters } = Shopware.Component.getComponentHelper();

Component.override('sw-product-detail-base', {
    template,

    computed: {
        ...mapState('swProductDetail', [
            'product',
            'loading',
        ]),

        ...mapGetters('swProductDetail', [
            'isLoading',
        ]),

        isGrouped() {
            return this.product && this.product.customFields && this.product.customFields.isGrouped;
        },
    },
});
