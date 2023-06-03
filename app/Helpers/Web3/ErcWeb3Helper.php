<?php

namespace App\Helpers\Web3;

use App\Enums\CoinNetworkEnum;
use App\Helpers\Web3\Exceptions\TransactionFailedException;
use App\NewServices\ConfigsServices;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use LaravelCommon\App\Exceptions\Err;
use LaravelCommon\App\Helpers\CommonHelper;
use phpseclib\Math\BigInteger;
use Web3\Contract;
use Web3\Providers\HttpProvider;
use Web3\RequestManagers\HttpRequestManager;
use Web3\Utils;
use Web3\Web3;
use Web3p\EthereumTx\Transaction;

class ErcWeb3Helper extends BaseWeb3Helper implements IWeb3Helper
{
    private Web3 $web3;
    private string $erc20Abi = '[{"constant":true,"inputs":[],"name":"name","outputs":[{"name":"","type":"string"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"spender","type":"address"},{"name":"value","type":"uint256"}],"name":"approve","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"totalSupply","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"sender","type":"address"},{"name":"recipient","type":"address"},{"name":"amount","type":"uint256"}],"name":"transferFrom","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"decimals","outputs":[{"name":"","type":"uint8"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"spender","type":"address"},{"name":"addedValue","type":"uint256"}],"name":"increaseAllowance","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"value","type":"uint256"}],"name":"burn","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"name":"account","type":"address"}],"name":"balanceOf","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"symbol","outputs":[{"name":"","type":"string"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"spender","type":"address"},{"name":"subtractedValue","type":"uint256"}],"name":"decreaseAllowance","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"recipient","type":"address"},{"name":"amount","type":"uint256"}],"name":"transfer","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"name":"owner","type":"address"},{"name":"spender","type":"address"}],"name":"allowance","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"inputs":[{"name":"name","type":"string"},{"name":"symbol","type":"string"},{"name":"decimals","type":"uint8"},{"name":"totalSupply","type":"uint256"},{"name":"feeReceiver","type":"address"},{"name":"tokenOwnerAddress","type":"address"}],"payable":true,"stateMutability":"payable","type":"constructor"},{"anonymous":false,"inputs":[{"indexed":true,"name":"from","type":"address"},{"indexed":true,"name":"to","type":"address"},{"indexed":false,"name":"value","type":"uint256"}],"name":"Transfer","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"owner","type":"address"},{"indexed":true,"name":"spender","type":"address"},{"indexed":false,"name":"value","type":"uint256"}],"name":"Approval","type":"event"}]';

    public function __construct()
    {
        $this->web3 = new Web3(new HttpProvider(new HttpRequestManager(config('web3.provider'), 30)));
    }

    /**
     * @param string $contractAddress
     * @param string $walletAddress
     * @return BigInteger
     */
    public function getTokenBalance(string $contractAddress, string $walletAddress): BigInteger
    {
        $contract = new Contract($this->web3->provider, $this->erc20Abi);
        $contract->at($contractAddress)->call('balanceOf', $walletAddress, [
            'from' => $walletAddress
        ], function ($err, $result) use (&$balance) {
            if ($err !== null) {
                $balance = new BigInteger(0);
            }
            $balance = $result[0];
//            if (isset($result)) {
//                // $bn = Utils::toBn($result);
//                // $this->assertEquals($bn->toString(), '10000', 'Balance should be 10000.');
//            }
        });
        return $balance;
    }

    /**
     * @param string $toAddress
     * @param float $amount
     * @param string|null $fromAddress
     * @param string|null $fromPrivateKey
     * @param bool $debug
     * @return string
     * @throws Err
     */
    public function Send(string $toAddress, float $amount, ?string $fromAddress = null, ?string $fromPrivateKey = null, bool $debug = false): string
    {
        $config = ConfigsServices::Get('address');
        if (!$fromAddress)
            $fromAddress = $config['send']; //Web3Wallets::getErcSendTokenWallet()->address;
        if (!$fromPrivateKey)
            $fromPrivateKey = $config['send']; //Web3Wallets::getErcSendTokenWallet()->privateKey;

        $amount = Utils::toWei('' . $amount, 'ether');

        $txParams = [
            'nonce' => '0x' . self::getNonce($fromAddress)->toHex(),
            'from' => $fromAddress,
            'to' => $toAddress,
            'gas' => '0x' . dechex(100000),
            'gasPrice' => $this->getGasPrice(),
            'value' => '0x' . $amount->toHex(),
            'chainId' => 1,
        ];
        if ($debug)
            dump($txParams);

        $transaction = new Transaction($txParams);
        $signedTransaction = $transaction->sign($fromPrivateKey);
        return $this->sendRawTransaction($signedTransaction);
    }

