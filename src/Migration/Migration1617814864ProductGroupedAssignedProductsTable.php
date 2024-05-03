<?php declare(strict_types=1);
/*
 * @author digital.manufaktur GmbH
 * @link   https://www.digitalmanufaktur.com/
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tradelinepro\GroupedProducts\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Log\Package;
use Tradelinepro\Core\Migration\Migration\AbstractMigrationStep;

#[Package('core')]
class Migration1617814864ProductGroupedAssignedProductsTable extends AbstractMigrationStep
{
    public function update(Connection $connection): void
    {
        $sql = <<< 'SQL'
CREATE TABLE IF NOT EXISTS `tradelinepro_product_grouped_assigned_products` (
    `id` BINARY(16) NOT NULL,
    `grouped_id` BINARY(16) NOT NULL,
    `product_id` BINARY(16) NOT NULL,
    `product_version_id` BINARY(16) NOT NULL,
    `position` INT(11) NOT NULL DEFAULT 0,
    `custom_fields` JSON NULL,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3),
    PRIMARY KEY (`id`),
    KEY `fk.tp_product_grouped_assigned_products.grouped_id` (`grouped_id`),
    KEY `fk.tp_product_grouped_assigned_products.product_id` (`product_id`, `product_version_id`),
    CONSTRAINT `fk.tp_product_grouped_assigned_products.grouped_id` FOREIGN KEY (`grouped_id`)
        REFERENCES `tradelinepro_product_grouped` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.tp_product_grouped_assigned_products.product_id` FOREIGN KEY (`product_id`, `product_version_id`)
        REFERENCES `product` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `json.tp_product_grouped_assigned_products.custom_fields` CHECK (JSON_VALID(`custom_fields`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL;
        $connection->executeStatement($sql);
    }
}
