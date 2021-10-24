<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddOrderColumnInToProductsStatTable extends AbstractMigration
{
    public function change(): void
    {
        $environment = $this->input->getParameterOption('-e');
        if (in_array($environment, ["production", "development", "testing"])) {
            $table = $this->table('sponsored_products_ads_stat');
            $table->addColumn(
                'orders',
                'integer',
                [
                    "after" => "ctr",
                    "comment" => "Количество заказов"
                ]
            )->save();
        }
    }
}
