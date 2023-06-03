<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PlatformsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('platforms')->delete();
        
        \DB::table('platforms')->insert(array (
            0 => 
            array (
                'id' => 1,
                'cmc_id' => 1027,
                'name' => 'Ethereum',
                'symbol' => 'ETH',
                'slug' => 'ethereum',
                'abi' => NULL,
                'created_at' => '2022-10-10 15:35:54',
                'updated_at' => '2022-10-10 15:35:54',
            ),
            1 => 
            array (
                'id' => 2,
                'cmc_id' => 1839,
                'name' => 'BNB',
                'symbol' => 'BNB',
                'slug' => 'bnb',
                'abi' => NULL,
                'created_at' => '2022-10-10 15:35:54',
                'updated_at' => '2022-10-10 15:35:54',
            ),
            2 => 
            array (
                'id' => 3,
                'cmc_id' => 1958,
                'name' => 'TRON',
                'symbol' => 'TRX',
                'slug' => 'tron',
                'abi' => NULL,
                'created_at' => '2022-10-10 15:35:54',
                'updated_at' => '2022-10-10 15:35:54',
            ),
            3 => 
            array (
                'id' => 4,
                'cmc_id' => 4256,
                'name' => 'Klaytn',
                'symbol' => 'KLAY',
                'slug' => 'klaytn',
                'abi' => NULL,
                'created_at' => '2022-10-10 15:35:54',
                'updated_at' => '2022-10-10 15:35:54',
            ),
            4 => 
            array (
                'id' => 5,
                'cmc_id' => 11840,
                'name' => 'Optimism',
                'symbol' => 'OP',
                'slug' => 'optimism-ethereum',
                'abi' => NULL,
                'created_at' => '2022-10-10 15:35:54',
                'updated_at' => '2022-10-10 15:35:54',
            ),
            5 => 
            array (
                'id' => 6,
                'cmc_id' => 3794,
                'name' => 'Cosmos',
                'symbol' => 'ATOM',
                'slug' => 'cosmos',
                'abi' => NULL,
                'created_at' => '2022-10-10 15:35:54',
                'updated_at' => '2022-10-10 15:35:54',
            ),
            6 => 
            array (
                'id' => 7,
                'cmc_id' => 2502,
                'name' => 'Huobi Token',
                'symbol' => 'HT',
                'slug' => 'huobi-token',
                'abi' => NULL,
                'created_at' => '2022-10-10 15:35:54',
                'updated_at' => '2022-10-10 15:35:54',
            ),
            7 => 
            array (
                'id' => 8,
                'cmc_id' => 2469,
                'name' => 'Zilliqa',
                'symbol' => 'ZIL',
                'slug' => 'zilliqa',
                'abi' => NULL,
                'created_at' => '2022-10-10 15:35:54',
                'updated_at' => '2022-10-10 15:35:54',
            ),
            8 => 
            array (
                'id' => 9,
                'cmc_id' => 7505,
                'name' => 'Everscale',
                'symbol' => 'EVER',
                'slug' => 'everscale',
                'abi' => NULL,
                'created_at' => '2022-10-10 15:35:54',
                'updated_at' => '2022-10-10 15:35:54',
            ),
            9 => 
            array (
                'id' => 10,
                'cmc_id' => 5805,
                'name' => 'Avalanche',
                'symbol' => 'AVAX',
                'slug' => 'avalanche',
                'abi' => NULL,
                'created_at' => '2022-10-10 15:35:54',
                'updated_at' => '2022-10-10 15:35:54',
            ),
            10 => 
            array (
                'id' => 11,
                'cmc_id' => 3077,
                'name' => 'VeChain',
                'symbol' => 'VET',
                'slug' => 'vechain',
                'abi' => NULL,
                'created_at' => '2022-10-10 15:35:54',
                'updated_at' => '2022-10-10 15:35:54',
            ),
            11 => 
            array (
                'id' => 12,
                'cmc_id' => 3626,
                'name' => 'RSK Smart Bitcoin',
                'symbol' => 'RBTC',
                'slug' => 'rsk-smart-bitcoin',
                'abi' => NULL,
                'created_at' => '2022-10-10 15:35:55',
                'updated_at' => '2022-10-10 15:35:55',
            ),
            12 => 
            array (
                'id' => 13,
                'cmc_id' => 2566,
                'name' => 'Ontology',
                'symbol' => 'ONT',
                'slug' => 'ontology',
                'abi' => NULL,
                'created_at' => '2022-10-10 15:35:55',
                'updated_at' => '2022-10-10 15:35:55',
            ),
            13 => 
            array (
                'id' => 14,
                'cmc_id' => 5567,
                'name' => 'Celo',
                'symbol' => 'CELO',
                'slug' => 'celo',
                'abi' => NULL,
                'created_at' => '2022-10-10 15:35:55',
                'updated_at' => '2022-10-10 15:35:55',
            ),
            14 => 
            array (
                'id' => 15,
                'cmc_id' => 512,
                'name' => 'Stellar',
                'symbol' => 'XLM',
                'slug' => 'stellar',
                'abi' => NULL,
                'created_at' => '2022-10-10 15:35:55',
                'updated_at' => '2022-10-10 15:35:55',
            ),
            15 => 
            array (
                'id' => 16,
                'cmc_id' => 2010,
                'name' => 'Cardano',
                'symbol' => 'ADA',
                'slug' => 'cardano',
                'abi' => NULL,
                'created_at' => '2022-10-10 15:35:55',
                'updated_at' => '2022-10-10 15:35:55',
            ),
            16 => 
            array (
                'id' => 17,
                'cmc_id' => 5426,
                'name' => 'Solana',
                'symbol' => 'SOL',
                'slug' => 'solana',
                'abi' => NULL,
                'created_at' => '2022-10-10 15:35:55',
                'updated_at' => '2022-10-10 15:35:55',
            ),
            17 => 
            array (
                'id' => 18,
                'cmc_id' => 3890,
                'name' => 'Polygon',
                'symbol' => 'MATIC',
                'slug' => 'polygon',
                'abi' => NULL,
                'created_at' => '2022-10-10 15:35:55',
                'updated_at' => '2022-10-10 15:35:55',
            ),
            18 => 
            array (
                'id' => 19,
                'cmc_id' => 1376,
                'name' => 'Neo',
                'symbol' => 'NEO',
                'slug' => 'neo',
                'abi' => NULL,
                'created_at' => '2022-10-10 15:35:55',
                'updated_at' => '2022-10-10 15:35:55',
            ),
            19 => 
            array (
                'id' => 20,
                'cmc_id' => 4172,
                'name' => 'Terra Classic',
                'symbol' => 'LUNC',
                'slug' => 'terra-luna',
                'abi' => NULL,
                'created_at' => '2022-10-10 15:35:55',
                'updated_at' => '2022-10-10 15:35:55',
            ),
            20 => 
            array (
                'id' => 21,
                'cmc_id' => 4066,
                'name' => 'Chiliz',
                'symbol' => 'CHZ',
                'slug' => 'chiliz',
                'abi' => NULL,
                'created_at' => '2022-10-10 15:35:55',
                'updated_at' => '2022-10-10 15:35:55',
            ),
        ));
        
        
    }
}