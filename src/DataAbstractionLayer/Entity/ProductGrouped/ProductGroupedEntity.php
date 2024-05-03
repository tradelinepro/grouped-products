<?php declare(strict_types=1);
/*
 * @author digital.manufaktur GmbH
 * @link   https://www.digitalmanufaktur.com/
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tradelinepro\GroupedProducts\DataAbstractionLayer\Entity\ProductGrouped;

use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\ProductStream\ProductStreamEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Log\Package;
use Tradelinepro\GroupedProducts\DataAbstractionLayer\Entity\ProductGrouped\Aggregate\ProductGroupedAssignedProducts\ProductGroupedAssignedProductsCollection;
use Tradelinepro\GroupedProducts\DataAbstractionLayer\Entity\ProductGrouped\Aggregate\ProductGroupedTranslation\ProductGroupedTranslationCollection;

#[Package('core')]
class ProductGroupedEntity extends Entity
{
    use EntityIdTrait;

    protected ?string $name = null;

    protected int $position;

    protected string $sortBy;

    protected string $sortDirection;

    protected int $limit;

    protected bool $active;

    protected string $productId;

    protected ?ProductEntity $product = null;

    protected string $productStreamId;

    protected ?ProductStreamEntity $productStream = null;

    protected string $type;

    protected ?ProductGroupedAssignedProductsCollection $assignedProducts = null;

    protected ?ProductGroupedTranslationCollection $translations = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getSortBy(): string
    {
        return $this->sortBy;
    }

    public function setSortBy(string $sortBy): void
    {
        $this->sortBy = $sortBy;
    }

    public function getSortDirection(): string
    {
        return $this->sortDirection;
    }

    public function setSortDirection(string $sortDirection): void
    {
        $this->sortDirection = $sortDirection;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    public function getProductId(): string
    {
        return $this->productId;
    }

    public function setProductId(string $productId): void
    {
        $this->productId = $productId;
    }

    public function getProduct(): ?ProductEntity
    {
        return $this->product;
    }

    public function setProduct(ProductEntity $product): void
    {
        $this->product = $product;
    }

    public function getProductStreamId(): ?string
    {
        return $this->productStreamId;
    }

    public function setProductStreamId(string $productStreamId): void
    {
        $this->productStreamId = $productStreamId;
    }

    public function getProductStream(): ?ProductStreamEntity
    {
        return $this->productStream;
    }

    public function setProductStream(ProductStreamEntity $productStream): void
    {
        $this->productStream = $productStream;
    }

    public function getTranslations(): ?ProductGroupedTranslationCollection
    {
        return $this->translations;
    }

    public function setTranslations(ProductGroupedTranslationCollection $translations): void
    {
        $this->translations = $translations;
    }

    public function getSorting(): FieldSorting
    {
        return new FieldSorting($this->sortBy, $this->sortDirection);
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getAssignedProducts(): ?ProductGroupedAssignedProductsCollection
    {
        return $this->assignedProducts;
    }

    public function setAssignedProducts(ProductGroupedAssignedProductsCollection $assignedProducts): void
    {
        $this->assignedProducts = $assignedProducts;
    }
}
