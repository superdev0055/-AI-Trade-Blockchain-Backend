<?php

namespace Tests\Modules\Admin;;


use Tests\TestCase;

/**
 * @intro
 */
class AssetsControllerTest extends TestCase
{
    /**
     * @intro
     */
    public function testStakings()
    {
        $this->go(__METHOD__, [
            'user_address' => '0xAE1670bf8274e024af26A1648FD1eE3483864ae6', # 用户地址
        ]);
    }
    /**
     * @intro
     */
    public function testWithdrawable()
    {
        $this->go(__METHOD__, [
            'user_address' => '0xAE1670bf8274e024af26A1648FD1eE3483864ae6', # 用户地址
        ]);
    }
    /**
     * @intro
     */
    public function testPendings()
    {
        $this->go(__METHOD__, [

        ]);
    }
    /**
     * @intro 待审批列表
     */
    public function testWithdrawApproveList()
    {
        $this->go(__METHOD__, [

        ]);
    }
    /**
     * @intro 审批
     */
    public function testConfirmWithdraw()
    {
        $this->go(__METHOD__, [
            'id' => '', # id
            'approve' => '', # 是否审批通过
            'pending_withdrawal_type' => '', # 审批选是的时候必须填写：Automatic, Manual
            'hash' => '', # 审批是是，且方式选自动的时候，必须填写，手工发送的hash

        ]);
    }

}
