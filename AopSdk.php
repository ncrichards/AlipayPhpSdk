<?php
/**
 * AOP SDK 入口文件
 * 请不要修改这个文件，除非你知道怎样修改以及怎样恢复
 * @author wuxiao
 */

namespace AlipayPhpSdk;

include 'aop/AopClient.php';
include 'aop/request/AlipayUserInfoShareRequest.php';
include 'aop/request/AlipaySystemOauthTokenRequest.php';
include 'aop/request/AlipayUserBenefitCreateRequest.php';
include 'aop/request/AlipayUserPointDeductRequest.php';
include 'aop/request/AlipayUserPointRefundRequest.php';
include 'aop/request/AlipayUserGradeQueryRequest.php';
include 'aop/request/AlipayPassTemplateAddRequest.php';
include 'aop/request/AlipayPassTemplateUpdateRequest.php';
include 'aop/request/AlipayPassInstanceAddRequest.php';
include 'aop/request/AlipayPassInstanceUpdateRequest.php';
include 'aop/request/AlipayMarketingCashlessvoucherTemplateCreateRequest.php';
include 'aop/request/AlipayMarketingCashlessvoucherTemplateModifyRequest.php';
include 'aop/request/AlipayMarketingVoucherSendRequest.php';
include 'aop/request/AlipayMarketingVoucherQueryRequest.php';
include 'aop/request/AlipayMarketingVoucherTemplatedetailQueryRequest.php';
include 'aop/request/AlipayOpenPublicMessageSingleSendRequest.php';
include 'aop/request/AlipayOpenPublicFollowBatchqueryRequest.php';
include 'aop/request/AlipayOpenAppMiniTemplatemessageSendRequest.php';
include 'aop/request/AlipayFundTransToaccountTransferRequest.php';
include 'aop/request/AlipayFundTransOrderQueryRequest.php';


class AopSdk
{
    private $appId                  = '';
    private $merchantPrivateKey     = '';
    private $merchantPublicKey      = '';
    private $charset                = 'GBK';
    private $gatewayUrl             = '';
    private $signType               = 'RSA2';
    private $useMode                 = 'H5';

    public function __construct( $config )
    {
        $this->appId                = $config['appId'];
        $this->merchantPrivateKey   = $config['merchantPrivateKey'];
        $this->merchantPublicKey    = $config['merchantPublicKey'];
        $this->alipayPublicKey      = $config['alipayPublicKey'];
        $this->gatewayUrl           = isset($config['gatewayUrl']) ? $config['gatewayUrl'] : "";
        $this->partnerId            = isset($config['partnerId']) ? $config['partnerId'] : "";
    }

    function characet($data)
    {
        if (! empty ( $data )) {
            $fileType = mb_detect_encoding ( $data, array (
                'UTF-8',
                'GBK',
                'GB2312',
                'LATIN1',
                'BIG5'
            ) );
            if ($fileType != 'UTF-8') {
                $data = mb_convert_encoding ( $data, 'UTF-8', $fileType );
            }
        }
        return $data;
    }

    public static function getLogid()
    {
        return md5(uniqid(mt_rand(), true));
    }

    /**
     * 使用SDK执行接口请求
     * @param unknown $request
     * @param string $token
     * @param string $app_auth_token
     * @return Ambigous <boolean, mixed>
     */
    function aopClientRequest($request, $token = NULL, $app_auth_token=null) {
        $aop = new \AopClient ();

        $aop->gatewayUrl            = $this->gatewayUrl;
        $aop->appId                 = $this->appId;
        $aop->rsaPrivateKey         = $this->merchantPrivateKey;
        $aop->alipayrsaPublicKey    = $this->alipayPublicKey;
        $aop->signType              = $this->signType;
        $aop->apiVersion            = "1.0";

        $result = $aop->execute ( $request, $token, $app_auth_token );

        return $result;
    }

