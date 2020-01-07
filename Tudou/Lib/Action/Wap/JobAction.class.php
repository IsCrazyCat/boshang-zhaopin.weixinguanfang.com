	<?php
class JobAction extends CommonAction{
	
    public function _initialize(){
        parent::_initialize();
        if($this->_CONFIG['operation']['mall'] == 0){
            $this->error('此功能已关闭');die;
        }
        $goods = cookie('goods_spec');
        $this->assign('cartnum', (int) array_sum($goods));
        $cat = (int) $this->_param('cat');
        $this->assign('goodscates', $goodscates = D('Goodscate')->fetchAll());
		$check_user_addr = D('Paddress')->where(array('user_id'=>$this->uid))->find();//全局检测地址
		$this->assign('check_user_addr', $check_user_addr);
    }
	
    public function index(){
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        $this->assign('keyword', $keyword);
		$linkArr['keyword'] = $keyword;
		
        $cat = (int) $this->_param('cat');
		if($cat){
//			$this->assign('TpGoodsAttribute',$TpGoodsAttribute = $this->getTpGoodsAttributes($cat));
		}
		$this->assign('cat', $cat);
		$linkArr['cat'] = $cat;
		
        $area = (int) $this->_param('area');
		$this->assign('area', $area);
		$linkArr['area'] = $area;
		
        $business = (int) $this->_param('business');
		$this->assign('business', $business);
		$linkArr['business'] = $business; 
		 
        $cate_id = (int) $this->_param('cate_id');
		if($cate_id){
//			$this->assign('TpGoodsAttribute',$TpGoodsAttribute = $this->getTpGoodsAttributes($cate_id));
		}
		$this->assign('cate_id', $cate_id);
		$linkArr['cate_id'] = $cate_id;
		
        $order = (int) $this->_param('order');
        $this->assign('order', $order);
        $linkArr['order'] = $order;
		
		$shop_id = (int) $this->_param('shop_id');
		$this->assign('shop_id', $shop_id);
		$linkArr['shop_id'] = $shop_id;
		
		$type = (int) $this->_param('type');
		$this->assign('type', $type);
		$linkArr['type'] = $type;
		
		$user_id = (int) $this->_param('user_id');
		$this->assign('user_id', $user_id);
		$linkArr['user_id'] = $user_id;
		
		
		//开始组装数组
		$query_string  = explode ('/',$_SERVER["QUERY_STRING"]);
		$arr = array();
		foreach($query_string as $key=>$values){
			if(strstr( $values , 'values_' ) !== false){
				array_push($arr, $values);
			}
		}
		
		foreach($arr as $k=>$v){
			$arr[$v] = $this->_param($v,'htmlspecialchars');
			$query[$v] = $arr[$v];
			$this->assign('query',$query);
			$linkArr[$v] = $arr[$v];
		}
		
		
        $this->assign('nextpage', LinkTo('job/loaddata',$linkArr,array('t' => NOW_TIME,'p' => '0000')));
        $this->assign('scroll', $scroll = D('Goods')->getScroll());
		$this->assign('linkArr',$linkArr);
        $this->display();
    }
	