    /**
     * @param string $contractAddress
     * @param string $toAddress
     * @param float $amount
     * @param string|null $fromAddress
     * @param string|null $fromPrivateKey
     * @param bool $debug
     * @return string
     * @throws Err
     */
    public function SendToken(string $contractAddress, string $toAddress, float $amount, ?string $fromAddress = null, ?string $fromPrivateKey = null, bool $debug = false): string
    {
        $config = ConfigsServices::Get('address');

        if (!$fromAddress)
            $fromAddress = $config['send']; // Web3Wallets::getErcSendTokenWallet()->address;
        if (!$fromPrivateKey)
            $fromPrivateKey = $config['send_private_key']; // Web3Wallets::getErcSendTokenWallet()->privateKey;

        $amount = Utils::toWei('' . $amount, 'mwei');

        $contract = new Contract($this->web3->provider, $this->erc20Abi);
        $data = '0x' . $contract->at($contractAddress)->getData('transfer', $toAddress, $amount->value);

        $txParams = [
            'nonce' => '0x' . self::getNonce($fromAddress)->toHex(),
            'from' => $fromAddress,
            'to' => $contractAddress,
            'gas' => '0x' . dechex(100000),
            'gasPrice' => $this->getGasPrice(),
            'value' => '0x0',
            'chainId' => 1,
            'data' => $data,
        ];
        if ($debug)
            dump($txParams);

        $transaction = new Transaction($txParams);
        $signedTransaction = $transaction->sign($fromPrivateKey);
        return $this->sendRawTransaction($signedTransaction);
    }

    /**
     * @param string $hash
     * @param bool $debug
     * @param string|null $toAddress
     * @return HashDataModel|null
     * @throws Err
     * @throws TransactionFailedException
     */
    public function GetTransactionByHash(string $hash, bool $debug = false, string $toAddress = null): ?HashDataModel
    {
        $this->web3->getEth()->getTransactionReceipt($hash, function ($err, $data) use (&$receipt) {
            if ($err !== null)
                Err::Throw($err->getMessage());
            $receipt = $data;
        });
        if ($debug)
            dump(json_encode($receipt));

        if ($receipt->status != "0x1")
            throw new TransactionFailedException(__("Web3 transaction receipt status is not OK"));

        $this->web3->getEth()->getTransactionByHash($hash, function ($err, $data) use (&$hashData, $debug) {
            if ($err !== null)
                Err::Throw($err->getMessage());
            $hashData = $data;
        });
        if ($debug)
            dump(json_encode($hashData));

        return $this->parseHashDataFromJsonRPC($receipt, $hashData, $toAddress);
    }

//    /**
//     * @param string $address
//     * @param int|null $start
//     * @param bool|null $debug
//     * @return HashDataModel[]
//     */
//    public function GetTransactionsByAddress(string $address, ?int $start = 0, ?bool $debug = false): array
//    {
//        $hashArr = [];
//        $hashDataModel = [];
//
//        $erc20 = EtherScanApi::GetErc20TransactionsByAddress($address, $start);
//        foreach ($erc20 as $item) {
//            $hashArr[] = $item['hash'];
//            $data = $this->parseHashDataFromErc20Transaction($item);
//            if ($data)
//                $hashDataModel[] = $data;
//        }
//
//        $normal = EtherScanApi::GetNormalTransactionsByAddress($address, $start);
//        foreach ($normal as $item) {
//            if (in_array($item['hash'], $hashArr)) {
//                continue;
//            }
//            $data = $this->parseHashDataFromNormalTransaction($item);
//            if ($data)
//                $hashDataModel[] = $data;
//        }
//
//        return $hashDataModel;
//    }

    /**
     * @param $signedTransaction
     * @return string
     * @throws Err
     */
    private function sendRawTransaction($signedTransaction): string
    {
        $this->web3->getEth()->sendRawTransaction('0x' . $signedTransaction, function ($err, $tx) use (&$hash) {
            if ($err !== null) {
                Err::Throw($err->getMessage());
            }
            $hash = $tx;
        });
        return $hash;
    }

    /**
     * @param $address
     * @return BigInteger
     * @throws Err
     */
    private function getNonce($address): BigInteger
    {
        $this->web3->getEth()->getTransactionCount($address, function ($err, $data) use (&$result) {
            if ($err !== null)
                Err::Throw($err->getMessage());
            $result = $data;
        });
        return $result;
    }

