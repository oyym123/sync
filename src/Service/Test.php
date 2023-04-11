<?php


namespace AsyncCenter\Service;

use AsyncCenter\Base\BaseApplication;

class Test
{
    /**
     * 回调测试
     * @param $params
     */
    public function callbackTest($params)
    {
        $master = 'QUEUE_DCM_TCC';
        Utils::asyncSuccessLog(Utils::getQueueData($params, $master), $master);
    }

    public static function setTest()
    {
        (new BaseApplication())->run([
            'command' => 'index.php callbackTest',
            'action' => 'callbackTest',
            'argument_first' => '',
            'argument_second' => '',
            'msg' => '{"app_id":"","access_token":"","timestamp":"1680759274","system_source":"front_pos","sign":"MDBiYzgxNzFhZTA5ZTFmNGUyYzY3OWQ4NGEzMTU1NWJjNDBmOTQyNTdhNzc3MGY3ZjAyZTIyZWQxNzJlN2ZkMA==","login_uid":"","login_name":"","NOT_SAVE_MONGO_INFO":1,"account_number":"13411279614","distributor_id":"11","api_request_remark":"API1","import_type":3,"excel_file_name":"API-API1-20230410174140","uid":"19","account_name":"\u82b1\u5f00\u4e3a\u541b\u7f8e","upload_id":3788,"upload_key":"11_19_API_IMPORT_PRODUCT_20230410174140826","upload_error_key":"11_19_API_IMPORT_PRODUCT_20230410174140826_ERROR","queue_name":"dcm_queue_my_product_api_import","call_back_method":"callBackImportProduct","import_sources":"API","excel_data":[{"custom_spu":"B1000","custom_sku":"D00011132696","product_name":"xiaozi123","category":"\u7ed8\u753b\u5de5\u5177","attribute_name":"\u5c3a\u5bf8||\u989c\u8272||\u5f62\u72b6","attribute_value":"M||\u7ea2||\u65b9\u7684","product_main_photo":"https:\/\/gimg2.baidu.com\/image_search\/src=http%3A%2F%2Fwww.gpbctv.com%2Fuploads%2F20210424%2Fzip_1619246266UkP6CL.jpg&refer=http%3A%2F%2Fwww.gpbctv.com&app=2002&size=f9999,10000&q=a80&n=0&g=0n&fmt=auto?sec=1669983420&t=1bd2d53a8df3db495027dfaa2f1ee9f8","product_sub_photo":"https:\/\/gimg2.baidu.com\/image_search\/src=http%3A%2F%2Fwww.gpbctv.com%2Fuploads%2F20210424%2Fzip_1619246266UkP6CL.jpg&refer=http%3A%2F%2Fwww.gpbctv.com&app=2002&size=f9999,10000&q=a80&n=0&g=0n&fmt=auto?sec=1669983420&t=1bd2d53a8df3db495027dfaa2f1ee9f8","product_description":"2","business_model":"1","gross_wight":"10","net_weight":"","package_size":"1*2*3","product_size":"","declared_product_name":"xiaozi","declared_price":"10","currency":"CNY","customs_code":"","product_use":"\u5bb6\u7528","product_material":"\u5851\u6599","logistics_attr":"\u666e\u8d27\u7c7b","deliverable_warehouse":"\u82f1\u56fd\u4ed3\uff0c\u7f8e\u56fd\u4ed3","us_sales_link":"https:\/\/www.baidu.com\/","uk_sales_link":"https:\/\/www.baidu.com\/","jp_sales_link":"https:\/\/www.baidu.com\/","inspection_standard":"1","purchase_price":"1000","product_name_en":"gdf","key_word_cn":"fg,545,7889","key_word":"hgfh,789456","selling_point_cn":"dfsd,4544","selling_point":"gfgdfgd,54564","description":"sdfdsd"}],"is_start":1,"number":1,"chunk_count":1,"is_end":1}',
            'master_process_name' => 'QUEUE_DCM_TCC',
            'cleanRepeat' => 20,
            'logFlag' => 10,
            'isCount' => 10,
            'isQueue' => 10,
        ]);
    }

