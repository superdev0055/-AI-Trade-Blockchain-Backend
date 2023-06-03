<?php

namespace App\Consts;

class Constants
{

    public const LANGUAGES = [
        'ar' => 'ar',
        'de' => 'de',
        'es' => 'es',
        'fr' => 'fr',
        'hi' => 'hi',
        'id' => 'id',
        'it' => 'it',
        'ja' => 'ja',
        'ko' => 'ko',
        'pt' => 'pt',
        'ru' => 'ru',
        'th' => 'th',
        'tr' => 'tr',
        'vi' => 'vi',
        'zh-TW' => 'zh_TW',
    ];

    public const COINS = [
        [
            'id' => 1,
            'symbol' => 'usdc',
            'name' => 'USD Coin',
            'cgID' => 'usd-coin',
            'network' => 'ERC20',
            'address' => '0xa0b86991c6218b36c1d19d4a2e9eb0ce3606eb48',
            'precision' => 6
        ], [
            'id' => 2,
            'symbol' => 'usdt',
            'name' => 'Tether USD',
            'cgID' => 'tether',
            'network' => 'ERC20',
            'address' => '0xdac17f958d2ee523a2206206994597c13d831ec7',
            'precision' => 6
        ], [
            'id' => 3,
            'symbol' => 'eth',
            'name' => 'Ethereum',
            'cgID' => 'ethereum',
            'network' => 'ERC20',
            'address' => null,
            'precision' => 18
        ], [
            'id' => 4,
            'symbol' => 'usdc',
            'name' => 'USD Coin',
            'cgID' => 'usd-coin',
            'network' => 'TRC20',
            'address' => 'TEkxiTehnzSmSe2XqrBj4w32RUN966rdz8',
            'precision' => 6
        ],
        [
            'id' => 5,
            'symbol' => 'usdt',
            'name' => 'Tether USD',
            'cgID' => 'tether',
            'network' => 'TRC20',
            'address' => 'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t',
            'precision' => 6
        ],
        [
            'id' => 6,
            'symbol' => 'trx',
            'name' => 'Tron',
            'cgID' => 'tron',
            'network' => 'TRC20',
            'address' => null,
            'precision' => 6
        ],
    ];

    public const DURATIONS = [7, 15, 30, 60, 90, 180, 360];
}