    public function requestUserInfo($token, $appAuthToken = null)
    {
        $alipayUserInfoShareRequest = new \AlipayUserInfoShareRequest();
        $result = $this->aopClientRequest ( $alipayUserInfoShareRequest, $token, $appAuthToken);

        return $result;
    }

    public function requestToken($authCode, $appAuthToken = null)
    {
        $alipaySystemOauthTokenRequest = new \AlipaySystemOauthTokenRequest();
        $alipaySystemOauthTokenRequest->setCode ( $authCode );
        $alipaySystemOauthTokenRequest->setGrantType ( "authorization_code" );

        $result = $this->aopClientRequest ( $alipaySystemOauthTokenRequest, null, $appAuthToken);

        return $result;
    }

    public function refreshToken($token, $appAuthToken = null)
    {
        $alipaySystemOauthTokenRequest = new \AlipaySystemOauthTokenRequest();
        $alipaySystemOauthTokenRequest->setRefreshToken($token);
        $alipaySystemOauthTokenRequest->setGrantType ( "refresh_token" );

        $result = $this->aopClientRequest ( $alipaySystemOauthTokenRequest, null, $appAuthToken);

        return $result;
    }

    public function  deductUserPoint( $params )
    {
        $alipayUserPointDeductRequest   = new \AlipayUserPointDeductRequest();

        $bizContent                     = array(
                                                'benefit_id'   => $params['benefitId'],
                                                'out_biz_no'   => $params['outBizId'],
                                                'biz_date'     => date('Y-m-d H:i:s'),
                                                'biz_type'     => isset($params['biz_type']) ? $params['biz_type'] : 'HSQ_DEAL',
                                                'sub_biz_type' => isset($params['sub_biz_type']) ? $params['sub_biz_type'] : 'DISCOUNT_FOOD',
                                            );

        $alipayUserPointDeductRequest->setBizContent( json_encode( $bizContent ) );
        $result                         = $this->aopClientRequest (
            $alipayUserPointDeductRequest,
            $params['token'],
            isset($params['app_auth_token']) ? $params['app_auth_token'] : null
        );
        $responseNode                   = str_replace(".", "_", $alipayUserPointDeductRequest->getApiMethodName() . '_response' );

        return $result->$responseNode;
    }

    public function  refundUserPoint( $params )
    {
        $alipayUserPointRefundRequest   = new \AlipayUserPointRefundRequest();

        $bizContent                     = array(
                                                'user_id'      => $params['userId'],
                                                'out_biz_no'   => $params['outBizId'],
                                                'biz_type'     => isset($params['biz_type']) ? $params['biz_type'] : 'HSQ_DEAL',
                                                'sub_biz_type' => isset($params['sub_biz_type']) ? $params['sub_biz_type'] : 'DISCOUNT_FOOD',
                                            );

        $alipayUserPointRefundRequest->setBizContent( json_encode( $bizContent ) );
        $result                         = $this->aopClientRequest (
            $alipayUserPointRefundRequest,
            isset($params['token']) ? $params['token'] : null,
            isset($params['app_auth_token']) ? $params['app_auth_token'] : null
        );
        $responseNode                   = str_replace(".", "_", $alipayUserPointRefundRequest->getApiMethodName() . '_response' );

        return $result->$responseNode;
    }

    public function requestUserGrade($token, $app_auth_token=null)
    {
        $alipayUserGradeQueryRequest   = new \AlipayUserGradeQueryRequest();
        $result = $this->aopClientRequest (
            $alipayUserGradeQueryRequest,
            $token,
            $app_auth_token
        );

        return $result;
    }

