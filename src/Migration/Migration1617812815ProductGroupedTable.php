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
use Shopware\Core\Framework\Migration\InheritanceUpdaterTrait;
use Tradelinepro\Core\Migration\Migration\AbstractMigrationStep;

#[Package('core')]
class Migration1617812815ProductGroupedTable extends AbstractMigrationStep
{
    use InheritanceUpdaterTrait;

    public function update(Connection $connection): void
    {
        $sql = <<< 'SQL'
CREATE TABLE IF NOT EXISTS `tradelinepro_product_grouped` (
    `id` BINARY(16) NOT NULL,
    `product_id` BINARY(16) NOT NULL,
    `product_version_id` BINARY(16) NOT NULL,
    `product_stream_id` BINARY(16),
    `active` TINYINT(1),
    `type` ENUM('productList', 'productStream') NOT NULL,
    `limit` INT(11) NOT NULL DEFAULT 24,
    `position` INT(11) NOT NULL,
    `sort_by` VARCHAR(32) NOT NULL,
    `sort_direction` ENUM('ASC', 'DESC') NOT NULL DEFAULT 'ASC',
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3),
    PRIMARY KEY (`id`),
    CONSTRAINT `fk.tp_product_grouped.product_id` FOREIGN KEY (`product_id`, `product_version_id`)
        REFERENCES `product` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.tp_product_grouped.product_stream_id` FOREIGN KEY (`product_stream_id`)
        REFERENCES `product_stream` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL;
        $connection->executeStatement($sql);

        if (!$this->columnExists($connection, 'product', 'groups')) {
            $this->updateInheritance($connection, 'product', 'groups');
        }
    }
}
