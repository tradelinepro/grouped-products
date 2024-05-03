import './sw-entity-listing.scss';

const { Component } = Shopware;

Component.override('sw-entity-listing', {
    methods: {
        createdComponent() {
            this.$super('createdComponent');
        },

        getRowClasses(item, itemIndex) {
            const rowClasses = this.$super('getRowClasses', item, itemIndex);

            if (item.customFields && item.customFields.isGrouped) {
                rowClasses.push('is--grouped');
            }

            return rowClasses;
        },
    },
});
