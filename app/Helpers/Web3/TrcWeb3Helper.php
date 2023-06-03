<?php

namespace App\Helpers\Web3;

use App\Enums\CoinNetworkEnum;
use App\Helpers\Web3\Exceptions\TransactionFailedException;
use App\NewServices\ConfigsServices;
use Exception;
use IEXBase\TronAPI\Exception\TRC20Exception;
use IEXBase\TronAPI\Exception\TronException;
use IEXBase\TronAPI\Provider\HttpProvider;
use IEXBase\TronAPI\Tron;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use LaravelCommon\App\Exceptions\Err;
use LaravelCommon\App\Helpers\CommonHelper;

class TrcWeb3Helper extends BaseWeb3Helper implements IWeb3Helper
{
    private Tron $tron;

    /**
     * @throws TronException
     */
    public function __construct()
    {
        $fullNode = new HttpProvider('https://api.trongrid.io');
        $solidityNode = new HttpProvider('https://api.trongrid.io');
        $eventServer = new HttpProvider('https://api.trongrid.io');

        $this->tron = new Tron($fullNode, $solidityNode, $eventServer);
    }

    /**
     * @param string $toAddress
     * @param float $amount
     * @param string|null $fromAddress
     * @param string|null $fromPrivateKey
     * @param bool $debug
     * @return mixed
     * @throws Err
     * @throws TronException
     */
    public function Send(string $toAddress, float $amount, ?string $fromAddress = null, ?string $fromPrivateKey = null, bool $debug = false): mixed
    {
        $config = ConfigsServices::Get('address');

        if (!$fromAddress)
            $fromAddress = $config['send']; // Web3Wallets::getTrcSendTokenWallet()->address;
        if (!$fromPrivateKey)
            $fromPrivateKey = $config['send']; // Web3Wallets::getTrcSendTokenWallet()->privateKey;

        $this->tron->setAddress($fromAddress);
        $this->tron->setPrivateKey($fromPrivateKey);

        $res = $this->tron->send($toAddress, $amount);
        if ($debug)
            dump(json_encode($res));

        if (!$res['result'])
            Err::Throw(__("Send TRX failed"));

        return $res['txid'];
    }

    /**
     * @param string $contractAddress
     * @param string $toAddress
     * @param float $amount
     * @param string|null $fromAddress
     * @param string|null $fromPrivateKey
     * @param bool $debug
     * @return mixed
     * @throws Err
     * @throws TRC20Exception
     * @throws TronException
     */
    public function SendToken(string $contractAddress, string $toAddress, float $amount, ?string $fromAddress = null, ?string $fromPrivateKey = null, bool $debug = false): mixed
    {
        $config = ConfigsServices::Get('address');

        if (!$fromAddress)
            $fromAddress = $config['send']; // Web3Wallets::getTrcSendTokenWallet()->address;
        if (!$fromPrivateKey)
            $fromPrivateKey = $config['send']; // Web3Wallets::getTrcSendTokenWallet()->privateKey;

        $this->tron->setAddress($fromAddress);
        $this->tron->setPrivateKey($fromPrivateKey);

        $contract = $this->tron->contract($contractAddress);
        $contract->setFeeLimit(100);

        $res = $contract->transfer($toAddress, $amount);
        if ($debug)
            dump(json_encode($res));

        if (!$res['result'])
            Err::Throw(__("Send TRX failed"));

        return $res['txid'];
    }

    /**
     * @param string $hash
     * @param bool $debug
     * @return HashDataModel|null
     * @throws TransactionFailedException
     */
    public function GetTransactionByHash(string $hash, bool $debug = false): ?HashDataModel
    {
        $res = Http::withOptions(['proxy' => CommonHelper::GetProxies()])->get("https://apilist.tronscan.org/api/transaction-info?hash=$hash")->json();
        if ($debug)
            dump(json_encode($res));

        return $this->parseHashData($res);
    }

