# TradelineproGroupedProducts

## Description

The Grouped products plugin allows you to display related products in groups on the frontend, simplifying the shopping process for your customers. An example application is order sets, where it makes sense to display all related products on one page, so that the customer can quickly and easily select the desired products and add them to the shopping basket. This feature is particularly useful when you want to link products that are not related through variants or similar constructs.

## Plugin Configuration

* The default product quantity can be set in the plugin settings.
* You can also configure the plugin to display the "Add to Cart" button for each individual product, rather than for the group as a whole.


## How To Use:

To create a grouped product, navigate to "Catalogues"=>"Products" in the backend.

A new grouped product can then be created by clicking on the arrow next to the "Add product" button at the top right and selecting "Add grouped product". When creating the product, only the name and product number are mandatory fields. Once you have saved, the product can be fully edited. As a grouped product only serves as a shell in the frontend, you should always add the media here, but the rest of the product data is not necessary.

The most important part is now to assign the products that will be part of this group. To do this, open the Groups tab.
Here you can either add individual products or assign a dynamic product group.


### Important Sidenote

* Since group products do not have prices, but shopware needs a tax id for all products, during the installation of the plugin it will be checked if there is already a tax with 0. If not it will be created (name Reduced rate 2) and used when creating group products.

## Setup & Development

### Install
```
composer require tradelinepro/grouped-products
```

### Setup

```
bin/console plugin:install --activate TradelineproGroupedProduct
```

## Compatibility

| Plugin version | Shopware version   | PHP version | 
|----------------|--------------------|-------------|
| `0.9.*`        | >=6.5.0 && < 6.7.0 | 8.2         |

### Licence
The MIT License (MIT). Please see [License File](LICENSE) for more information.

## Links/Reference (optional)

* [Documentation][WIKI]
* [Shopware 6.5][SW]

## Known Issues

* The price calculation for the minimum-price is done on the fly. This can sometimes cause performance problems. If this is the case, the price calculation should be triggered by an independent event instead.
* Creating many groups and using dynamic product groups, can sometimes cause performance problems.

## Troubleshooting


[WIKI]: https://docs.tradelinepro.de/gruppenprodukte
[SW]:https://docs.shopware.com/en/shopware-6-en/getting-started