    public function loaddata(){
        $Goods = D('Goods');
        import('ORG.Util.Page');
      
        $area = (int) $this->_param('area');
        if($area){
            $map['area_id'] = $area;
			$this->assign('area', $area);
			$linkArr['area'] = $area;
        }
		
		
		$business = (int) $this->_param('business');
        if($business){
            $map['business_id'] = $business;
			$this->assign('business', $business);
			$linkArr['business'] = $business;
        }
		
   
		
		$shop_id = (int) $this->_param('shop_id');
        if($shop_id){
            $map['shop_id'] = $shop_id;
			$this->assign('shop_id', $shop_id);
			$linkArr['shop_id'] = $shop_id;
        }
		
		$user_id = (int) $this->_param('user_id');
        if($user_id){
			$this->assign('user_id', $user_id);
			$linkArr['user_id'] = $user_id;
        }
		
		$type = (int) $this->_param('type');
        if($type){
			$this->assign('type', $type);
			$linkArr['type'] = $type;
        }
		
        $map['audit'] = 1;
        $map['closed'] = 0;
        $map['end_date'] = array('egt', TODAY);
//		$map['city_id'] = $this->city_id;
		
		
        if($keyword = $this->_param('keyword', 'htmlspecialchars')){
            $map['title|intro'] = array('LIKE', '%' . $keyword . '%');
			$this->assign('keyword', $keyword);
			$linkArr['keyword'] = $keyword;
        }
		
		
		//开始组装数组
		$query_string  = explode ('/',$_SERVER["QUERY_STRING"]);
		$arr = array();
		foreach($query_string as $key=>$values){
			if(strstr( $values , 'values_' ) !== false){
				array_push($arr, $values);
			}
		}
		
		foreach($arr as $k=>$v){
			$arr[$v] = $this->_param($v,'htmlspecialchars');
			$query[$v] = $arr[$v];
			$this->assign('query',$query);
			$linkArr[$v] = $arr[$v];
		}
		
		$array = array();
		foreach($query as $kk=>$vv){
			$explode = explode('_',$kk); 
			$array[$kk]['attr_id'] = $explode['1'];
			$array[$kk]['attr_value'] = $vv;
		}
		foreach($array as $val){
            $attr_values[$val['attr_value']] = $val['attr_value'];
        }

		$maps['attr_value']  = array('IN',$attr_values);
		//$TpGoodsAttr = M('TpGoodsAttr')->where($map)->group('attr_value')->select();
		$TpGoodsAttr = M('TpGoodsAttr')->where($maps)->select();
		
		
		
		$result= array();
		foreach($TpGoodsAttr as $key => $info){
		 	$result[$info['goods_id']][] = $info;
		}
		
		foreach($result as $kkk => $vvv){
			foreach($vvv as $k2 => $v2){
				$attr_valuess[$kkk][$k2] = $v2['attr_value'];
			}
        }
		
		$implode = implode('_',$attr_values);
		
		foreach($attr_valuess as $k3 => $v3){
			$implodes = implode('_',$v3);
			if($implodes != $implode){
				unset($attr_valuess[$k3]);
			}
        }
		
		
		foreach($attr_valuess as $k4 => $v4){
            $goods_ids[$k4] = $k4;
        }
		if($array){
			$map['goods_id'] = array('IN',$goods_ids);
		}
		//多属性搜索结束
		
		
		
		$cate_id = (int) $this->_param('cate_id');
		$cat = (int) $this->_param('cat');
        if($cate_id){
			if($cate_id){
				if(empty($array)){
					$map['cate_id'] = $cate_id;
				}
				$linkArr['cate_id'] = $cate_id;
//				$this->assign('TpGoodsAttribute',$TpGoodsAttribute = $this->getTpGoodsAttributes($cate_id));
			}
        }else{
			$catids = D('Goodscate')->getChildren($cat);
//			$this->assign('TpGoodsAttribute',$TpGoodsAttribute = $this->getTpGoodsAttributes($cat));
            if(!empty($catids)){
				if(empty($array)){
					$map['cate_id'] = array('IN', $catids);
				}
            }else{
                $map['cate_id'] = $cate_id;
				$linkArr['cate_id'] = $cate_id;
				
            }
			
		}
		$this->assign('cat', $cat);
		$this->assign('cate_id', $cate_id);
	
        $count = $Goods->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
		
		$lat = addslashes(cookie('lat'));
        $lng = addslashes(cookie('lng'));
        if(empty($lat) || empty($lng)){
            $lat = $this->city['lat'];
            $lng = $this->city['lng'];
        }
		
		$order = $this->_param('order', 'htmlspecialchars');
        switch($order){
            case '1':
                $orderby = array('create_time' => 'asc');
                break;
            case '2':
                $orderby = array('old_num' => 'asc');
                break;
            case '3':
                $orderby = array('mall_price' => 'desc');
                break;
            case '4':
                $orderby = array('mall_price' => 'asc');
                break;
			case '5':
                $orderby = array('top_time' => 'desc','orderby' =>'asc');
                break;
			default:
                $orderby = array('create_time' => 'asc');
                break;
        }
		
		
		
        $list = $Goods->where($map)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $val){
            if($val['shop_id']) {
                $shop_ids[$val['shop_id']] = $val['shop_id'];
            }
			$Shop = D('Shop')->find($val['shop_id']);
			$val['d'] = getDistance($lat, $lng, $Shop['lat'], $Shop['lng']);
            $list[$k] = $val;
        }
        $hot_jobs = D("Job")->where(["is_hot"=>'1','is_able'=>1])->limit(5)->order("job_order ASC")->select();

