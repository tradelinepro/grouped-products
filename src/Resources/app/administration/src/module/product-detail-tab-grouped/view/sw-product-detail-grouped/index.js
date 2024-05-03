import template from './sw-product-detail-grouped.html.twig';
import './sw-product-detail-grouped.scss';

const { Component } = Shopware;
const { Criteria } = Shopware.Data;
const { mapState, mapGetters } = Shopware.Component.getComponentHelper();

Component.register('sw-product-detail-grouped', {
    template,

    inject: ['repositoryFactory', 'acl'],

    props: {
        allowEdit: {
            type: Boolean,
            required: false,
            default: true,
        },
    },

    data() {
        return {
            grouped: null,
        };
    },

    computed: {
        ...mapState('swProductDetail', [
            'product',
        ]),

        ...mapGetters('swProductDetail', [
            'isLoading',
        ]),

        showGroupCard() {
            return !this.isLoading && this.product.extensions.groups && this.product.extensions.groups.length > 0;
        },

        tooltipDefault() {
            return {
                message: this.$tc('sw-privileges.tooltip.warning'),
                disabled: this.acl.can('product.editor'),
                showOnDisabledElements: true,
            };
        },
    },

    watch: {
        product(product) {
            product.extensions.groups.forEach((item) => {
                if (item.assignedProducts.length > 0) {
                    return;
                }

                this.loadAssignedProducts(item);
            });
        },
    },

    methods: {
        loadAssignedProducts(grouped) {
            const repository = this.repositoryFactory.create(
                grouped.assignedProducts.entity,
                grouped.assignedProducts.source
            );

            const criteria = new Criteria();
            criteria.addFilter(Criteria.equals('groupedId', grouped.id))
                .addSorting(Criteria.sort('position', 'ASC'))
                .addAssociation('product');

            repository.search(criteria, { ...Shopware.Context.api, inheritance: true }).then((assignedProducts) => {
                grouped.assignedProducts = assignedProducts;
            });

            return grouped;
        },

        onAddGrouped() {
            const groupedRepository = this.repositoryFactory.create(
                this.product.extensions.groups.entity,
                this.product.extensions.groups.source
            );

            this.grouped = groupedRepository.create(Shopware.Context.api);

            this.grouped.productId = this.product.id;
            this.grouped.position = this.product.extensions.groups.length + 1;
            this.grouped.type = 'productStream';
            this.grouped.sortBy = 'name';

            this.product.extensions.groups.push(this.grouped);
        },
    },
});