    public function benefitCreate( $params )
    {
       $alipayUserBenefitCreateRequest = new \AlipayUserBenefitCreateRequest();
       $bizContent                     = array(
                                             'benefit_name'      => $params['benefitName'],
                                             'benefit_sub_title' => $params['benefitSubtitle'],
                                             'icon_url'          => $params['iconUrl'],
                                             'start_dt'          => $params['startDt'],
                                             'end_dt'            => $params['endDt'],
                                             'differentiation'   => $params['differentiation'],
                                             'grade_config'      => array(
                                                                        array(
                                                                            'grade'          => $params['grade'],
                                                                            'page_url'       => $params['pageUrl'],
                                                                            'detail'         => $params['detail'],
                                                                            'background_url' => $params['backgroundUrl'],
                                                                            'point'          => $params['point'],
                                                                            'point_discount' => $params['pointDiscount'],
                                                                        ),
                                                                    ),
                                         );
       $alipayUserBenefitCreateRequest->setBizContent( json_encode( $bizContent ) );
       $result       = $this->aopClientRequest (
           $alipayUserBenefitCreateRequest,
           isset($params['token']) ? $params['token'] : null,
           isset($params['app_auth_token']) ? $params['app_auth_token'] : null
       );
       $responseNode = str_replace(".", "_", $alipayUserBenefitCreateRequest->getApiMethodName() . '_response' );
       return $result->$responseNode;
    }

    public function addPassTemplate($tplContent, $token=null, $app_auth_token=null)
    {
        $alipayPassTemplateAddRequest   = new \AlipayPassTemplateAddRequest();

        $bizContent                     = array(
                                                'unique_id'     => self::getLogid(),
                                                'tpl_content'   => $tplContent,
                                            );
        $alipayPassTemplateAddRequest->setBizContent(json_encode($bizContent));
        $result                         = $this->aopClientRequest(
            $alipayPassTemplateAddRequest,
            $token,
            $app_auth_token
        );
        $responseNode                   = str_replace(".", "_", $alipayPassTemplateAddRequest->getApiMethodName() . '_response' );
        return $result->$responseNode;
    }

    public function updatePassTemplate($tplId, $tplContent, $token=null, $app_auth_token=null)
    {
        $alipayPassTemplateUpdateRequest= new \AlipayPassTemplateUpdateRequest();

        $bizContent                     = array(
                                                'tpl_id'        => $tplId,
                                                'tpl_content'   => $tplContent,
                                            );
        $alipayPassTemplateUpdateRequest->setBizContent(json_encode($bizContent));
        $result                         = $this->aopClientRequest(
            $alipayPassTemplateUpdateRequest,
            $token,
            $app_auth_token
        );
        $responseNode                   = str_replace(".", "_", $alipayPassTemplateUpdateRequest->getApiMethodName() . '_response' );
        return $result->$responseNode;
    }

    public function addPassInstance($tplId, $tplParams, $recognitionType, $recognitionInfo, $token=null, $app_auth_token=null)
    {
        $alipayPassInstanceAddRequest   = new \AlipayPassInstanceAddRequest();

        $bizContent                     = array(
                                                'recognition_type'  => $recognitionType,
                                                'tpl_id'            => $tplId,
                                                'recognition_info'  => $recognitionInfo,
                                                'tpl_params'        => $tplParams,
                                            );
        $alipayPassInstanceAddRequest->setBizContent(json_encode($bizContent));
        $result                         = $this->aopClientRequest(
            $alipayPassInstanceAddRequest,
            $token,
            $app_auth_token
        );
        $responseNode                   = str_replace(".", "_", $alipayPassInstanceAddRequest->getApiMethodName() . '_response' );
        return $result->$responseNode;
    }

