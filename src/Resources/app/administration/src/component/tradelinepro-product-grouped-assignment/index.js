import template from './tradelinepro-product-grouped-assignment.html.twig';
import './tradelinepro-product-grouped-assignment.scss';

const { mapGetters } = Shopware.Component.getComponentHelper();
const { Component, Context } = Shopware;
const { Criteria } = Shopware.Data;
const {version} = "vue";


Component.register('tradelinepro-product-grouped-assignment', {
    template,

    inject: ['repositoryFactory', 'feature'],

    props: {
        assignedProducts: {
            type: Array,
            required: true,
        },

        groupedId: {
            type: String,
            required: true,
        },

        allowEdit: {
            type: Boolean,
            required: false,
            default: true,
        },
    },

    data() {
        return {
            productId: null,
            isLoadingData: false,
            variantNames: {}
        };
    },

    created() {
        this.createdComponent();
    },

    computed: {
        ...mapGetters('swProductDetail', [
            'isLoading',
        ]),

        context() {
            return { ...Shopware.Context.api, inheritance: true };
        },

        isLoadingGrid() {
            return this.isLoadingData || this.isLoading;
        },

        assignmentRepository() {
            return this.repositoryFactory.create(this.assignedProducts.entity, this.assignedProducts.source);
        },

        productRepository() {
            return this.repositoryFactory.create('product');
        },

        searchCriteria() {
            const criteria = new Criteria();

            if (this.productId) {
                criteria.addFilter(Criteria.not('and', [Criteria.equals('id', this.productId)]));
            }

            criteria.addAssociation('options.group');

            return criteria;
        },

        searchContext() {
            return { ...Context.api, inheritance: true };
        },

        total() {
            if (!this.assignedProducts || !Array.isArray(this.assignedProducts)) {
                return 0;
            }

            return this.assignedProducts.length;
        },

        assignedProductColumns() {
            return [{
                property: 'product.translated.name',
                label: this.$tc('sw-product.list.columnName'),
                primary: true,
                allowResize: true,
                sortable: false,
            }, {
                property: 'product.productNumber',
                label: this.$tc('sw-product.list.columnProductNumber'),
                allowResize: true,
                sortable: false,
            }, {
                property: 'position',
                label: this.$tc('sw-product.crossselling.inputCrossSellingPosition'),
                allowResize: true,
                sortable: false,
            }];
        },

        emptyStateSubline() {
            return this.$tc('grouped-products.assignEmptyStateDescription');
        },

        variantProductIds() {
            const variantProductIds = [];

            this.assignedProducts.forEach((item) => {
                if (!item.product.parentId || item.product.translated.name || item.product.name) {
                    return;
                }

                variantProductIds.push(item.product.id);
            });

            return variantProductIds;
        },

        variantCriteria() {
            const criteria = new Criteria(1, 25);
            criteria.setIds(this.variantProductIds);

            return criteria;
        },

        assetFilter() {
            return Shopware.Filter.getByName('asset');
        }
    },

    methods: {
        createdComponent() {
            if (this.$route.params.id) {
                this.productId = this.$route.params.id;
            }

            if (this.variantProductIds.length > 0) {
                this.productRepository.search(this.variantCriteria, { ...Context.api, inheritance: true }).then((variants) => {
                    const variantNames = {};
                    variants.forEach((variant) => {
                        variantNames[variant.id] = variant.translated.name;
                    });
                    this.variantNames = variantNames;
                });
            }
        },

        onToggleProduct(productId) {
            if (productId === null) {
                return;
            }

            this.isLoadingData = true;

            const matchedAssignedProduct = this.assignedProducts.find((assignedProduct) => {
                return assignedProduct.productId === productId;
            });

            if (matchedAssignedProduct) {
                this.removeItem(matchedAssignedProduct);

                this.isLoadingData = false;
            } else {
                const newProduct = this.assignmentRepository.create();

                newProduct.groupedId = this.groupedId;
                newProduct.productId = productId;
                newProduct.position = this.assignedProducts.length + 1;

                this.assignedProducts.add(newProduct);

                const criteria = new Criteria(1, 25);
                criteria.addAssociation('options.group');
                this.productRepository.get(productId, { ...Context.api, inheritance: true }, criteria).then((product) => {
                    newProduct.product = product;

                    this.isLoadingData = false;
                });
            }
        },

        removeItem(item) {
            const oldPosition = item.position;

            this.assignedProducts.remove(item.id);

            this.assignedProducts.forEach((assignedProduct) => {
                if (assignedProduct.position > oldPosition) {
                    --assignedProduct.position;
                }
            });
        },

        isSelected(item) {
            return this.assignedProducts.some(p => p.productId === item.id);
        },
    },
});