        $this->assign('hot_jobs',$hot_jobs);//热门工作
//        $this->assign('list', $list);
        $this->assign('page', $show);

        $this->display();
    }
    public function detail(){
        $job_id = $this->_param('job_id');
        //工作岗位信息
        $jobInfo = D('Job')->where('job_id', $job_id)->find();
        //工作岗位介绍图，就是工作介绍的时候的轮播图
        $jobImages = D('JobImages')->where('job_id', $job_id)->order('img_id', DESC)->select();
        //工作岗位标签 只展示3个
        $job_tags = explode(';', $jobInfo['job_tags']);
        $job_tags = count($job_tags) > 3 ? array($job_tags[0], $job_tags[1], $job_tags[2]) : $job_tags;
        //该职位已参加报名人数
        $jion_count = D('JobApply')->where('job_id', $job_id)->count();

        //岗位补贴信息
        $job_subsidy = D('JobSubsidy')->where('job_id', $job_id)->order('creat_time', DESC)->select();

        //描述信息获取
        //0薪资福利 1薪资说明 2食宿介绍 3保险说明 4注意事项 5录用条件 6岗位介绍 7公司介绍 8入职流程 9平台提示
        $desc_all = array();
        for ($i = 0; $i <= 9; $i++) {
            $desc = D('JobDescription')->where(['job_id' => $job_id, 'desc_type' => $i])->order('content_order', ASC)->select();
            $desc_all[$i] = $desc;
        }
        //公司位置

        //猜你喜欢 这里根据tags标签筛选 最多六个
        $where_params = "";
        foreach ($job_tags as $key => $val) {
            if ($key == 0) {
                $where_params .= "job_tags like '%" . $val . "%'";
                continue;
            }
            $where_params .= " or job_tags like '%" . $val . "%'";
        }
//        $params1['_complex'] = array(
//            'job_tags' => array('like','%提供住宿%'),
//            'job_tags' => array('like','%补贴%'),
//            '_logic'=>'or'
//        );
        $like_jobs = D('Job')->where($where_params)->limit(6)->select();

        //底部的免费报名和VIP拿高价 这段代码写的真傻逼
        $bottom_price = "";
        $bottom_price_vip = "";
        if ($jobInfo['show_price_type'] == 0) {
            $bottom_price = $jobInfo['job_price_hour'] . $jobInfo['job_price_hour_unit'];
            $bottom_price_vip = $jobInfo['job_price_hour_vip'] . $jobInfo['job_price_hour_unit'];
        } else if ($jobInfo['show_price_type'] == 1) {
            $bottom_price = $jobInfo['job_price_day'] . $jobInfo['job_price_day_unit'];
            $bottom_price_vip = $jobInfo['job_price_day_vip'] . $jobInfo['job_price_day_unit'];
        } else if ($jobInfo['show_price_type'] == 2) {
            $bottom_price = $jobInfo['job_subsidy_hour'] . $jobInfo['job_subsidy_hour_unit'];
            $bottom_price_vip = $jobInfo['job_subsidy_hour_vip'] . $jobInfo['job_subsidy_hour_unit'];
        } else if ($jobInfo['show_price_type'] == 3) {
            $bottom_price = $jobInfo['job_subsidy_day'] . $jobInfo['job_subsidy_day_unit'];
            $bottom_price_vip = $jobInfo['job_subsidy_day_vip'] . $jobInfo['job_subsidy_day_unit'];
        } else if ($jobInfo['show_price_type'] == 4) {
            $bottom_price = $jobInfo['job_subsidy_monty'] . $jobInfo['job_subsidy_monty_unit'];
            $bottom_price_vip = $jobInfo['job_subsidy_monty_vip'] . $jobInfo['job_subsidy_monty_unit'];
        }


        $this->assign('job_info', $jobInfo);
        $this->assign('job_images', $jobImages);
        $this->assign('job_tags', $job_tags);
        $this->assign('jion_count', $jion_count);

        $this->assign('sub_sidy', $job_subsidy);

        $this->assign('desc_all', $desc_all);
        $this->assign('like_jobs', $like_jobs);

        $this->assign('bottom_price', $bottom_price);
        $this->assign('bottom_price_vip', $bottom_price_vip);
        $this->display();
    }

    public function vipRouter(){
        $rank = D('Userrank')->where(array('rank_name'=>'VIP会员'))->find();
        $this->assign('rank',$rank);
        $this->display();
    }

}