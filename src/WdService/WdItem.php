<?php

namespace Maxsihong\WdService\WdService;

use Maxsihong\WdService\Kernel\HttpClient\Client;

class WdItem extends Client
{
    protected array $item_data = [];

    protected function initItemData($item): array
    {
        $itemDeliveryList = [4]; // 商品配送方式,4-快递发货;8-同城配送;16-到店自提
        if (isset($item['itemDeliveryList']) && !empty($item['itemDeliveryList'])) {
            $itemDeliveryList = $item['itemDeliveryList'];
        }

        $item_data = [
            "itemName" => $item['itemName'], // 商品标题
            "price" => $item['price'], // 商品价格
            "stock" => $item['stock'] ?? 0, // 无sku场景下的库存数量
            "imgs" => $item['imgs'], // 主图列表
            "itemDetails" => $item['itemDetails'], // 商品详情

            "attrList" => $item['attrList'] ?? [], // 规格信息列表，如果商品有sku，attrList为必传参数；如果商品没有sku，attrList可不传
            "cateIds" => $item['cateIds'] ?? '', // 商品分类id，逗号,分隔
            "createTempAttr" => $item['createTempAttr'] ?? false, // 是否上传临时型号，true表示是，false表示否。如果传true,attrId、attrTitle、attrValue 必传且允许自定义，同一个商品attrId不允许重复
            "crossedPrice" => $item['crossedPrice'] ?? 0, // 划线价
            "expressFeeTemplateId" => $item['expressFeeTemplateId'] ?? 0, // 运费模板id
            "minPrice" => $item['minPrice'] ?? 0, // 最低售价（连锁店业务）
            "maxPrice" => $item['maxPrice'] ?? 0, // 最高售价（连锁店业务）
            "costPrice" => $item['costPrice'] ?? 0, // 成本价 日历类型和充值中心商品不支持
            "freeDelivery" => $item['freeDelivery'] ?? false, // 是否包邮,true-包邮；false-不包邮
            "itemComment" => $item['itemComment'] ?? '', // 商品分享文案
            "itemDeliveryList" => $itemDeliveryList, // 商品配送方式,4-快递发货;8-同城配送;16-到店自提
            "status" => $item['status'] ?? 2, // 商品状态,1-上架，2-下架;不传默认上架
            "merchantCode" => $item['merchantCode'] ?? '', // 商家自定义商品编码
            "remoteFreeDelivery" => $item['remoteFreeDelivery'] ?? false, // 偏远地区是否包邮,true-包邮；false-不包邮
            "shopPropertyDOS" => $item['shopPropertyDOS'] ?? [], // 商品设置属性信息
            "skus" => $item['skus'] ?? [], // 商品型号列表,商品最多只能有500个sku。 如果传了attrList，必须传skus；如果没有attrList 则skus可不传
            "itemBizType" => 1,
        ];

        // 发货时间
        if (isset($item['deliveryTime']) && !empty($item['deliveryTime'])) {
            $item_data['deliveryTime'] = [
                'deliveryAtAfterPayBefore' => $item['deliveryTime']['deliveryAtAfterPayBefore'] ?? 0, // 0-当日，1-次日，结合deliveryTimeType为1时使用，x点前付款，付款后xx天发货
                'deliveryTimeType' => $item['deliveryTime']['deliveryTimeType'] ?? 0, // 发货时间选择类型，0-x点前付款，付款后xx天发货；1-现在付款，预计当日/次日发货
                'payBefore' => $item['deliveryTime']['payBefore'] ?? 0, // 结合deliveryTimeType为0时使用，x点前付款
                'afterPayDeliveryAt' => $item['deliveryTime']['afterPayDeliveryAt'] ?? 0, // 结合deliveryTimeType为0时使用，付款后xx天发货；枚举参数：8小时、16小时、1天、2天、3天、4天、5天、6天、7天、15天、30天、45天，3600000*8=8小时，其余枚举传参以此类推
            ];
        }

        return $item_data;
    }

    /**
     * 添加商品
     * @link https://open.weidian.com/#/api/231
     * @param array $item
     * @return array
     * @since: 2023/5/18
     * @author: 陈志洪
     */
    public function add(array $item): array
    {
        $item_data = $this->initItemData($item);

        return $this->api('vdian.item.add', '1.5', $item_data);
    }

    /**
     * 更新库存
     * @link https://open.weidian.com/#/api/108
     * @param array $items 需要更新的商品和库存信息
     * @return array
     * @since: 2023/5/18
     * @author: 陈志洪
     */
    public function stockUpdate(array $items)
    {
        $param = [
            'type' => 'set',
            'items' => $items,
        ];

        return $this->api('vdian.item.stock.update', '1.0', $param);
    }

    /**
     * 上下架商品
     * @link https://open.weidian.com/#/api/52
     * @param int $itemid 商品id
     * @param int $opt 1表示商品上架，2表示商品下架
     * @return array
     * @since: 2023/5/18
     * @author: 陈志洪
     */
    public function onSale(int $itemid, int $opt)
    {
        return $this->api('weidian.item.onSale', '1.0', compact('itemid', 'opt'));
    }

