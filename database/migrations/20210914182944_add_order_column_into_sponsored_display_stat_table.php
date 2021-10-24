<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddOrderColumnIntoSponsoredDisplayStatTable extends AbstractMigration
{
    public function change(): void
    {
        $environment = $this->input->getParameterOption('-e');
        if (in_array($environment, ["production", "development", "testing"])) {
            $table = $this->table('sponsored_display_ads_stat');
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