    public function usedPassInstance($params)
    {
        $alipayPassInstanceUpdateRequest= new \AlipayPassInstanceUpdateRequest();

        $verifyType                     = isset($params['verifyType']) ? $params['verifyType'] : 'qrcode';
        $tplParams                      = array(
                                                'useStateDesc'  => '已使用',
                                            );
        $bizContent                     = array(
                                                'status'        => 'USED',
//                                                'channel_id'    => $this->partnerId,
                                                'channel_id'    => $params['channelId'],
                                                'serial_number' => $params['serialNumber'],
                                                'verify_type'   => $verifyType,
                                                'verify_code'   => $params['verifyCode'],
                                                'tpl_params'    => $tplParams,
                                            );
        $alipayPassInstanceUpdateRequest->setBizContent(json_encode($bizContent));
        $result                         = $this->aopClientRequest(
            $alipayPassInstanceUpdateRequest,
            isset($params['token']) ? $params['token'] : null,
            isset($params['app_auth_token']) ? $params['app_auth_token'] : null
        );
        $responseNode                   = str_replace(".", "_", $alipayPassInstanceUpdateRequest->getApiMethodName() . '_response' );
        return $result->$responseNode;
    }

    public function closedPassInstance($serialNumber, $channelId, $token=null, $app_auth_token=null)
    {
        $alipayPassInstanceUpdateRequest= new \AlipayPassInstanceUpdateRequest();

        $bizContent                     = array(
                                                'status'        => 'CLOSED',
                                                'channel_id'    => $channelId,
                                                'serial_number' => $serialNumber,
                                            );
        $alipayPassInstanceUpdateRequest->setBizContent(json_encode($bizContent));
        $result                         = $this->aopClientRequest(
            $alipayPassInstanceUpdateRequest,
            $token,
            $app_auth_token
        );
        $responseNode                   = str_replace(".", "_", $alipayPassInstanceUpdateRequest->getApiMethodName() . '_response' );
        return $result->$responseNode;
    }

    public function updatePassInstance($serialNumber, $channelId, $tplParams, $token=null, $app_auth_token=null)
    {
        $alipayPassInstanceUpdateRequest= new \AlipayPassInstanceUpdateRequest();

        $bizContent                     = array(
                                                'channel_id'    => $channelId,
                                                'serial_number' => $serialNumber,
                                                'tpl_params'    => $tplParams,
                                            );
        $alipayPassInstanceUpdateRequest->setBizContent(json_encode($bizContent));
        $result                         = $this->aopClientRequest(
            $alipayPassInstanceUpdateRequest,
            $token,
            $app_auth_token
        );
        $responseNode                   = str_replace(".", "_", $alipayPassInstanceUpdateRequest->getApiMethodName() . '_response' );
        return $result->$responseNode;
    }

    /**
     * 无资金券模板接口
     * https://docs.open.alipay.com/api_5/alipay.marketing.cashlessvoucher.template.create
     * @param $brandName               品牌名
     * @param $startTime               发放开始时
     * @param $endTime                 发放结束时
     * @param $voucherValidPeriod      流水号
     * @param $activityNum             流水号
     * @param $descriptionArr          券使用说明
     * @param $quantity                拟发行券的数量
     * @param $amount                  面额
     * @param $floorAmount             最低额度
     * @param $voucherAvailableArr     券可用时段
     * @param $pid                     好时期PID
     * @param $linkUrl                 立即使用跳转地址
     * @param $notifyUrl               核销回调地址
     * @param $token                   用户授权
     * @param $app_auth_token          商家授权
     * @return mixed
     */
    public function marketingTemplateCreate(
        $brandName,
        $startTime,
        $endTime,
        $voucherValidPeriod,
        $activityNum,
        $descriptionArr,
        $quantity,
        $amount,
        $floorAmount,
        $voucherAvailableArr,
        $pid,
        $linkUrl,
        $notifyUrl,
        $token=null,
        $app_auth_token=null
    )
    {
        $request = new \AlipayMarketingCashlessvoucherTemplateCreateRequest ();

        $voucherAvailableTime[] = $voucherAvailableArr;

        $bizContent             = [
            "voucher_type"          => "CASHLESS_FIX_VOUCHER",
            "brand_name"            => $brandName,
            "publish_start_time"    => $startTime,
            "publish_end_time"      => $endTime,
            "voucher_valid_period"  => $voucherValidPeriod,
            "voucher_available_time"  => $voucherAvailableTime,
            "out_biz_no"              => $activityNum,
            "voucher_description"     => $descriptionArr,
            "voucher_quantity"        => $quantity,
            "amount"                  => $amount,
            "floor_amount"            => $floorAmount,
            "rule_conf"               => [
                "PID"   => $pid,
            ],
            "extension_info"          => [
                "useMode"       => $this->useMode,
                "useModeData"   => [
                    "url"       => $linkUrl,
                    "signType"  => $this->signType
                ]
            ],
            "notify_uri"              => $notifyUrl
        ];
        $request->setBizContent(json_encode($bizContent));
        $result                         = $this->aopClientRequest(
            $request,
            $token,
            $app_auth_token
        );
        $responseNode                   = str_replace(".", "_", $request->getApiMethodName() . '_response' );
        return $result->$responseNode;

    }

