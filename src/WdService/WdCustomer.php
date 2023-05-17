<?php

namespace Maxsihong\WdService\WdService;

use Maxsihong\WdService\Kernel\HttpClient\Client;

class WdCustomer extends Client
{
    /**
     * 查询客户详情
     * @link https://open.weidian.com/#/api/1075
     * @param $buyerId
     * @return array
     * @author: 陈志洪
     * @since: 2023/5/17
     */
    public function queryCustomerInfo($buyerId)
    {
        return $this->api('vdian.crm.queryCustomerInfo', '2.0', ['buyerId' => intval($buyerId)]);
    }

    /**
     * 查询客户buyerid【注意该接口需要申请授权】
     * @link https://open.weidian.com/#/api/248
     * @param array $data
     * @param string $data['telephone'] 客户电话号码。【和unionId、[cardCode,cardID]查询三选一】
     * @param string $data['code'] 电话号码区号，如果是中国大陆号码则不用填；如果查询非中国大陆地区手机号，则必须传区号
     * @param string $data['unionId'] 微信私域unionId，【和电话号码、[cardCode,cardID]查询三选一】
     * @param string $data['cardCode'] 用户微信会员卡Code值，【和客户电话号码、unionId查询三选一】
     * @return array
     * @author: 陈志洪
     * @since: 2023/5/17
     */
    public function queryBuyerInfo(array $data)
    {
        $param = arrayListOnly($data, [
            'telephone', 'code', 'unionId', 'cardCode',
        ]);

        return $this->api('vdian.crm.queryBuyerInfo', '1.0', $param);
    }
}
