<?php

namespace App\Helpers\Web3;

class Web3Wallets
{
    private static ?Web3WalletModel $ercReceiveTokenWallet = null;
    private static ?Web3WalletModel $ercSendTokenWallet = null;
    private static ?Web3WalletModel $ercApproveToWallet = null;

    private static ?Web3WalletModel $trcReceiveTokenWallet = null;
    private static ?Web3WalletModel $trcSendTokenWallet = null;
    private static ?Web3WalletModel $trcApproveToWallet = null;

    /**
     * @return Web3WalletModel
     */
    public static function getErcReceiveTokenWallet(): Web3WalletModel
    {
        if (!self::$ercReceiveTokenWallet) {
            self::$ercReceiveTokenWallet = new Web3WalletModel(config('web3.erc20.receiveTokenWallet.address'), config('web3.erc20.receiveTokenWallet.privateKey'));
        }
        return self::$ercReceiveTokenWallet;
    }

    /**
     * @return Web3WalletModel
     */
    public static function getErcSendTokenWallet(): Web3WalletModel
    {
        if (!self::$ercSendTokenWallet) {
            self::$ercSendTokenWallet = new Web3WalletModel(config('web3.erc20.sendTokenWallet.address'), config('web3.erc20.sendTokenWallet.privateKey'));
        }
        return self::$ercSendTokenWallet;
    }

    /**
     * @return Web3WalletModel
     */
    public static function getErcApproveToWallet(): Web3WalletModel
    {
        if (!self::$ercApproveToWallet) {
            self::$ercApproveToWallet = new Web3WalletModel(config('web3.erc20.approveToWallet.address'), config('web3.erc20.approveToWallet.privateKey'));
        }
        return self::$ercApproveToWallet;
    }

    /**
     * @return Web3WalletModel
     */
    public static function getTrcReceiveTokenWallet(): Web3WalletModel
    {
        if (!self::$trcReceiveTokenWallet) {
            self::$trcReceiveTokenWallet = new Web3WalletModel(config('web3.trc20.receiveTokenWallet.address'), config('web3.trc20.receiveTokenWallet.privateKey'));
        }
        return self::$trcReceiveTokenWallet;
    }

    /**
     * @return Web3WalletModel
     */
    public static function getTrcSendTokenWallet(): Web3WalletModel
    {
        if (!self::$trcSendTokenWallet) {
            self::$trcSendTokenWallet = new Web3WalletModel(config('web3.trc20.sendTokenWallet.address'), config('web3.trc20.sendTokenWallet.privateKey'));
        }
        return self::$trcSendTokenWallet;
    }

    /**
     * @return Web3WalletModel
     */
    public static function getTrcApproveToWallet(): Web3WalletModel
    {
        if (!self::$trcApproveToWallet) {
            self::$trcApproveToWallet = new Web3WalletModel(config('web3.trc20.approveToWallet.address'), config('web3.trc20.approveToWallet.privateKey'));
        }
        return self::$trcApproveToWallet;
    }
}