    /**
     * 无资金券发券
     * https://docs.open.alipay.com/api_5/alipay.marketing.voucher.send/
     * @param $tplId      模板id
     * @param $userId     支付宝用户ID
     * @param $bizNo      流水号
     * @param $amount     券金额
     * @param $token
     * @param $app_auth_token
     * @return mixed
     */
    public function marketingVoucherSend($tplId, $userId, $bizNo,  $amount, $token=null, $app_auth_token=null)
    {
        $request = new \AlipayMarketingVoucherSendRequest();
        $bizContent = [
            "template_id" => $tplId,
            "user_id" => $userId,
            "out_biz_no" => $bizNo,
            "amount" => $amount,
        ];
        $request->setBizContent(json_encode($bizContent));
        $result = $this->aopClientRequest(
            $request,
            $token,
            $app_auth_token
        );
        $responseNode = str_replace(".", "_", $request->getApiMethodName() . '_response');
        return $result->$responseNode;
    }

     /**
     * 模板更新
     * https://docs.open.alipay.com/api_5/alipay.marketing.cashvoucher.template.modify/
     * @param $tplId
     * @param $bizNo
     * @param $publishTime
     * @param $pid
     * @param $token
     * @param $app_auth_token
     * @return mixed
     */
    public function marketingTemplateUpdate($tplId, $bizNo, $publishTime, $pid, $token=null, $app_auth_token=null)
    {

        $request = new \AlipayMarketingCashlessvoucherTemplateModifyRequest ();
        $bizContent = [
            "template_id"         => $tplId,
            "out_biz_no"          => $bizNo,
            "publish_end_time"    => $publishTime,
            "rule_conf"           => $rule_conf   = [
                "PID"   => $pid,
            ]
        ];

        $request->setBizContent(json_encode($bizContent));
        $result                         = $this->aopClientRequest(
            $request,
            $token,
            $app_auth_token
        );
        $responseNode                   = str_replace(".", "_", $request->getApiMethodName() . '_response' );
        return $result->$responseNode;

    }

    /**
     * 券查询
     * @param $voucherId
     * @param $token
     * @param $app_auth_token
     * @return mixed
     */
    public function marketingVoucherQuery($voucherId, $token=null, $app_auth_token=null)
    {
        $request = new \AlipayMarketingVoucherQueryRequest();
        $bizContent = [
            "voucher_id"   => $voucherId
        ];
        $request->setBizContent(json_encode($bizContent));
        $result                         = $this->aopClientRequest(
            $request,
            $token,
            $app_auth_token
        );
        $responseNode                   = str_replace(".", "_", $request->getApiMethodName() . '_response' );
        return $result->$responseNode;

    }

    /**
     * 模板查询
     * @param $tplId
     * @param $token
     * @param $app_auth_token
     * @return mixed
     */
    public function marketingTemplateQuery($tplId, $token=null, $app_auth_token=null)
    {
        $request = new \AlipayMarketingVoucherTemplatedetailQueryRequest();
        $bizContent = [
            "template_id"   => $tplId
        ];
        $request->setBizContent(json_encode($bizContent));
        $result                         = $this->aopClientRequest(
            $request,
            $token,
            $app_auth_token
        );
        $responseNode                   = str_replace(".", "_", $request->getApiMethodName() . '_response' );
        return $result->$responseNode;

    }


