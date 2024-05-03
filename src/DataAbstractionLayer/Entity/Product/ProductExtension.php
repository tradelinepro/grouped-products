<?php declare(strict_types=1);
/*
 * @author digital.manufaktur GmbH
 * @link   https://www.digitalmanufaktur.com/
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tradelinepro\GroupedProducts\DataAbstractionLayer\Entity\Product;

use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Inherited;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Runtime;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\Log\Package;
use Tradelinepro\GroupedProducts\DataAbstractionLayer\Entity\ProductGrouped\Aggregate\ProductGroupedAssignedProducts\ProductGroupedAssignedProductsDefinition;
use Tradelinepro\GroupedProducts\DataAbstractionLayer\Entity\ProductGrouped\ProductGroupedDefinition;

#[Package('core')]
class ProductExtension extends EntityExtension
{
    public const GROUPS_EXTENSION_NAME = 'groups';
    public const EXTENSION_NAME = 'groupedProductGroupItems';
    public const PRICE_EXTENSION_NAME = 'groupedProduct';

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new OneToManyAssociationField(
                self::GROUPS_EXTENSION_NAME,
                ProductGroupedDefinition::class,
                'product_id'
            )
            )->addFlags(new ApiAware(), new CascadeDelete(), new Inherited())
        );

        $collection->add(
            (new OneToManyAssociationField(
                'groupedAssignedProducts',
                ProductGroupedAssignedProductsDefinition::class,
                'product_id'
            )
            )->addFlags(new CascadeDelete())
        );

        $collection->add(
            (new JsonField(self::EXTENSION_NAME, self::EXTENSION_NAME)
            )->addFlags(new Runtime(), new ApiAware())
        );

        $collection->add(
            (new JsonField(self::PRICE_EXTENSION_NAME, self::PRICE_EXTENSION_NAME)
            )->addFlags(new Runtime(), new ApiAware())
        );
    }

    public function getDefinitionClass(): string
    {
        return ProductDefinition::class;
    }
}
