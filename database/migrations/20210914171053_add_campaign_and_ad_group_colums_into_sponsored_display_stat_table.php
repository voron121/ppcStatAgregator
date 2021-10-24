<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddCampaignAndAdGroupColumsIntoSponsoredDisplayStatTable extends AbstractMigration
{
    public function change(): void
    {
        $environment = $this->input->getParameterOption('-e');
        if (in_array($environment, ["production", "development", "testing"])) {
            $table = $this->table('sponsored_display_ads_stat');
            $table->addColumn(
                'campaignName',
                'string',
                [
                    'limit' => 255,
                    'null' => false,
                    "comment" => "Название кампании в amazon",
                    'after' => 'id'
                ]
            );
            $table->addColumn(
                'adGroupName',
                'string',
                [
                    'limit' => 255,
                    'null' => false,
                    "comment" => "Название группы в amazon",
                    'after' => 'campaignName'
                ]
            );
            $table->save();
        }
    }
}
