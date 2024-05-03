<?php declare(strict_types=1);
/*
 * @author digital.manufaktur GmbH
 * @link   https://www.digitalmanufaktur.com/
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tradelinepro\GroupedProducts\DataAbstractionLayer\Entity\ProductGrouped;

use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Content\ProductStream\ProductStreamDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ReverseInherited;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Log\Package;
use Tradelinepro\GroupedProducts\DataAbstractionLayer\Entity\ProductGrouped\Aggregate\ProductGroupedAssignedProducts\ProductGroupedAssignedProductsDefinition;
use Tradelinepro\GroupedProducts\DataAbstractionLayer\Entity\ProductGrouped\Aggregate\ProductGroupedTranslation\ProductGroupedTranslationDefinition;

#[Package('core')]
class ProductGroupedDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'tradelinepro_product_grouped';
    final public const SORT_BY_PRICE = 'price';
    final public const SORT_BY_RELEASE_DATE = 'releaseDate';
    final public const SORT_BY_NAME = 'name';
    final public const TYPE_PRODUCT_STREAM = 'productStream';
    final public const TYPE_PRODUCT_LIST = 'productList';

    public function getEntityName(): string
    {
        return static::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return ProductGroupedEntity::class;
    }

    public function getCollectionClass(): string
    {
        return ProductGroupedCollection::class;
    }

    public function getParentDefinitionClass(): ?string
    {
        return ProductDefinition::class;
    }

    public function getDefaults(): array
    {
        return [
            'position' => 0,
            'sortBy' => static::SORT_BY_PRICE,
            'sortDirection' => FieldSorting::ASCENDING,
            'type' => static::TYPE_PRODUCT_STREAM,
            'active' => false,
            'limit' => 24,
        ];
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new ApiAware(), new PrimaryKey(), new Required()),
            (new TranslatedField('name'))->addFlags(new ApiAware(), new Required()),
            (new IntField('position', 'position', 0))->addFlags(new ApiAware(), new Required()),
            (new StringField('sort_by', 'sortBy', 32))->addFlags(new ApiAware()),
            (new StringField('sort_direction', 'sortDirection'))->addFlags(new ApiAware()),
            (new StringField('type', 'type'))->addFlags(new ApiAware(), new Required()),
            (new BoolField('active', 'active'))->addFlags(new ApiAware()),
            (new IntField('limit', 'limit', 0))->addFlags(new ApiAware()),
            (new FkField('product_id', 'productId', ProductDefinition::class))->addFlags(new Required()),
            (new ReferenceVersionField(ProductDefinition::class))->addFlags(new Required()),
            (new ManyToOneAssociationField('product', 'product_id', ProductDefinition::class))->addFlags(new ReverseInherited('groups')),
            new FkField('product_stream_id', 'productStreamId', ProductStreamDefinition::class),
            new ManyToOneAssociationField('productStream', 'product_stream_id', ProductStreamDefinition::class),
            (new OneToManyAssociationField('assignedProducts', ProductGroupedAssignedProductsDefinition::class, 'grouped_id'))->addFlags(new ApiAware(), new CascadeDelete()),
            (new TranslationsAssociationField(ProductGroupedTranslationDefinition::class, 'tradelinepro_product_grouped_id'))->addFlags(new ApiAware()),
        ]);
    }
}
