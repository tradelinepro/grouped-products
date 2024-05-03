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
class Migration1617813724ProductGroupedTranslationTable extends AbstractMigrationStep
{
    public function update(Connection $connection): void
    {
        $sql = <<< 'SQL'
CREATE TABLE IF NOT EXISTS `tradelinepro_product_grouped_translation` (
    `tradelinepro_product_grouped_id` BINARY(16) NOT NULL,
    `language_id` BINARY(16) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `custom_fields` JSON NULL,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3) NULL,
    PRIMARY KEY (`tradelinepro_product_grouped_id`, `language_id`),
    CONSTRAINT `fk.tp_product_grouped_translation.tp_product_grouped_id` FOREIGN KEY (`tradelinepro_product_grouped_id`)
        REFERENCES `tradelinepro_product_grouped` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.tp_product_grouped_translation.language_id` FOREIGN KEY (`language_id`)
        REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `json.tp_product_grouped_translation.custom_fields` CHECK (JSON_VALID(`custom_fields`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL;
        $connection->executeStatement($sql);
    }
}