    /**
     * @return string
     */
    private function getGasPrice(): string
    {
        $res = Http::withOptions(['proxy' => CommonHelper::GetProxies()])
            ->get('https://api.etherscan.io/api', [
                'module' => 'gastracker',
                'action' => 'gasoracle',
                'apikey' => config('web3.etherScanApiKey'),
            ])
            ->json();
        return '0x' . Utils::toWei($res['result']['FastGasPrice'], 'Gwei')->toHex();
    }

    /**
     * @param mixed $receipt
     * @param mixed $hashData
     * @param string|null $toAddress
     * @return HashDataModel
     * @throws TransactionFailedException
     */
    private function parseHashDataFromJsonRPC(mixed $receipt, mixed $hashData, ?string $toAddress = null): HashDataModel
    {
        try {
            if ($hashData->input != '0x' && strlen($hashData->input) < 200) {
                // ERC20 Token

                $coin = $this->getCoinByAddress($hashData->to);
                if (!$coin)
                    throw new TransactionFailedException('Not allowed coin symbol' . $hashData->to);

                // parse input
                list($method, $toAddress, $amount) = $this->parseInputData($hashData->input, $coin);

                return new HashDataModel(
                    coin_network: CoinNetworkEnum::ERC20->name,
                    coin_symbol: $coin['symbol'],
                    coin_amount: $amount,

                    from_address: $hashData->from,
                    to_address: $toAddress,

                    method: $method,

                    hash: $hashData->hash,
                    block_number: hexdec($hashData->blockNumber),
                    timestamp: 1,

                    raw_data: json_encode(['receipt' => $receipt, 'hashData' => $hashData]),

                    confirmed: true,
                    result: 'SUCCESS',

                    coin_address: $coin['address'],
                    owner_address: $hashData->from,
                );
            } elseif ($hashData->input != '0x' && strlen($hashData->input) > 200) {
                if (!property_exists($receipt, 'logs'))
                    Err::Throw(__("The transaction hash cannot be recognized"));

                if (!$toAddress)
                    Err::Throw(__("No target address"));

                foreach ($receipt->logs as $item) {
                    if (!property_exists($item, 'topics'))
                        Err::Throw(__("The transaction hash cannot be recognized"));

                    $a1 = strtolower($item->topics[2]);
                    $a2 = str_replace('0x', '', strtolower($toAddress));
                    if (str_contains($a1, $a2)) {
                        $coin = $this->getCoinByAddress($item->address);
                        if (!$coin)
                            throw new TransactionFailedException('系统不支持的coin' . $hashData->to);

                        $amount = floatval(Utils::toBn($item->data)->value) / pow(10, $coin['precision']);

                        return new HashDataModel(
                            coin_network: CoinNetworkEnum::ERC20->name,
                            coin_symbol: $coin['symbol'],
                            coin_amount: $amount,

                            from_address: $hashData->from,
                            to_address: $toAddress,

                            method: 'transfer',

                            hash: $hashData->hash,
                            block_number: hexdec($hashData->blockNumber),
                            timestamp: 1,

                            raw_data: json_encode(['receipt' => $receipt, 'hashData' => $hashData]),

                            confirmed: true,
                            result: 'SUCCESS',

                            coin_address: $coin['address'],
                            owner_address: $hashData->from,
                        );
                    }
                }
                Err::Throw(__("There is no key information required by the system in the transaction"));
            } else {
                // ETH
                $coin = $this->getCoinBySymbol('eth');
                $amount = floatval(hexdec($hashData->value)) / pow(10, $coin['precision']);

                return new HashDataModel(
                    coin_network: CoinNetworkEnum::ERC20->name,
                    coin_symbol: 'eth',
                    coin_amount: $amount,

                    from_address: $hashData->from,
                    to_address: $hashData->to,

                    method: 'ETH Transfer',

                    hash: $hashData->hash,
                    block_number: hexdec($hashData->blockNumber),
                    timestamp: 1,

                    raw_data: json_encode(['receipt' => $receipt, 'hashData' => $hashData]),

                    confirmed: true,
                    result: 'SUCCESS',

                    coin_address: null,
                    owner_address: $hashData->from,
                );
            }
        } catch (Exception $exception) {
            $msg = "parseHashDataFromJsonRPC error ::: " . $exception->getMessage();
            Log::error($msg);
//            dump($msg);
            throw new TransactionFailedException($msg);
        }
    }

//    /**
//     * @param array $hashData
//     * @return HashDataModel|null
//     */
//    private function parseHashDataFromErc20Transaction(array $hashData): ?HashDataModel
//    {
//        try {
//            $coin = $this->getCoinByAddress($hashData['contractAddress']);
//            if (!$coin)
//                throw new TransactionFailedException('Not allowed coin symbol ');
//
//            return new HashDataModel(
//                coin_network: CoinNetworkEnum::ERC20->name,
//                coin_symbol: $coin['symbol'],
//                coin_amount: floatval($hashData['value']) / pow(10, $coin['precision']),
//
//                from_address: $hashData['from'],
//                to_address: $hashData['to'],
//
//                method: 'transfer',
//
//                hash: $hashData['hash'],
//                block_number: intval($hashData['blockNumber']),
//                timestamp: intval($hashData['timeStamp']),
//
//                raw_data: json_encode(['hashData' => $hashData]),
//
//                confirmed: true,
//                result: 'SUCCESS',
//
//                coin_address: $coin['address'],
//                owner_address: $hashData['from'],
//            );
//        } catch (Exception $exception) {
//            $msg = "【{$hashData['hash']}】parseHashDataFromErc20Transaction error ::: " . $exception->getMessage();
//            Log::error($msg);
//            dump($msg);
//            return null;
//        }
//    }
//
//    /**
//     * @param array $hashData
//     * @return HashDataModel|null
//     */
//    private function parseHashDataFromNormalTransaction(array $hashData): ?HashDataModel
//    {
//        try {
//            // get coin
//            if ($hashData['methodId'] == '0x' && $hashData['functionName'] == "") {
//                $coin = $this->getCoinBySymbol('eth');
//            } else {
//                $coin = $this->getCoinByAddress($hashData['to']);
//            }
//            if (!$coin)
//                throw new TransactionFailedException('Not allowed coin symbol');
//
//            if ($coin['symbol'] == 'eth') {
//                $method = 'transfer ETH';
//                $toAddress = $hashData['to'];
//                $amount = floatval($hashData['value']) / pow(10, $coin['precision']);
//
//            } else {
//                list($method, $toAddress, $amount) = $this->parseInputData($hashData['input'], $coin);
//            }
//
//            return new HashDataModel(
//                coin_network: CoinNetworkEnum::ERC20->name,
//                coin_symbol: $coin['symbol'],
//                coin_amount: $amount,
//
//                from_address: $hashData['from'],
//                to_address: $toAddress,
//
//                method: $method,
//
//                hash: $hashData['hash'],
//                block_number: intval($hashData['blockNumber']),
//                timestamp: intval($hashData['timeStamp']),
//
//                raw_data: json_encode(['hashData' => $hashData]),
//
//                confirmed: true,
//                result: 'SUCCESS',
//
//                coin_address: $coin['address'],
//                owner_address: $hashData['from'],
//            );
//        } catch (Exception $exception) {
//            $msg = "【{$hashData['hash']}】parseHashDataFromNormalTransaction error ::: " . $exception->getMessage();
//            Log::error($msg);
//            dump($msg);
//            return null;
//        }
//    }

