<?php

class JobAction extends CommonAction {

   public function index(){

		if(empty($this->uid)){

			redirect('wap/passport/login');

		}else{
			redirect("http://" . $_SERVER['HTTP_HOST'] . "/user/member/index.html");

		}

   }
    public function jobSign(){
        if(empty($this->uid)){
            header('Location:'.U('wap/passport/login'));
            die;
        }else{
            $this->success('恭喜您报名成功！',U('user/job/signInfo'));
            die;
        }
    }
    public function signInfo(){
       $this->display();
    }

    /**
     * 支付成为ＶＩＰ
     */
    public function pay(){
        if (empty($this->uid)) {
            $this->error('登录状态失效!', U('passport/login'));
            die;
        }
        $user = D('Users')->find($this->uid);
        if(!empty($user)){
            if($user['rank_id'] > 0){
                $this->success('您已是VIP会员，无需重复购买!', U('member/index'));
                die;
            }
        }else{
            $this->error('登录状态失效!', U('passport/login'));
            die;
        }
        $rank = D('Userrank')->where(array('rank_name'=>array('LIKE','%VIP%')))->find();
        if($this->isPost()){
            if(!($code = $this->_post('code'))){
                $this->tuMsg('请选择支付方式');
            }
            $logs = array(
                'type' => 'rank',
                'user_id' => $this->uid,
                'order_id' => -1,
                'code' => $code,
                'need_pay' => $rank['price'],
                'create_time' => NOW_TIME,
                'create_ip' => get_client_ip(),
                'is_paid' => 0
            );
            $logs['log_id'] = D('Paymentlogs')->add($logs);
            D('Weixintmpl')->weixin_notice_VIP_user(-1,$this->uid,1);//商城微信通知
            $this->tuMsg('订单设置完成，即将进入付款。', U('wap/payment/payment', array('log_id' => $logs['log_id'])));
        }
        $this->assign('rank',$rank);
        $this->assign('payment', D('Payment')->getPayments(true));
        $this->display();
    }
    public function jobCanalSign(){
        if (empty($this->uid)) {
            $this->error('登录状态失效!', U('passport/login'));
            die;
        }
        $goods_id = $this->_param('goods_id');
        $apply_id = $this->_param('apply_id');
        if(empty($goods_id)){
            $this->error('请选择需要取消的工作',U('user/goods/index',array('aready'=>999)));
        }
        $result = D('jobApply')->save(array('id'=>$apply_id,'goods_id'=>$goods_id,'user_id'=>$this->uid,'cancal'=>0));
        if(!empty($result)){
            $this->success('恭喜您，取消报名成功！',U('user/goods/index',array('aready'=>3)));
        }else{
            $this->success('取消报名失败，请稍后重试！',U('user/goods/index',array('aready'=>0)));
        }
    }

}