    public static function setTestHttp()
    {
        (new BaseApplication())->run([
            'command' => 'http://kf.com/api/help/add',
            'action' => 'callbackTest',
            'argument_first' => '',
            'argument_second' => '',
            'msg' => '{"initiator_user_id":"1","access_token":"","timestamp":"1680759274","system_source":"front_pos","sign":"MDBiYzgxNzFhZTA5ZTFmNGUyYzY3OWQ4NGEzMTU1NWJjNDBmOTQyNTdhNzc3MGY3ZjAyZTIyZWQxNzJlN2ZkMA==","login_uid":"","login_name":"","NOT_SAVE_MONGO_INFO":1,"account_number":"13411279614","distributor_id":"11","api_request_remark":"API1","import_type":3,"excel_file_name":"API-API1-20230410174140","uid":"19","account_name":"\u82b1\u5f00\u4e3a\u541b\u7f8e","upload_id":3788,"upload_key":"11_19_API_IMPORT_PRODUCT_20230410174140826","upload_error_key":"11_19_API_IMPORT_PRODUCT_20230410174140826_ERROR","queue_name":"dcm_queue_my_product_api_import","call_back_method":"callBackImportProduct","import_sources":"API","excel_data":[{"custom_spu":"B1000","custom_sku":"D00011132696","product_name":"xiaozi123","category":"\u7ed8\u753b\u5de5\u5177","attribute_name":"\u5c3a\u5bf8||\u989c\u8272||\u5f62\u72b6","attribute_value":"M||\u7ea2||\u65b9\u7684","product_main_photo":"https:\/\/gimg2.baidu.com\/image_search\/src=http%3A%2F%2Fwww.gpbctv.com%2Fuploads%2F20210424%2Fzip_1619246266UkP6CL.jpg&refer=http%3A%2F%2Fwww.gpbctv.com&app=2002&size=f9999,10000&q=a80&n=0&g=0n&fmt=auto?sec=1669983420&t=1bd2d53a8df3db495027dfaa2f1ee9f8","product_sub_photo":"https:\/\/gimg2.baidu.com\/image_search\/src=http%3A%2F%2Fwww.gpbctv.com%2Fuploads%2F20210424%2Fzip_1619246266UkP6CL.jpg&refer=http%3A%2F%2Fwww.gpbctv.com&app=2002&size=f9999,10000&q=a80&n=0&g=0n&fmt=auto?sec=1669983420&t=1bd2d53a8df3db495027dfaa2f1ee9f8","product_description":"2","business_model":"1","gross_wight":"10","net_weight":"","package_size":"1*2*3","product_size":"","declared_product_name":"xiaozi","declared_price":"10","currency":"CNY","customs_code":"","product_use":"\u5bb6\u7528","product_material":"\u5851\u6599","logistics_attr":"\u666e\u8d27\u7c7b","deliverable_warehouse":"\u82f1\u56fd\u4ed3\uff0c\u7f8e\u56fd\u4ed3","us_sales_link":"https:\/\/www.baidu.com\/","uk_sales_link":"https:\/\/www.baidu.com\/","jp_sales_link":"https:\/\/www.baidu.com\/","inspection_standard":"1","purchase_price":"1000","product_name_en":"gdf","key_word_cn":"fg,545,7889","key_word":"hgfh,789456","selling_point_cn":"dfsd,4544","selling_point":"gfgdfgd,54564","description":"sdfdsd"}],"is_start":1,"number":1,"chunk_count":1,"is_end":1}',
            'master_process_name' => 'QUEUE_DCM_TCC',
            'cleanRepeat' => 20,
            'logFlag' => 10,
            'isCount' => 10,
            'isQueue' => 10,
        ]);
    }
}