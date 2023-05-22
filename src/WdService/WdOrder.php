<?php

declare(strict_types=1);

namespace Maxsihong\WdService\WdService;

use Maxsihong\WdService\Kernel\HttpClient\Client;

class WdOrder extends Client
{
    /**
     * 电子卡券-核销码查询
     * @link https://open.weidian.com/#/api/1069
     * @param string $ecode 核销码
     * @return array
     * @author: 陈志洪
     * @since: 2023/5/19
     */
    public function ecodeQuery(string $ecode): array
    {
        return $this->api('vdian.order.ecode.query', '2.0', compact('ecode'));
    }

    /**
     * 获取订单列表
     * @link https://open.weidian.com/#/api/1065
     * @param array $data
     * @param int $data['page_num'] 订单翻页，初始页为1
     * @param int $data['page_size'] 单页条数，最大50条，建议30条以下。如果不传此参数，默认值：50
     * @param string $data['order_type'] 订单类型，unship：待发货；unpay：待付款；shiped：已发货；refunding：退款中；finish：已完成；close：已关闭；all：全部类型。不传或是传空值，查询全部类型
     * @param string $data['add_start'] 订单创建时间段的开始时间，如:2014-11-12 16:36:08精确到秒    如果add_start和add_end任意一个参数未传，且update_start也未传，默认查询近31天内的订单
     * @param string $data['add_end'] 订单创建时间段的结束时间，如：2014-11-12 16:36:08 精确到秒
     * @param string $data['update_start'] 订单更新时间段的开始时间，如：2014-11-12 16:36:08 精确到秒    如果add_start和add_end任意一个参数未传，且update_start也未传，默认查询近31天内的订单
     * @param string $data['update_end'] 订单更新时间段的结束时间，如：2014-11-12 16:36:08精确到秒
     * @param string $data['group_type'] 1不包含微团购未成团订单，0包含，默认是1
     * @param int $data['order_biz_type'] 默认为空查询全部，过滤业务类型，如14-跨境订单
     * @return array
     * @since: 2023/5/17
     * @author: 陈志洪
     */
    public function list(array $data = []): array
    {
        $param = arrayListOnly($data, [
            'page_num', 'order_type', 'page_size', 'update_end', 'add_start','add_end', 'update_start',
            'group_type', 'order_biz_type',
        ]);

        return $this->api('vdian.order.list.get', '1.5', $param);
    }

    /**
     * 获取卖家自提地址
     * @link https://open.weidian.com/#/api/319
     * @param int $page_num 页码,不传默认为0
     * @param int $page_size 每页条数
     * @param int $type 地址类型, 自提地址-2
     * @return array
     * @author: 陈志洪
     * @since: 2023/5/19
     */
    public function sellerAddressListGet(int $page_num = 1, int $page_size = 15, int $type = 2): array
    {
        return $this->api('vdian.seller.address.list.get', '1.0', compact(
            'page_num', 'page_size', 'type'
        ));
    }

    /**
     * 跨境支付查询报关
     * @link https://open.weidian.com/#/api/306
     * @param string $order_id 订单ID
     * @return array
     * @author: 陈志洪
     * @since: 2023/5/19
     */
    public function crossBorderDeclareCustomsPaymentQuery(string $order_id): array
    {
        return $this->api('vdian.crossborder.declarecustoms.paymentquery', '1.0', compact('order_id'));
    }

    /**
     * 跨境支付申请报关
     * @link https://open.weidian.com/#/api/305
     * @param string $order_id 订单ID
     * @return array
     * @author: 陈志洪
     * @since: 2023/5/19
     */
    public function crossBorderDeclareCustomsPaymentReport(string $order_id): array
    {
        return $this->api('vdian.crossborder.declarecustoms.paymentreport', '1.0', compact('order_id'));
    }

    /**
     * 获取跨境口岸列表
     * @link https://open.weidian.com/#/api/304
     * @return array
     * @since: 2023/5/19
     * @author: 陈志洪
     */
    public function crossBorderElectronicPortQuery(): array
    {
        return $this->api('vdian.crossborder.electronicport.query');
    }

    /**
     * 获取订单id列表
     * @link https://open.weidian.com/#/api/128
     * @param array $data
     * @param string $data['order_date'] 订单日期 yyyy-MM-dd 格式
     * @param int $data['group_type'] 1不包含微团购未成团订单，0包含，默认是1
     * @param string $data['isweicenter'] 是否是微中心订单（0：全部订单，1微中心订单，2普通订单，默认是全部订单）
     * @param int $data['page_num'] 返回页码，从1开始，默认1
     * @param int $data['page_size'] 单页条数，默认值20，最大50条
     * @return array
     * @author: 陈志洪
     * @since: 2023/5/19
     */
    public function orderIdsGet(array $data): array
    {
        $data = arrayListOnly($data, [
            'order_date', 'isweicenter', 'page_num', 'page_size', 'group_type'
        ]);

        return $this->api('vdian.order.ids.get', '1.1', $data);
    }