    /**
     * @param string $address
     * @param int|null $start
     * @param bool|null $debug
     * @return array
     */
    public function GetTransactionsByAddress(string $address, ?int $start = null, ?bool $debug = false): array
    {
        $params = [
            'sort' => '-timestamp', // @param sort: define the sequence of the records return;
            'limit' => 50, // @param limit: page size for pagination;
            'start' => 0, // @param start: query index for pagination;
            'count' => true, // @param count: total number of records;
//                'start_timestamp' => 0, // @param start_timestamp: query date range;
//                'end_timestamp' => 0, // @param end_timestamp: query date range;
            'address' => $address, // @param address: an account;
        ];
        if ($start)
            $params['start_timestamp'] = $start;

        $res = Http::withOptions(['proxy' => CommonHelper::GetProxies()])->get("https://apilist.tronscan.org/api/transaction", $params)->json();
        if ($debug)
            dump(json_encode($res));

        $result = [];
        foreach ($res['data'] as $item) {
            try {
                $data = $this->parseHashData($item);
                if ($data)
                    $result[] = $data;
            } catch (Exception $exception) {
                Log::error($exception->getMessage());
            }
        }
        return $result;
    }

    /**
     * @param mixed $res
     * @return HashDataModel|null
     * @throws TransactionFailedException
     */
    private function parseHashData(mixed $res): ?HashDataModel
    {
        try {
            if ($res['contractRet'] != 'SUCCESS')
                throw new TransactionFailedException("web3交易失败");

            if (isset($res['trigger_info']['parameter'])) {
                // TRC20 Token

                $parameter = $res['trigger_info']['parameter'];
                if (isset($parameter['to'])) {
                    $value = $parameter['value'];
                    $to = $parameter['to'];
                } elseif (isset($parameter['_to'])) {
                    $value = $parameter['_value'];
                    $to = $parameter['_to'];
                } else {
                    throw new TransactionFailedException("识别交易数据失败，请联系管理员");
                }

                $decimal = isset($res['tokenInfo']) ? $res['tokenInfo']['tokenDecimal'] : $res['tokenTransferInfo']['decimals'];
                return new HashDataModel(
                    coin_network: CoinNetworkEnum::TRC20->name,
                    coin_symbol: $this->getCoinByAddress($res['contractData']['contract_address'])['symbol'],
                    coin_amount: (float)(intval($value) / pow(10, intval($decimal))),

                    from_address: $res['ownerAddress'],
                    to_address: $to,

                    method: $this->getMethod($res['trigger_info']['method']),

                    hash: $res['hash'],
                    block_number: $res['block'],
                    timestamp: $res['timestamp'],

                    raw_data: json_encode($res),

                    confirmed: $res['confirmed'],
                    result: $res['contractRet'],

                    coin_address: $res['contractData']['contract_address'],
                    owner_address: $res['ownerAddress'],
                );
            } else {
                // TRX
                return new HashDataModel(
                    coin_network: CoinNetworkEnum::TRC20->name,
                    coin_symbol: 'TRX',
                    coin_amount: (float)$res['contractData']['amount'] / pow(10, 6),

                    from_address: $res['contractData']['owner_address'],
                    to_address: $res['contractData']['to_address'],

                    method: 'TRX Transfer',

                    hash: $res['hash'],
                    block_number: $res['block'],
                    timestamp: $res['timestamp'],

                    raw_data: json_encode($res),

                    confirmed: $res['confirmed'],
                    result: $res['contractRet'],

                    coin_address: null,
                    owner_address: $res['contractData']['owner_address'],
                );
            }
        } catch (Exception $exception) {
            dump($res);
            Log::error("Parse hash data error ::: " . $exception->getMessage());
//            dump($exception->getMessage(), $res);
            throw $exception;
        }
    }

    /**
     * @param mixed $method
     * @return string
     */
    private function getMethod(mixed $method): string
    {
        return substr($method, 0, strpos($method, '('));
    }
}