    /**
     * 获取全店商品分类
     * @link https://open.weidian.com/#/api/94
     * @param int $showNoCate 是否显示“未分类”(0代表不显示，1代表显示默认的情况为0)
     * @return array
     * @since: 2023/5/18
     * @author: 陈志洪
     */
    public function cateGetList(int $showNoCate = 0)
    {
        return $this->api('weidian.cate.get.list', '1.0', compact('showNoCate'));
    }

    /**
     * 获取型号属性列表
     * @link https://open.weidian.com/#/api/110
     * @return array
     * @since: 2023/5/18
     * @author: 陈志洪
     */
    public function skuAttrsGet()
    {
        return $this->api('vdian.sku.attrs.get', '1.0');
    }

    /**
     * 添加商品型号
     * @link https://open.weidian.com/?shopId=1642388722#/api/43
     * @param int $itemid 商品id
     * @param array $skus 商品sku，无型号的商品skus为空
     * @param array $attr_list 属性数组
     * @return array
     * @since: 2023/5/18
     * @author: 陈志洪
     */
    public function itemSkuAdd(int $itemid, array $skus, array $attr_list = []): array
    {
        return $this->api('vdian.item.sku.add', '1.1', compact('skus', 'itemid', 'attr_list'));
    }

    /**
     * 添加型号属性
     * @link https://open.weidian.com/#/api/111
     * @param array $attr_list
     * @param string $attr_list []['attr_title'] 属性名
     * @param array $attr_list []['attr_value'][]['attr_value'] 属性值数组，最多20个，注意：attr_value不允许重复
     * @return array
     * @since: 2023/5/18
     * @author: 陈志洪
     */
    public function skuAttrsAdd(array $attr_list): array
    {
        return $this->api('vdian.sku.attr.add', '1.0', compact('attr_list'));
    }

    /**
     * 获取单个商品
     * @link https://open.weidian.com/?shopId=1642388722#/api/117
     * @param int $itemid 商品ID
     * @return array
     * @since: 2023/5/18
     * @author: 陈志洪
     */
    public function itemGet(int $itemid): array
    {
        return $this->api('vdian.item.get', '1.0', compact('itemid'));
    }

    /**
     * 获取商品集合
     * @link https://open.weidian.com/?shopId=1642388722#/api/178
     * @param int $page_num 返回页码(默认值：1)，从1开始，最大400
     * @param int $page_size 单页条数，默认值30，最大50条
     * @param int $status status=1或不传为在架商品(不包含供货商货源)，status=2为下架商品,4表示下架和在架商品,10供货商货源
     * @param int $orderby 排序方式(默认值1)：1.推荐商品排前面、添加时间降序，2.只查询已售罄商品、按商品添加时间降序，3.按商品销量降序 ，4.按商品销量升序， 5.按商品库存降序 ，6. 按商品库存升序，7. 推荐商品排前面、添加时间升序
     * @param string $update_start 商品更新时间段的开始时间，如：2014-11-12 16:36:08 精确到秒
     * @param string $update_end 商品更新时间段的结束时间，如：2014-11-1216:36:08精确到秒
     * @return array
     * @since: 2023/5/18
     * @author: 陈志洪
     */
    public function itemListGet(int $page_num = 1, int $page_size = 15, int $status = 4, int $orderby = 1, string $update_start = '', string $update_end = ''): array
    {
        $data = compact('page_num', 'page_size', 'status', 'orderby');

        if (!empty($update_start) && !empty($update_end)) {
            $data['update_start'] = $update_start;
            $data['update_end'] = $update_end;
        }

        return $this->api('vdian.item.list.get', '1.1', $data);
    }

    /**
     * 获取多个商品
     * @link https://open.weidian.com/?shopId=1642388722#/api/173
     * @param string $ids 商品ID，多个商品ID以逗号隔开，最多支持100个商品id
     * @param int $need_idno 是否返回清关标志，1是，0否
     * @return array
     * @since: 2023/5/18
     * @author: 陈志洪
     */
    public function getItems(string $ids, int $need_idno = 1): array
    {
        return $this->api('weidian.get.items', '1.1', compact('ids', 'need_idno'));
    }

    /**
     * 更新商品信息
     * @link https://open.weidian.com/?shopId=1642388722#/api/232
     * @param array $item 商品信息,从微店中查询出来的
     * @return array
     * @author: 陈志洪
     * @since: 2023/5/18
     */
    public function update(array $item): array
    {
        return $this->api('vdian.item.update', '1.5', $item);
    }

    /**
     * 店铺内商品搜索
     * @link https://open.weidian.com/?shopId=1642388722#/api/119
     * @param string $keyWord 搜索关键词
     * @param int $fx 结果是否包含分销商品：1包含0不包含，默认是1
     * @param int $page 第几页
     * @param int $pageSize 单页条数，最大50条，建议30条以下
     * @param int $selfCreate 是否自建商品，不包含分销商品、挑货商品、连锁同步商品，fx字段无效：：1是0不是，默认是0
     * @return array
     * @author: 陈志洪
     * @since: 2023/5/18
     */
    public function shopItemSearch(string $keyWord, int $fx = 1, int $page = 1, int $pageSize = 15, int $selfCreate = 0): array
    {
        return $this->api('weidian.item.search', '1.0', compact(
            'keyWord', 'fx', 'page', 'pageSize', 'selfCreate'
        ));
    }
}