    /**
     * @param string $input
     * @param array $coin
     * @return array
     */
    private function parseInputData(string $input, array $coin = []): array
    {
        $method = substr($input, 0, 10);
        $methodName = match ($method) {
            '0xa9059cbb' => 'transfer',
            '0x095ea7b3' => 'approve',
//                    '0x23b872dd' => 'transferFrom',
            default => 'unknown',  //throw new TransactionFailedException("Unknown method $method")
        };
        $input = substr($input, 10);
        $args = str_split($input, 64);

        $toAddress = '';
        $amount = 0;

        if ($methodName == 'transfer') {
            $toAddress = '0x' . substr($args[0], strlen($args[0]) - 40);
            $amount = floatval(Utils::toBn('0x' . $args[1])->value) / pow(10, $coin['precision']);
        } else if ($methodName == 'approve') {
            $toAddress = '0x' . substr($args[0], strlen($args[0]) - 40);
            $amount = floatval(Utils::toBn('0x' . $args[1])->value) / pow(10, $coin['precision']);
        }

        return array($methodName, $toAddress, $amount);
    }

    public function GetTransactionsByAddress(string $address, ?int $start = null, ?bool $debug = false): array
    {
        // TODO: Implement GetTransactionsByAddress() method.
        return [];
    }
}
