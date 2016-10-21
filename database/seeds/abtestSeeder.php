<?php

use Illuminate\Database\Seeder;

class abtestSeeder extends Seeder {

    public function run()
    {

        DB::table('abtest')->delete();

        $abtestArr = array(
            [
                'id' => 1,
                'domain' => 'bigrock.in',
                'path' => '/content.php?action=mypages&page=cms-variation-5A.html',
                'testUrl' => '/web-hosting/cms-hosting.php',
                'status' => 'active',
                'visitorCount' => 0,
                'shownCount' => 0,
                'targetPercent' => 30,
                'created_at' => new DateTime,
                'updated_at' => new DateTime
            ]
        );

        DB::table('abtest')->insert($abtestArr);
    }

}