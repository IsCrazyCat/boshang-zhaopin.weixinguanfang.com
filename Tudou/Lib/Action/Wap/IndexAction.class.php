<?php


class IndexAction extends CommonAction {

      public function index(){

//          $hot_jobs = D("Job")->where(["is_hot"=>'1','is_able'=>1])->limit(5)->order("job_order ASC")->select();
//          $this->assign('hot_jobs',$hot_jobs);//热门工作
        $CONFIG = D('Setting')->fetchAll();
        $goods = D('Goods')->where(array('audti'=>1,'closed'=>0,'end_date' =>  array('EGT', TODAY)))->limit(8)->select();
        $this->assign('goods',$goods);
        $this->assign('news', $news = D('Article')->where(array( 'closed' => 0, 'audit' => 1))->order(array('create_time' => 'desc'))->limit(0, 5)->select());
		$maps = array('status' => 2,'closed'=>0);
		$this->assign('nav',$nav = D('Navigation') ->where($maps)->order(array('orderby' => 'asc'))->select());
		$this->assign('mobile',$CONFIG['site']['tel']);
        $this->display();
    }
   

    public function search() {
        $keys = D('Keyword')->fetchAll();
        $keytype = D('Keyword')->getKeyType();
        $this->assign('keys',$keys);
        $this->display();
    }



	public function more() {
		$cates = D('NavCate')->order(array('cate_id' => 'asc'))->select();
		
        foreach($cates as $k => $v){
			if($v['parent_id'] == 0){
            	if($v['type'] != 2){
                	unset($cates[$k]);
                }
				
			}
		}
		$this->assign('cates',$cates);
		$this->display();
	}
	
	public function fabu() {
		$this->display();
	}
	public function house() {
		$this->display();
	}
	//获取定位
	public function dingwei(){
        $lat = I('lat');
        $lng = I('lon');
		$url = I('url');
		$address = I('address');
		cookie('lat',$lat,3600);
        cookie('lng',$lng,3600);
		cookie('url', $url);//保存clookie
		list($code,$city_id,$city_name,$msg,$address,$url) = $this->getDingwei($lat, $lng);
		//p($code.'---'.$city_id.'---'.$city_name.'---'.$msg.'---'.$address.'---'.$url);die;			
        $this->ajaxReturn(array('la'=>$lat,'lon'=>$lng,'code'=>$code,'city_id'=> $city_id,'city_name'=> $city_name,'msg'=> $msg,'address'=>$address,'url'=>$url));
    }
	
	//返回定位
	public function getDingwei($lat, $lng){
		$config = D('Setting')->fetchAll();
		$addr = $this->getArea($lat, $lng);
		cookie('addr',$addr, 3600);
		if(!empty($addr)) {
			cookie('location', 2);
		}
		$city = mb_substr($addr['city'], 0, -1, 'utf-8');
		$district = mb_substr($addr['district'], 0, -1, 'utf-8');
	
		$position = $city . $district;
		$city = D('Pinyin')->pinyin($city);//城市拼音
		$district = D('Pinyin')->pinyin($district);//地区拼音
		
		$town = D('City')->where(array('pinyin'=>$district,'is_open' =>1))->find();//城市
		$county = D('City')->where(array('pinyin'=>$city,'is_open' =>1))->find();//县城
		
		
		$city_id = cookie('city_id');
		$cityop = cookie('cityop');
		
		
		
		if(!empty($city_id)){
				if($city_id == $county['city_id'] || $city_id == $town['city_id'] || $cityop == 1) {
					//return array($code = 1,$city_id = 999,$city_name = '有城市了',$msg = '不弹出了',$position);
					return array($code = 1,$city_id = $config['site']['city_id'],$city_name = '有城市了',$msg = '不弹出了',$position);
				}else{
					if(!empty($county)){
						return array($code = 2,$city_id = $county['city_id'],$city_name = $county['name'],$msg = '县城位置【'.$position.'】',$position,$url = U('city/change', array('city_id' => $county['city_id'],'type'=>3)));
					}elseif(!empty($town)) {
						return array($code = 2,$city_id = $town['city_id'],$city_name = $town['name'],$msg = '城市位置【'.$position.'】',$position,$url = U('city/change', array('city_id' => $town['city_id'],'type'=>3)));
					}
				}
			}else{
				if(!empty($county)){
					return array($code = 2,$city_id = $county['city_id'],$city_name = $county['name'],$msg = '当前县城：'.$position,$position,$url = U('city/change', array('city_id' => $county['city_id'],'type'=>3)));
				}elseif(!empty($town)) {
					return array($code = 2,$city_id = $town['city_id'],$city_name = $town['name'],$msg = '当前城市：'.$position,$position,$url = U('city/change', array('city_id' => $town['city_id'],'type'=>3)));
				}else{
					$detail = D('City')->find($config['site']['city_id']);//城市
					return array($code = 6,$city_id = $config['site']['city_id'],$city_name = $detail['name'],$msg = '当前位置：'.$position.'没有匹配到城市 ',$position,$url = U('city/change', array('city_id' => $config['site']['city_id'],'type'=>3)));
				}
			}
		
		
		
	}
	
