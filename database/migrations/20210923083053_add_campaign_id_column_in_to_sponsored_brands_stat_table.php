<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddCampaignIdColumnInToSponsoredBrandsStatTable extends AbstractMigration
{
    public function change(): void
    {
        $environment = $this->input->getParameterOption('-e');
        if (in_array($environment, ["production", "development", "testing"])) {
            $table = $this->table('sponsored_brands_ads_stat');
            $table->addColumn(
                'campaignId',
                'biginteger',
                [
                    "after" => "id",
                    "comment" => "Ид кампании в amazon"
                ]
            )->save();
        }
    }
}
