import template from './tradelinepro-product-grouped-form.html.twig';
import './tradelinepro-product-grouped-form.scss';

const { Criteria } = Shopware.Data;
const { Component, Context, Utils } = Shopware;
const { mapPropertyErrors, mapGetters, mapState } = Component.getComponentHelper();

Component.register('tradelinepro-product-grouped-form', {
    template,

    inject: ['repositoryFactory', 'productStreamConditionService'],

    provide() {
        return {
            productCustomFields: {}
        };
    },

    props: {
        grouped: {
            type: Object,
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
            showDeleteModal: false,
            showModalPreview: false,
            productStream: null,
            productStreamFilter: [],
            productStreamFilterTree: null,
            optionSearchTerm: '',
            useManualAssignment: false,
            sortBy: 'name',
            sortDirection: 'ASC',
            assignmentKey: 0
        };
    },

    computed: {
        ...mapPropertyErrors('grouped', [
            'name',
            'type',
            'position',
        ]),

        ...mapState('swProductDetail', [
            'product',
        ]),

        ...mapGetters('swProductDetail', [
            'isLoading',
        ]),

        productGroupedRepository() {
            return this.repositoryFactory.create('tradelinepro_product_grouped');
        },

        productStreamRepository() {
            return this.repositoryFactory.create('product_stream');
        },

        productStreamFilterRepository() {
            if (!this.productStream) {
                return null;
            }

            const { entity, source } = this.productStream.filters;

            return this.repositoryFactory.create(entity, source);
        },

        productStreamFilterCriteria() {
            const criteria = new Criteria();

            criteria.addFilter(
                Criteria.equals('productStreamId', this.grouped.productStreamId)
            );

            return criteria;
        },

        groupedAssigmentRepository() {
            return this.repositoryFactory.create('tradelinepro_product_grouped_assigned_products');
        },

        sortingTypes() {
            return [{
                label: this.$tc('sw-product.crossselling.priceDescendingSortingType'),
                value: 'price:DESC',
            }, {
                label: this.$tc('sw-product.crossselling.priceAscendingSortingType'),
                value: 'price:ASC',
            }, {
                label: this.$tc('sw-product.crossselling.nameSortingType'),
                value: 'name:ASC',
            }, {
                label: this.$tc('sw-product.crossselling.releaseDateDescendingSortingType'),
                value: 'releaseDate:DESC',
            }, {
                label: this.$tc('sw-product.crossselling.releaseDateAscendingSortingType'),
                value: 'releaseDate:ASC',
            }];
        },

        groupedTypes() {
            return [{
                label: this.$tc('sw-product.crossselling.productListType'),
                value: 'productList',
            }];
        },

        previewDisabled() {
            return !this.productStream;
        },

        sortingConCat() {
            return `${this.grouped.sortBy}:${this.grouped.sortDirection}`;
        },

        disablePositioning() {
            return (!!this.term) || (this.sortBy !== 'position');
        },

        associationValue() {
            return Utils.get(this.grouped, 'productStreamId') || '';
        },
    },

    watch: {
        'grouped.productStreamId'() {
            if (this.streamPreviewAvailable()) {
                this.loadStreamPreview();
            }
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            // support only productList type for now
            this.grouped.type = 'productList';

            this.useManualAssignment = this.grouped.type === 'productList';

            if (this.streamPreviewAvailable()) {
                this.loadStreamPreview();
            }
        },

        streamPreviewAvailable() {
            return !this.useManualAssignment && this.grouped.productStreamId;
        },

        onShowDeleteModal() {
            this.showDeleteModal = true;
        },

        onCloseDeleteModal() {
            this.showDeleteModal = false;
        },

        onConfirmDelete() {
            this.onCloseDeleteModal();
            this.$nextTick(() => {
                this.product.extensions.groups.remove(this.grouped.id);
            });
        },

        openModalPreview() {
            if (this.previewDisabled) {
                return;
            }

            this.showModalPreview = true;
        },

        closeModalPreview() {
            this.showModalPreview = false;
        },

        loadStreamPreview() {
            this.productStreamRepository.get(this.grouped.productStreamId, Context.api)
                .then((productStream) => {
                    this.productStream = productStream;
                    this.getProductStreamFilter();
                });
        },

        getProductStreamFilter() {
            return this.productStreamFilterRepository.search(this.productStreamFilterCriteria, Context.api)
                .then((productStreamFilter) => {
                    this.productStreamFilter = productStreamFilter;
                });
        },

        updateProductStreamFilterTree({ conditions }) {
            this.productStreamFilterTree = conditions;
        },

        onSortingChanged(value) {
            [this.grouped.sortBy, this.grouped.sortDirection] = value.split(':');
        },

        onTypeChanged(value) {
            this.useManualAssignment = value === 'productList';
        },
    },
});
