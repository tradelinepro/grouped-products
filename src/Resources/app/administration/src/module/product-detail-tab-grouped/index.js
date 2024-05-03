import './view/sw-product-detail-grouped';

const { Module } = Shopware;

Module.register('sw-product-detail-grouped', {
    routeMiddleware(next, currentRoute) {
        if (currentRoute.name === 'sw.product.detail') {
            currentRoute.children.push({
                name: 'sw.product.detail.grouped',
                path: '/sw/product/detail/:id/grouped',
                component: 'sw-product-detail-grouped',
                meta: {
                    parentPath: 'sw.product.index',
                },
            });
        }

        next(currentRoute);
    }
});