	//通过接口将坐标转地理位置
    function getArea($lat, $lng){
        $url = 'https://api.map.baidu.com/geocoder/v2/?ak=C9613fa45f450daa331d85184c920119&location=' . $lat . ',' . $lng . '&output=json&pois=1';
        $arr = file_get_contents($url);
        $arr = json_decode($arr);
        $place = $pois = $po = array();
        foreach ($arr->result->pois as $value) {
            $po['name'] = $value->name;
            $po['addr'] = $value->addr;
            $pois[] = $po;
        }
        $place['formatted_address'] = $arr->result->formatted_address;
		$place['city'] = $arr -> result -> addressComponent -> city;
		$place['district'] = $arr -> result -> addressComponent -> district;
        $place['pois'] = $pois;
        return $place;
    }
	
	//中转站
	public function url(){
		$config = D('Setting')->fetchAll();
		$index_mask_cookie = ($config['other']['index_mask_cookie'] >= 7200) ? $config['other']['index_mask_cookie'] : 3600*4;
		if($config['other']['index_mask_show'] && $config['other']['index_mask_url']){
			cookie('index_mask_show',$config['other']['index_mask_cookie'],$config['other']['index_mask_cookie']);
			header("Location:" . $config['other']['index_mask_url']);
			die;
		}else{
			cookie('index_mask_show',$index_mask_cookie);
			header("Location:" . U('Wap/index/index'));
         	die;
		}
    }
	

	
	//红包详情
	public function hongbao(){
		if(empty($this->uid)){
            $this->error('登录状态失效!', U('passport/login'));
            die;
        }
		$envelope = M('Envelope')->where(array('bg_date' => array('ELT', TODAY),'closed'=>'0','type'=>'1'))->find();
		$this->assign('show',$show = $envelope['bg_date'] ? '1' : '0');
		$this->assign('envelope',$envelope); 
        $this->display();
    }
	
	//领取红包
    public function envelope($envelope_id = 0,$orderType = 0){
        $oenvelope_id = I('envelope_id', '', 'intval,trim');
		$orderType = I('orderType', '', 'intval,trim');
		
		if(!$envelope_id){
			$this->ajaxReturn(array('code'=>'0','msg'=>'红包ID不存在'));
		}
		if(!$Envelope = M('Envelope')->find($envelope_id)){
			$this->ajaxReturn(array('code'=>'0','msg'=>'红包详情'));
		}
		
		$bg_time = strtotime(TODAY);
		if($EnvelopeLogs = M('EnvelopeLogs')->where(array('user_id'=>$this->uid,'create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)),'envelope_id'=>$envelope_id,'order_id'=>1,'orderType'=>$orderType))->find()){
			$this->ajaxReturn(array('code'=>'0','msg'=>'今日您已经领取过了'));
		}
		
		if(!$orderType){
			$this->ajaxReturn(array('code'=>'0','msg'=>'类型不能为空'));
		}
		
		$money = (int)(rand(1,100));
		
		if($Envelope['prestore'] < $money){
			M('Envelope')->where(array('envelope_id'=>$envelope_id))->save(array('closed'=>1)); //关闭返还
			//如果是企业红包
			if($Envelope['type'] == 2){
				$shop = M('Shop')->find($Envelope['shop_id']);
				D('Users')->addMoney($shop['user_id'],$Envelope['prestore'],'用户兑换的金额大于红包剩余余额，自动关闭该红包');
			}
			$this->ajaxReturn(array('code'=>'0','msg'=>'当前红包金额不足无法领取'));
		}
		
		if($money > 0){
			$intro = '【平台发布红包】ID【'.$envelope_id.'】用户领取红包【'.round($money/100,2).'】';
			$arr['type'] = $Envelope['type'];
			$arr['orderType'] = $orderType;
			$arr['envelope_id'] = $envelope_id;
			$arr['shop_id'] = $Envelope['shop_id'] ? $Envelope['shop_id'] : '0';
			$arr['user_id'] = $this->uid;
			$arr['order_id'] = 1;
			$arr['money'] = $money;
			$arr['surplus_prestore'] = $Envelope['prestore'] - $money;
			$arr['intro'] = $intro;
			$arr['create_time'] = time();
			$arr['create_ip'] = get_client_ip();
			if(M('EnvelopeLogs')->add($arr)){
				D('Users')->addMoney($this->uid,$money,$intro);
				D('Envelope')->where(array('envelope_id'=>$envelope_id))->setDec('prestore',$money); 
				$this->ajaxReturn(array('code'=>'1','msg'=>$intro,'url'=>U('user/index/index')));        
			}else{
				$this->ajaxReturn(array('code'=>'0','msg'=>'领取失败'));
			}
		}else{
			$this->ajaxReturn(array('code'=>'0','msg'=>'平台红包配置有误'));
		}
		
    }
    public function closed(){

    }

    /**
     * 排行榜
     */
	public function ranking(){

        $users = D('Users')->where(array('fuid1'=>array('neq','0')))->field(array('COUNT(*)' => 'cnt', 'fuid1'))->order('cnt desc')->group('fuid1')->limit(0,10)->select();
        foreach ($users as $key=>$val){
            $user = D('Users')->find($val['fuid1']);
            $users[$key]['user']=$user;
        }
        if(!empty($this->uid)){
            $this->assign('user_id',$this->uid);
        }
        $config = D('Setting')->fetchAll();
        $this->assign('remark',$config['ranking']['remark']);
        $this->assign('remark1',$config['ranking']['remark1']);
        $this->assign('users',$users);
        $this->display();
    }
}