    /**
     * 生活号 单发模板消息
     * @param $bizContent
     * @param $token
     * @param $app_auth_token
     */
    public function messageSingleSend( $bizContent, $token=null, $app_auth_token=null )
    {
        $request = new \AlipayOpenPublicMessageSingleSendRequest ();

        $request->setBizContent(json_encode($bizContent));
        $result                         = $this->aopClientRequest(
            $request,
            $token,
            $app_auth_token
        );
        $responseNode                   = str_replace(".", "_", $request->getApiMethodName() . '_response' );
        return $result->$responseNode;

    }

    /**
     * 生活号 获得关注用户列表
     * @param $userId
     * @param $token
     * @param $app_auth_token
     * @return mixed
     */
    public function getFollowUsers( $userId, $token=null, $app_auth_token=null )
    {
        $request = new \AlipayOpenPublicFollowBatchqueryRequest ();
        $bizContent = [
            "next_user_id" => $userId
        ];
        $request->setBizContent(json_encode($bizContent));
        $result                         = $this->aopClientRequest(
            $request,
            $token,
            $app_auth_token
        );
        $responseNode                   = str_replace(".", "_", $request->getApiMethodName() . '_response' );
        return $result->$responseNode;

    }

    /**
     * 小程序模板信息发送
     * @param $data
     * @return mixed
     */
    public function miniTemplateMsgSend($data)
    {
        $request = new \AlipayOpenAppMiniTemplatemessageSendRequest();
        $sendData = [
            "to_user_id"       => $data['to_user_id'],
            "form_id"          => $data['form_id'],
            "user_template_id" => $data['user_template_id'],
            "page"             => $data['page'],
            "data"             => $data['data']
        ];
        $request->setBizContent(json_encode($sendData, true));
        $result        = $this->aopClientRequest(
            $request,
            isset($data['token']) ? $data['token'] : null,
            isset($data['app_auth_token']) ? $data['app_auth_token'] : null
        );
        $responseNode  = str_replace(".", "_", $request->getApiMethodName() . '_response' );

        return $result->$responseNode;

    }

    /**
     * 单笔转账到支付宝账号
     * @param $data
     * @param $token
     * @param $app_auth_token
     * @return array
     */
    public function toAccountTransfer($row, $token=null, $app_auth_token=null)
    {
        $request  = new \AlipayFundTransToaccountTransferRequest();
        $request -> setBizContent(json_encode($row));
        $result = $this->aopClientRequest(
            $request,
            $token,
            $app_auth_token
        );

        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";

        return (array)$result->$responseNode;
    }

    /**
    * 单笔转账到支付宝账号查询
    * @param $data
    * @param $token
    * @param $app_auth_token
    * @return array
    */
    public function toAccountTransferQuery($row, $token=null, $app_auth_token=null)
    {
        $request  = new \AlipayFundTransOrderQueryRequest();
        $request -> setBizContent(json_encode($row));
        $result = $this->aopClientRequest(
            $request,
            $token,
            $app_auth_token
        );

        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";

        return (array)$result->$responseNode;
    }

    /**
     * @param $grant_type
     * @param $code
     * @param $refresh_token
     * @return array
     */
    public function aliOpenAuthTokenApp(
        $grant_type,
        $code=null,
        $refresh_token=null
    )
    {
        $params = [
            'grant_type' => $grant_type
        ];
        if ($code) {
            $params['code'] = $code;
        } else {
            $params['refresh_token'] = $refresh_token;
        }
        $req = new \AlipayOpenAuthTokenAppRequest();
        $req->setBizContent(json_encode($params));
        $result = $this->aopClientRequest($req);

        $responseNode = str_replace(".", "_", $req->getApiMethodName()) . "_response";

        return (array)$result->$responseNode;
    }
}