    /**
     * 获取订单详情
     * @link https://open.weidian.com/#/api/1064
     * @param string $order_id 订单号
     * @return array
     * @since: 2023/5/17
     * @author: 陈志洪
     */
    public function show(string $order_id): array
    {
        return $this->api('vdian.order.get', '2.0', compact('order_id'));
    }

    /**
     * 获取快递列表
     * @link https://open.weidian.com/#/api/157
     * @return array
     * @since: 2023/5/17
     * @author: 陈志洪
     */
    public function expressList(): array
    {
        return $this->api('vdian.order.expresslist');
    }

    /**
     * 到店自提-核销码查询
     * @link https://open.weidian.com/#/api/1071
     * @param string $code 核销码
     * @return array
     * @author: 陈志洪
     * @since: 2023/5/20
     */
    public function codeQuery(string $code): array
    {
        return $this->api('vdian.order.code.query', '2.0', compact('code'));
    }

    /**
     * @desc: 订单发货
     * @link https://open.weidian.com/?shopId=1642388722#/api/57
     * @param string $order_id 订单ID
     * @param string $express_no 快递单号
     * @param string $express_type 快递公司编号
     * @param string $express_custom 自定义快递
     * @param array $eCodeList 卡券码，旅游门票类订单必传，非门票订单不传。旅游门票订单发货时，只需传order_id、eCodeList即可。如果订单内商品数量n件，eCode传n个
     * @param array $itemUpdates 商品更新数组
     * @return array|string
     * @since: 2023/5/17
     * @author: 陈志洪
     */
    public function deliver(string $order_id, string $express_no, string $express_type, string $express_custom = '', array $eCodeList = [], array $itemUpdates = [])
    {
        return $this->api('vdian.order.deliver', '1.0', compact(
            'order_id', 'express_no', 'express_type', 'express_custom', 'eCodeList', 'itemUpdates'
        ));
    }

    /**
     * 到店自提-核销码核销
     * @link https://open.weidian.com/?shopId=1642388722#/api/116
     * @param string $code 核销码
     * @param array $update_item 商品更新数组
     * @param string $update_item[]['itemId'] 商品id
     * @param string $update_item[]['skuId'] sku ID
     * @param string $update_item[]['weight'] 发货重量, 缺省值 -1
     * @return array
     * @author: 陈志洪
     * @since: 2023/5/21
     */
    public function codeVerify(string $code, array $update_item = []): array
    {
        $param = [
            'PickingCode' => $code
        ];
        if (!empty($update_item)) {
            $param['itemUpdates'] = $update_item;
        }

        return $this->api('vdian.order.code.verify', '1.0', $param);
    }

    /**
     * @desc: 订单部分发货
     * @link https://open.weidian.com/?shopId=1642388722#/api/113
     * @param string $order_id 订单ID
     * @param string $express_no 快递单号
     * @param string $express_type 快递公司编号
     * @param string $express_custom 自定义快递
     * @param array $item 如果通过商品进行发货，则必传item_id(item_sku_id)字段;如果通过子订单号发货，则sub_order_id必传
     * @param string $item[]['item_id'] 商品id，对应订单详情接口的item_id(不要传cur_shop_item_id)
     * @param string $item[]['item_sku_id sku'] ID,对应订单详情接口的sku_id(不要传cur_shop_sku_id)
     * @param string $item[]['sub_order_id'] 子订单号
     * @param array $itemUpdates 商品更新数组
     * @return array|string
     * @since: 2023/5/17
     * @author: 陈志洪
     */
    public function splitDeliver(string $order_id, string $express_no, string $express_type, string $express_custom, array $item, array $itemUpdates = [])
    {
        return $this->api('vdian.order.deliver.split', '1.0', compact(
            'order_id', 'express_no', 'express_type', 'express_custom', 'item', 'itemUpdates'
        ));
    }

    /**
     * 电子卡券-核销码核销
     * @link https://open.weidian.com/#/api/1010
     * @param string $orderId 订单号id
     * @param array $ecodes 电子卡券-核销码查询 接口返回的code
     * @return array
     * @since: 2023/5/22
     * @author: 陈志洪
     */
    public function ecodeVerify(string $orderId, array $ecodes): array
    {
        return $this->api('vdian.order.ecode.verify', '1.0', compact('orderId', 'ecodes'));
    }

