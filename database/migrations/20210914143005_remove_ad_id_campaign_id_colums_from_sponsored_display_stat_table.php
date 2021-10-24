<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RemoveAdIdCampaignIdColumsFromSponsoredDisplayStatTable extends AbstractMigration
{
    public function change(): void
    {
        $environment = $this->input->getParameterOption('-e');
        if (in_array($environment, ["production", "development", "testing"])) {
            $this->table('sponsored_display_ads_stat')->removeIndexByName('adIdByDate')->save();
            $this->table('sponsored_display_ads_stat')->removeColumn('campaignId')->save();
            $this->table('sponsored_display_ads_stat')->removeColumn('adId')->save();
        }
    }
}
