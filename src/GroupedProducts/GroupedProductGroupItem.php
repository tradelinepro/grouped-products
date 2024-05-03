<?php declare(strict_types=1);
/*
 * @author digital.manufaktur GmbH
 * @link   https://www.digitalmanufaktur.com/
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tradelinepro\GroupedProducts\GroupedProducts;

use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Struct\Struct;
use Tradelinepro\GroupedProducts\DataAbstractionLayer\Entity\ProductGrouped\ProductGroupedEntity;

#[Package('core')]
class GroupedProductGroupItem extends Struct
{
    protected ProductGroupedEntity $productGroup;

    protected ProductCollection $products;

    protected int $total;

    protected ?string $streamId = null;

    protected ?array $productsIds = null;

    public function getProductGroup(): ProductGroupedEntity
    {
        return $this->productGroup;
    }

    public function setProductGroup(ProductGroupedEntity $productGroup): void
    {
        $this->productGroup = $productGroup;
    }

    public function getProducts(): ProductCollection
    {
        return $this->products;
    }

    public function getProductsIds(): array
    {
        return (array) $this->productsIds;
    }

    public function setProducts(ProductCollection $products): void
    {
        $this->products = $products;
    }

    public function setProductIds(array $productIds): void
    {
        $this->productsIds = $productIds;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function setTotal(int $total): void
    {
        $this->total = $total;
    }

    public function getApiAlias(): string
    {
        return 'grouped_product_group_item';
    }

    public function getStreamId(): ?string
    {
        return $this->streamId;
    }

    public function setStreamId(?string $streamId): void
    {
        $this->streamId = $streamId;
    }
}
