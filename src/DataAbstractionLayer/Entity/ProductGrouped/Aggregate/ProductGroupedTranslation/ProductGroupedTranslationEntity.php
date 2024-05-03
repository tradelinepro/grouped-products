<?php declare(strict_types=1);
/*
 * @author digital.manufaktur GmbH
 * @link   https://www.digitalmanufaktur.com/
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tradelinepro\GroupedProducts\DataAbstractionLayer\Entity\ProductGrouped\Aggregate\ProductGroupedTranslation;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Shopware\Core\Framework\DataAbstractionLayer\TranslationEntity;
use Shopware\Core\Framework\Log\Package;
use Tradelinepro\GroupedProducts\DataAbstractionLayer\Entity\ProductGrouped\ProductGroupedEntity;

#[Package('core')]
class ProductGroupedTranslationEntity extends TranslationEntity
{
    use EntityCustomFieldsTrait;

    protected string $productGroupedId;

    protected ?string $name = null;

    protected ?ProductGroupedEntity $productGrouped = null;

    public function getProductGroupedId(): string
    {
        return $this->productGroupedId;
    }

    public function setProductGroupedId(string $productGroupedId): void
    {
        $this->productGroupedId = $productGroupedId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getProductGrouped(): ?ProductGroupedEntity
    {
        return $this->productGrouped;
    }

    public function setProductGrouped(ProductGroupedEntity $productGrouped): void
    {
        $this->productGrouped = $productGrouped;
    }
}