    /**
     * @desc: 子单拆单发货
     * @link https://open.weidian.com/?shopId=1642388722#/api/211
     * @param string $order_id 订单号
     * @param array $spilt_item_list 如果通过商品进行子单拆单发货，则item_id(sku_id)必传；如果通过子订单号进行发货，则subOrderId必传
     * @param array $itemUpdates 商品更新数组
     * @param string -sku_id sku ID，1.商品如果没有sku,传0；2.对应订单详情接口的sku_id(不要传cur_shop_sku_id)
     * @param string -item_id 商品id，对应订单详情接口的item_id(不要传cur_shop_item_id)
     * @param int -count 发货商品数量
     * @param string -express_type 物流公司type
     * @param string -express_custom 物流公司名称
     * @param string -express_no 物流单号
     * @param string -subOrderId 子订单号
     * @return array|string
     * @since: 2023/5/17
     * @author: 陈志洪
     */
    public function deliverSplitSub(string $order_id, array $spilt_item_list, array $itemUpdates = [])
    {
        return $this->api('vdian.order.deliver.split.sub', '1.0', compact(
            'order_id', 'spilt_item_list', 'itemUpdates'
        ));
    }

    /**
     * 延长确认收货时间
     * @link https://open.weidian.com/#/api/62
     * @param string $order_id
     * @param int $delay_time
     * @return array|string
     * @since: 2023/5/22
     * @author: 陈志洪
     */
    public function acceptDelay(string $order_id, int $delay_time)
    {
        return $this->api('vdian.order.accept.delay', '1.0', compact('order_id', 'delay_time'));
    }

    /**
     * 修改物流信息
     * @link https://open.weidian.com/#/api/58
     * @param string $order_id 订单ID，说明:订单完成后不可修改
     * @param string $express_no 快递单号，如果只需修改单号，可只传express_no，不传express_type
     * @param int $express_type 快递公司编号
     * @param string $deliver_id 发货批次编号，代表一次发货（订单详情接口获取）
     * @param string $express_custom 自定义快递，特殊可选
     * @return array|string
     * @author: 陈志洪
     * @since: 2023/5/22
     */
    public function expressModify(string $order_id, string $express_no, int $express_type = 0, string $deliver_id = '', string $express_custom = '')
    {
        $param = arrayListOnly(
            compact(
                'order_id', 'express_no', 'express_type', 'deliver_id', 'express_custom'
            ),
            ['order_id', 'express_no', 'express_type', 'deliver_id', 'express_custom'],
            true
        );

        return $this->api('vdian.order.express.modify', '1.0', $param);
    }

    /**
     * 修改订单价格
     * @link https://open.weidian.com/#/api/59
     * @param string $order_id 订单ID
     * @param string $total_items_price 修改订单的商品总价
     * @param string $express_price 修改订单运费价格
     * @return array|string
     * @author: 陈志洪
     * @since: 2023/5/22
     */
    public function modify(string $order_id, string $total_items_price, string $express_price)
    {
        return $this->api('vdian.order.modify', '1.0', compact(
            'order_id', 'total_items_price', 'express_price'
        ));
    }
    
    /**
     * 逆向-商家发起退款【需用户主动同意或拒绝，若7天用户未处理则订单会自动同意且退款】
     * @link https://open.weidian.com/#/api/300
     * @param string $order_id 订单号
     * @param int $reasonId 退款原因，1 表示：缺货；2 表示：协商一致退款；3 表示其他原因
     * @param string $refundItemFee 退款商品费用
     * @param string $refundExpressFee 退款运费，没有运费或是运费不退，填0.00
     * @param string $refundDesc 退款描述
     * @param array $refundSubOrderIdList 子订单号，可通过订单详情接口获取，订单详情中对应的字段是sub_order_id
     * @return array
     * @since: 2023/5/17
     * @author: 陈志洪
     * 备注：
    1. 一次只能退订单的一个商品！
    2. 目前只允许商品退，不允许整单退款，即使一个订单只有一种商品，退款时也要写子订单号！
     */
    public function sellerCreateRefund(string $order_id, int $reasonId, string $refundItemFee, string $refundExpressFee, string $refundDesc, array $refundSubOrderIdList): array
    {
        return $this->api('open.sellerCreateRefund', '1.0', compact(
            'order_id', 'reasonId', 'refundItemFee', 'refundExpressFee', 'refundDesc', 'refundSubOrderIdList'
        ));
    }
}
