<?php

return [
    'enableS3' => env('ENABLE_S3', false),

    'provider' => 'https://mainnet.infura.io/v3/' . env('WEB3_INFURA_KEY'),
    'etherScanApiKey' => env('WEB3_ETHERSCAN_KEY'),
    'cmcProApiKey' => env('WEB3_CMC_KEY'),
    'cgProApiKey' => env('WEB3_CG_KEY'),

    'service' => [
        'py' => env('WEB3_SERVICE_PY', 'http://cb-py'),
        'web3' => env('WEB3_SERVICE_WEB3', 'http://cb-web3'),
    ],

    'erc20' => [
        'receiveTokenWallet' => [
            'address' => env('ERC20_RECEIVE_TOKEN_WALLET_ADDRESS'),
            'privateKey' => env('ERC20_RECEIVE_TOKEN_WALLET_PRIVATE_KEY'),
        ],
        'sendTokenWallet' => [
            'address' => env('ERC20_SEND_TOKEN_WALLET_ADDRESS'),
            'privateKey' => env('ERC20_SEND_TOKEN_WALLET_PRIVATE_KEY'),
        ],
        'approveToWallet' => [
            'address' => env('ERC20_APPROVE_TO_WALLET_ADDRESS'),
            'privateKey' => env('ERC20_APPROVE_TO_WALLET_PRIVATE_KEY'),
        ]
    ],
    'trc20' => [
        'receiveTokenWallet' => [
            'address' => env('TRC20_RECEIVE_TOKEN_WALLET_ADDRESS'),
            'privateKey' => env('TRC20_RECEIVE_TOKEN_WALLET_PRIVATE_KEY'),
        ],
        'sendTokenWallet' => [
            'address' => env('TRC20_SEND_TOKEN_WALLET_ADDRESS'),
            'privateKey' => env('TRC20_SEND_TOKEN_WALLET_PRIVATE_KEY'),
        ],
        'approveToWallet' => [
            'address' => env('TRC20_APPROVE_TO_WALLET_ADDRESS'),
            'privateKey' => env('TRC20_APPROVE_TO_WALLET_PRIVATE_KEY'),
        ]
    ],

    'telegram' => [
        'token' => env('TELEGRAM_BOT_TOKEN'),
        'chat_id' => env('TELEGRAM_BOT_CHAT_ID'),
    ]
];
