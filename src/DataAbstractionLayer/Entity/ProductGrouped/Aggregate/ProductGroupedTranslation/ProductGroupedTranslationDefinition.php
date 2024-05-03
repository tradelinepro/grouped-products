<?php declare(strict_types=1);
/*
 * @author digital.manufaktur GmbH
 * @link   https://www.digitalmanufaktur.com/
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tradelinepro\GroupedProducts\DataAbstractionLayer\Entity\ProductGrouped\Aggregate\ProductGroupedTranslation;

use Shopware\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CustomFields;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\Log\Package;
use Tradelinepro\GroupedProducts\DataAbstractionLayer\Entity\ProductGrouped\ProductGroupedDefinition;

#[Package('core')]
class ProductGroupedTranslationDefinition extends EntityTranslationDefinition
{
    final public const ENTITY_NAME = 'tradelinepro_product_grouped_translation';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return ProductGroupedTranslationCollection::class;
    }

    public function getEntityClass(): string
    {
        return ProductGroupedTranslationEntity::class;
    }

    protected function getParentDefinitionClass(): string
    {
        return ProductGroupedDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new StringField('name', 'name'))->addFlags(new ApiAware(), new Required()),
            (new CustomFields())->addFlags(new ApiAware()),
        ]);
    }
}
