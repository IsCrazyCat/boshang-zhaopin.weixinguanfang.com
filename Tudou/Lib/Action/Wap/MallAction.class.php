<?php
class MallAction extends CommonAction{

    public function _initialize(){
        parent::_initialize();
        if($this->_CONFIG['operation']['mall'] == 0){
            $this->error('此功能已关闭');die;
        }
        $goods = cookie('goods_spec');
        $this->assign('cartnum', (int) array_sum($goods));
        $cat = (int) $this->_param('cat');
        $this->assign('goodscates', $goodscates = D('Goodscate')->fetchAll());
        $this->assign('goodstags', $goodstags = D('Goodstag')->fetchAll());
        $check_user_addr = D('Paddress')->where(array('user_id'=>$this->uid))->find();//全局检测地址
        $this->assign('check_user_addr', $check_user_addr);
    }


    public function push(){
        $obj = D('Goods');
        import('ORG.Util.Page');
        $map = array('audit' => 1, 'closed' => 0, 'end_date' => array('EGT', TODAY));
        $count = $obj->where($map)->count();
        $Page = new Page($count, 5);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $goods = $obj->where($map)->order(array('top_time' =>'desc','orderby' =>'asc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('goods', $goods);
        $this->assign('page', $show);
        $this->display();
    }

    public function index(){
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        $this->assign('keyword', $keyword);
        $linkArr['keyword'] = $keyword;

        $cat = (int) $this->_param('cat');
        if($cat){
            $this->assign('TpGoodsAttribute',$TpGoodsAttribute = $this->getTpGoodsAttributes($cat));
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
            $this->assign('TpGoodsAttribute',$TpGoodsAttribute = $this->getTpGoodsAttributes($cate_id));
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

        $tag_id = (int) $this->_param('tag_id');
        $this->assign('tag_id', $tag_id);
        $linkArr['tag_id'] = $tag_id;

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


        $this->assign('nextpage', LinkTo('mall/loaddata',$linkArr,array('t' => NOW_TIME,'p' => '0000')));
        $this->assign('scroll', $scroll = D('Goods')->getScroll());
        $this->assign('linkArr',$linkArr);
        $this->display();
    }


    public function getTpGoodsAttributes($cat){
        $res = D('Goodscate')->find($cat);
        $TpGoodsType = M('TpGoodsType')->where(array('id'=>$res['type_id']))->find();
        $TpGoodsAttributes = M('TpGoodsAttribute')->where(array('type_id'=>$TpGoodsType['id'],'attr_input_type'=>1))->select();
        foreach($TpGoodsAttributes as $k => $val){
            if(empty($val['attr_values']) || trim($val['attr_values']) == ''){
                unset($TpGoodsAttributes[$k]);
            }

        }
        foreach($TpGoodsAttributes as $kk => $vv){
            $TpGoodsAttribute[$kk]['attr_id'] = $vv['attr_id'];
            $TpGoodsAttribute[$kk]['attr_name'] = $vv['attr_name'];
            $TpGoodsAttribute[$kk]['attr_values'] = explode(PHP_EOL,$vv['attr_values']);
        }
        return $TpGoodsAttribute;
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

        $tag_id = (int) $this->_param('tag_id');
        if($tag_id){
            $map['tag_id'] = array('like','%'.$tag_id.'%');
            $this->assign('tag_id', $tag_id);
            $linkArr['tag_id'] = $tag_id;
        }

        $map['audit'] = 1;
        $map['closed'] = 0;
        $map['end_date'] = array('egt', TODAY);
//        $map['city_id'] = $this->city_id;


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
                $this->assign('TpGoodsAttribute',$TpGoodsAttribute = $this->getTpGoodsAttributes($cate_id));
            }
        }else{
            $catids = D('Goodscate')->getChildren($cat);
            $this->assign('TpGoodsAttribute',$TpGoodsAttribute = $this->getTpGoodsAttributes($cat));
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
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    //工作收藏
    public function favorites(){
        if (empty($this->uid)) {
            $this->tuMsg('登录状态失效!', U('passport/login'));
            die;
        }
        $goods_id = (int) $this->_get('goods_id');
        if (!($detail = D('Goods')->find($goods_id))) {
            $this->tuMsg('没有该工作');
        }
        if ($detail['closed']) {
            $this->tuMsg('该工作已经被删除');
        }
        if (D('Goodsfavorites')->check($goods_id, $this->uid)) {
            $this->tuMsg('您已经收藏过了');
        }
        $data = array('goods_id' => $goods_id, 'user_id' => $this->uid, 'create_time' => NOW_TIME, 'create_ip' => get_client_ip());
        if (D('Goodsfavorites')->add($data)) {
            $this->tuMsg('恭喜您收藏成功', U('mall/detail', array('goods_id' => $goods_id)));
        }
        $this->tuMsg('收藏失败');
    }
    //立即购买
    public function buy($goods_id){
        $goods_id = (int) $goods_id;
        if (empty($goods_id)) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '请选择产品'));
        }
        if (!($detail = D('Goods')->find($goods_id))) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '工作不存在'));
        }
        if ($detail['closed'] != 0 || $detail['audit'] != 1) {
            $this->tuMsg('该工作不存在');
        }
        if ($detail['end_date'] < TODAY) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '该工作已经过期，暂时不能购买'));
        }
        $goods_spec= cookie('goods_spec');
        $num = (int) $this->_get('num');
        $spec_key =  $this->_get('spec_key');
        if (empty($num) || $num <= 0) {
            $num = 1;
        }
        $is_spec_stock = is_spec_stock($goods_id,$spec_key,$num);
        if(!$is_spec_stock){
            $this->ajaxReturn(array('status' => 'error', 'msg' => '亲！该规格库存不足了，少买点吧！'));
        }
        if ($detail['num'] < $num) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '亲！该工作只剩' . $detail['num'] . '件了，少买点吧！'));
        }
        $goods_spec_v = $goods_id.'|'.$spec_key; //重新组合那个 工作id和那个啥规格键
        if (isset($goods_spec[$goods_spec_v])) {
            $goods_spec[$goods_spec_v] += $num;
        } else {
            $goods_spec[$goods_spec_v] = $num;
        }
        $key[$goods_id]=$spec_key;//规格
        cookie('goods_spec', $goods_spec, 604800);
        $this->ajaxReturn(array('status' => 'success', 'msg' => '加入购物车成功,正在跳转到购物车', 'url' => U('mall/cart')));
    }
    public function cartadd($goods_id){
        $shop_id = (int) $this->_param('shop_id');
        $goods_id = (int) $goods_id;
        if (empty($goods_id)) {
            die('请选择产品');
        }
        if (!($detail = D('Goods')->find($goods_id))) {
            die('该工作不存在');
        }
        if ($detail['closed'] != 0 || $detail['audit'] != 1) {
            die('该工作不存在');
        }
        if ($detail['end_date'] < TODAY) {
            die('该工作已经过期，暂时不能购买');
        }
        $goods = cookie('goods');
        if (isset($goods[$goods_id])) {
            $goods[$goods_id] = $goods[$goods_id] + 1;
        } else {
            $goods[$goods_id] = 1;
        }
        cookie('goods', $goods);
        die('0');
    }
    public function cartadd2(){
        if (IS_AJAX) {
            $shop_id = (int) $_POST['shop_id'];
            $goods_id = (int) $_POST['goods_id'];
            $goods_spec= cookie('goods_spec');
            $spec_key =  $_POST['spec_key'];
            $num =  $_POST['num'];
            if (empty($goods_id)) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '请选择工作'));
            }
            if (!($detail = D('Goods')->find($goods_id))) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '该工作不存在'));
            }
            if ($detail['closed'] != 0 || $detail['audit'] != 1) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '该工作不存在'));
            }
            if ($detail['end_date'] < TODAY) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '该工作已经过期，暂时不能购买'));
            }
            if ($detail['num'] <= 0) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '亲！没有库存了！'));
            }
            $goods_spec_v = $goods_id.'|'.$spec_key;
            //重新组合那个 工作id和那个啥规格键
            //加入购物车时候检查规格库存  如果不走这里他会走下面的
            $is_spec_stock = is_spec_stock($goods_id,$spec_key,$num);
            if(!$is_spec_stock){
                $this->ajaxReturn(array('status' => 'error', 'msg' => '亲！该规格库存不足了，少买点吧！'));
            }
            if ($detail['num'] < $num) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '亲！该工作只剩' . $detail['num'] . '件了，少买点吧！'));
            }
            if (isset($goods_spec[$goods_spec_v])) {
                $goods_spec[$goods_spec_v] += $num;
            } else {
                $goods_spec[$goods_spec_v] = $num;
            }
            cookie('goods_spec', $goods_spec, 604800);
            $goods = cookie('goods');
            if (isset($goods[$goods_id])) {
                $goods[$goods_id] = $goods[$goods_id] + 1;
            } else {
                $goods[$goods_id] = 1;
            }
            $this->ajaxReturn(array('status' => 'success', 'msg' => '加入购物车成功'));
        }
    }
    public function cart(){

        $goods = cookie('goods');
        $back = end($goods);
        $back = key($goods);
        $goods_spec = cookie('goods_spec');
        $this->assign('back', $back);


        if(empty($goods_spec)) {
            $this->error('亲还没有选购产品呢!', U('mall/index'));
        }

        $spec_keys = array_keys($goods_spec);
        $spec_arr = $this ->spec_to_arr($goods_spec);
        $goods_ids= $this->get_goods_ids($goods_spec);

        foreach($goods_ids as $k=> $v){
            $cart_goods[] = D('Goods')->itemsByIds($v);
        }
        $shop_ids = array();
        foreach ($cart_goods as $k => $val) {
            foreach($val as $key => $det){
                $cart_goods[$k][$key]['buy_num'] = $spec_arr[$k][2];//购买数量
                $cart_goods[$k][$key]['sky'] =  $spec_arr[$k][1];
                $cart_goods[$k][$key]['goods_spec'] = $spec_keys[$k];
                $shop_ids[$det['shop_id']] = $det['shop_id'];
                if(!empty($cart_goods[$k][$key][sky])){
                    //通过这个sky来查多属性里面的价格  其实也就是一条数据
                    $spt=D('TpSpecGoodsPrice')->where("`key`='{$cart_goods[$k][$key][sky]}' and `goods_id`='{$cart_goods[$k][$key][goods_id]}'")->find();            		$cart_goods[$k][$key]['mall_price']=$spt['price']*100;
                    $cart_goods[$k][$key]['key_name']=$spt['key_name'];
                }
            }

        }
        $this->assign('cart_shops', D('Shop')->itemsByIds($shop_ids));
        $this->assign('cart_goods', $cart_goods);

        $this->display();
    }

    private function spec_to_arr($goods_spec){
        $spec_key = array_keys($goods_spec);
        foreach($spec_key as $k=> $v){
            $spec_arr[$k] = explode('|',$v);
            $spec_arr[$k][]= $goods_spec[$v];
        }
        return $spec_arr;

    }
    private function get_goods_ids($goods_spec){
        $spec_arr = $this -> spec_to_arr($goods_spec);
        foreach($spec_arr as $k => $v){
            $goods_ids[] = $v[0];
        }
        return $goods_ids;
    }

    public function detail($goods_id) {
        $goods_id = (int) $goods_id;
        if (empty($goods_id)) {
            $this->error('工作不存在');
        }
        if (!($detail = D('Goods')->find($goods_id))) {
            $this->error('工作不存在');
        }
        if ($detail['closed'] != 0 || $detail['audit'] != 1) {
            $this->error('工作不存在');
        }
        $shop_id = $detail['shop_id'];


        $this->assign('recom',$recom = D('Goods')->where(array('shop_id' => $shop_id,'audit'=>1,'closed'=>0,'goods_id' => array('neq', $goods_id),'end_date' => array('EGT', TODAY)))->limit(0, 5)->select());


        $this->assign('detail', $detail);
        $this->assign('shop', D('Shop')->find($shop_id));
        $this->assign('shopdetails', D('Shopdetails')->find($shop_id));
        $filter_spec = $this->get_spec($goods_id); //获取工作规格参数        
        $goodsss=M('Goods')->find($goods_id);
        $goodsss[mall_price]=$goodsss[mall_price]/100;
        $spec_goods_price  = M('TpSpecGoodsPrice')->where("goods_id = $goods_id")->getField("key,price,store_count"); // 规格 对应 价格 库存表
        if($spec_goods_price != null){
            $this->assign('spec_goods_price', json_encode($spec_goods_price,true)); // 规格 对应 价格 库存表
        }
        $yh=$goodsss[yh];
        if($yh!= '0'){
            $yh=explode(PHP_EOL,$yh);
            for ($i=0; $i < count($yh)-1;$i++){
                $yh[s][]=explode(',',$yh[$i]);
            }
            foreach($yh[s] as $k2=>$vo){
                foreach($vo as $k2=>$v2){
                    $rs[$k2][] = $v2;
                }
            }
            $goodsss['zks'][]=$rs[0];
            $goodsss['zks'][]=$rs[1];
        }

        $this->assign('filter_spec',$filter_spec);
        $this->assign('goods', $goodsss);
        $pingnum = D('Goodsdianping')->where(array('goods_id' => $goods_id, 'show_date' => array('ELT', TODAY)))->count();
        $this->assign('pingnum', $pingnum);
        $score = (int) D('Goodsdianping')->where(array('goods_id' => $goods_id))->avg('score');
        if ($score == 0) {
            $score = 5;
        }
        $this->assign('score', $score);
        if(($detail['is_vs1'] || $detail['is_vs2'] || $detail['is_vs3'] || $detail['is_vs4'] || $detail['is_vs5'] || $detail['is_vs6']) ==1 ){
            $this->assign('is_vs', $is_vs = 1);
        }else{
            $this->assign('is_vs', $is_vs = 0);
        }
        $goods = D('Goods')->where(array('audti'=>1,'closed'=>0,'end_date' =>  array('EGT', TODAY)))->limit(8)->select();
        $this->assign('like_goods',$goods);
        $this->assign('pics', D('Goodsphoto')->getPics($detail['goods_id']));
        $this->assign('count_goodsfavorites',$count_goodsfavorites = D('Goodsfavorites')->where(array('goods_id'=>$detail['goods_id']))->count());
        $this->assign('goodsfavorites', $goodsfavorites = D('Goodsfavorites')->check($goods_id, $this->uid));//检测自己是不是收藏
        $map_coupon_where = array('shop_id' =>$detail['shop_id'], 'audit' => 1,'closed' => 0, 'expire_date' => array('EGT', TODAY));
        $this->assign('coupon',$coupon = D('Coupon')->order('coupon_id desc ')->where($map_coupon_where )->limit(0, 2)->select());
        $this->assign('mall_coupon_null',$mall_coupon_null = D('Coupon')->order('coupon_id desc ')->where($map_coupon_where)->count());

        $this->assign('goods_attribute',$goods_attribute = M('tpGoodsAttribute')->getField('attr_id,attr_name'));//属性值
        $this->assign('goods_attr_list',$goods_attr_list = M('tpGoodsAttr')->where("goods_id = $goods_id")->select());//属性列表
        $this->display();
    }
    public function detail1($goods_id) {
        $goods_id = (int) $goods_id;
        if (empty($goods_id)) {
            $this->error('工作不存在');
        }
        if (!($detail = D('Goods')->find($goods_id))) {
            $this->error('工作不存在');
        }
        if ($detail['closed'] != 0 || $detail['audit'] != 1) {
            $this->error('工作不存在');
        }
        $shop_id = $detail['shop_id'];


        $this->assign('recom',$recom = D('Goods')->where(array('shop_id' => $shop_id,'audit'=>1,'closed'=>0,'goods_id' => array('neq', $goods_id),'end_date' => array('EGT', TODAY)))->limit(0, 5)->select());


        $this->assign('detail', $detail);
        $this->assign('shop', D('Shop')->find($shop_id));
        $filter_spec = $this->get_spec($goods_id); //获取工作规格参数
        $goodsss=M('Goods')->find($goods_id);
        $goodsss[mall_price]=$goodsss[mall_price]/100;
        $spec_goods_price  = M('TpSpecGoodsPrice')->where("goods_id = $goods_id")->getField("key,price,store_count"); // 规格 对应 价格 库存表
        if($spec_goods_price != null){
            $this->assign('spec_goods_price', json_encode($spec_goods_price,true)); // 规格 对应 价格 库存表
        }
        $yh=$goodsss[yh];
        if($yh!= '0'){
            $yh=explode(PHP_EOL,$yh);
            for ($i=0; $i < count($yh)-1;$i++){
                $yh[s][]=explode(',',$yh[$i]);
            }
            foreach($yh[s] as $k2=>$vo){
                foreach($vo as $k2=>$v2){
                    $rs[$k2][] = $v2;
                }
            }
            $goodsss['zks'][]=$rs[0];
            $goodsss['zks'][]=$rs[1];
        }

        $this->assign('filter_spec',$filter_spec);
        $this->assign('goods', $goodsss);
        $pingnum = D('Goodsdianping')->where(array('goods_id' => $goods_id, 'show_date' => array('ELT', TODAY)))->count();
        $this->assign('pingnum', $pingnum);
        $score = (int) D('Goodsdianping')->where(array('goods_id' => $goods_id))->avg('score');
        if ($score == 0) {
            $score = 5;
        }
        $this->assign('score', $score);
        if(($detail['is_vs1'] || $detail['is_vs2'] || $detail['is_vs3'] || $detail['is_vs4'] || $detail['is_vs5'] || $detail['is_vs6']) ==1 ){
            $this->assign('is_vs', $is_vs = 1);
        }else{
            $this->assign('is_vs', $is_vs = 0);
        }
        $goods = D('Goods')->where(array('audti'=>1,'closed'=>0,'end_date' =>  array('EGT', TODAY)))->limit(8)->select();
        $this->assign('like_goods',$goods);
        $this->assign('pics', D('Goodsphoto')->getPics($detail['goods_id']));
        $this->assign('count_goodsfavorites',$count_goodsfavorites = D('Goodsfavorites')->where(array('goods_id'=>$detail['goods_id']))->count());
        $this->assign('goodsfavorites', $goodsfavorites = D('Goodsfavorites')->check($goods_id, $this->uid));//检测自己是不是收藏
        $map_coupon_where = array('shop_id' =>$detail['shop_id'], 'audit' => 1,'closed' => 0, 'expire_date' => array('EGT', TODAY));
        $this->assign('coupon',$coupon = D('Coupon')->order('coupon_id desc ')->where($map_coupon_where )->limit(0, 2)->select());
        $this->assign('mall_coupon_null',$mall_coupon_null = D('Coupon')->order('coupon_id desc ')->where($map_coupon_where)->count());

        $this->assign('goods_attribute',$goods_attribute = M('tpGoodsAttribute')->getField('attr_id,attr_name'));//属性值
        $this->assign('goods_attr_list',$goods_attr_list = M('tpGoodsAttr')->where("goods_id = $goods_id")->select());//属性列表
        $this->display();
    }

    public function get_spec($goods_id){
        //工作规格 价钱 库存表 找出 所有 规格项id
        $keys = M('TpSpecGoodsPrice')->where("goods_id = $goods_id")->getField("GROUP_CONCAT(`key` SEPARATOR '_') ");
        $filter_spec = array();
        if($keys){
            //$specImage =  M('TpSpecImage')->where("goods_id = $goods_id and src != '' ")->getField("spec_image_id,src");// 规格对应的 图片表， 例如颜色
            $keys = str_replace('_',',',$keys);
            $sql  = "SELECT a.name,a.order,b.* FROM __PREFIX__tp_spec AS a INNER JOIN __PREFIX__tp_spec_item AS b ON a.id = b.spec_id WHERE b.id IN($keys) ORDER BY a.order";
            $filter_spec2 = M()->query($sql);
            foreach($filter_spec2 as $key => $val){
                $filter_spec[$val['name']][] = array(
                    'item_id'=> $val['id'],
                    'item'=> $val['item'],
                );
            }
        }
        return $filter_spec;
    }

    public function cartdel(){
        $goods_spec = $_POST['goods_spec'];
        $goods_spec_all = cookie('goods_spec');
        if (isset($goods_spec_all[$goods_spec])) {
            unset($goods_spec_all[$goods_spec]);
            cookie('goods_spec', $goods_spec_all, 604800);
            $this->ajaxReturn(array('status' => 'success', 'msg' => '删除成功'));
        } else {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '删除失败'));
        }
    }

    public function cartdel2(){
        $goods_id = (int) $this->_get('goods_id');
        $goods = cookie('goods');
        if (isset($goods[$goods_id])) {
            unset($goods[$goods_id]);
            cookie('goods', $goods);
        }
        header('Location:' . U('mall/cart'));
    }
    public function neworder(){
        $goods = $this->_get('goods');
        $goods = explode(',', $goods);
        if (empty($goods)) {
            $this->error('亲购买点吧');
        }
        $datas = array();
        foreach ($goods as $val) {
            $good = explode('-', $val);
            $good[1] = (int) $good[1];
            if (empty($good[0]) || empty($good[1])) {
                $this->error('亲购买点吧');
            }
            if ($good[1] > 99 || $good[1] < 0) {
                $this->error('本店不支持批发');
            }
            $datas[$good[0]] = $good[1];
        }
        cookie('goods', $datas);
        header('Location:' . U('mall/cart'));
        die;
    }
    public function order() {
        if (empty($this->uid)) {
            $this->tuMsg('请先登陆', U('passport/login'));
        }
        $user_integral = D("users")->field('integral')->find($this->uid);
        $num = $this->_post('num', false);
        $goods_ids = array();
        foreach ($num as $k => $val) {
            $val = (int) $val;
            if (empty($val)) {
                unset($num[$k]);
            } elseif ($val < 1 || $val > 99) {
                unset($num[$k]);
            } else {
                $spec_keys[]=$k;
                $spec_arr[] = explode('|',$k);
                $spec_temp = explode('|',$k);
                $goods_ids[$k][] = (int)$spec_temp[0];
            }
        }

        foreach($goods_ids as $v){
            $goods[] = D('Goods')->itemsByIds($v);
        }
        foreach ($goods as $k => $v) {
            foreach($v as $key => $val){
                if ($val['closed'] != 0 || $val['audit'] != 1 || $val['end_date'] < TODAY) {
                    unset($goods[$key]);
                }
                //把这个工作的规格存进数组
                $goods[$k][$key][sky]=$spec_arr[$k][1]; //把后面的规格存进来 148_150
                $goods[$k][$key]['goods_spec'] = $spec_keys[$k];//整个存一下
                if(!empty($goods[$k][$key][sky])){
                    //改变价格
                    $spt=D('TpSpecGoodsPrice')->where("`key`='{$goods[$k][$key][sky]}' and `goods_id`='{$goods[$k][$key][goods_id]}'")->find();
                    $goods[$k][$key]['mall_price']=$spt['price']*100;
                    $goods[$k][$key]['key_name']=$spt['key_name'];//建的中文名
                }
            }
        }

        if (empty($goods)) {
            $this->tuMsg('很抱歉，您提交的产品暂时不能购买');
        }
        //下单前检查库存
        foreach ($goods as $val) {
            $val = reset($val);
            //二维数组 取第一个
            //加入购物车时候检查规格库存  如果不走这里他会走下面的
            $is_spec_stock = is_spec_stock($val[goods_id],$val[sky],$num[$val['goods_spec']]);
            if(!$is_spec_stock){
                $spec_one_num =  get_one_spec_stock($val[goods_id],$val[sky]);
                $this->tuMsg('亲！规格为<' . $val['key_name']. '>的工作库存不够了,只剩' . $spec_one_num . '件了');
            }

            if ($val['num'] < $num[$val['goods_spec']]) {
                $this->tuMsg('亲！工作<' . $val['title'] . '>库存不够了,只剩' . $val['num'] . '件了');
            }
        }


        $tprice = 0;
        $all_integral = $total_mobile = 0;
        $ip = get_client_ip();
        $total_canuserintegral = $ordergoods = $total_price = array();
        foreach ($goods as $val) {
            $val = reset($val);
            //二维数组 取第一个
            //二次开发的 其他人可能看不懂 之前是  $num[$val['goods_id']]  这个我前面那个num已经改过了 但是下面的代码不想改了 所以统一赋值一下
            //前面已经通过这个规格的键值来重新传了
            $num[$val['goods_id']] = $num[$val['goods_spec']];
            $price = $val['mall_price'] * $num[$val['goods_id']];
            $js_price = $val['settlement_price'] * $num[$val['goods_id']];
            $mobile_fan = $val['mobile_fan'] * $num[$val['goods_id']]; //每个工作的手机减少的钱
            $canuserintegral = $val['use_integral'] * $num[$val['goods_id']];
            $order_express_price = D('Ordergoods')->calculation_express_price($this->uid,$val['kuaidi_id'], $num[$val['goods_id']],$val['goods_id'],0);
            //返回单个工作运费
            $m_price = $price - $mobile_fan;
            $tprice += $m_price;
            $total_mobile += $mobile_fan;
            $all_integral += $canuserintegral;

            $ordergoods[$val['shop_id']][] = array(
                'goods_id' => $val['goods_id'],
                'shop_id' => $val['shop_id'],
                'num' => $num[$val['goods_id']],
                'kuaidi_id' => $val['kuaidi_id'],
                'price' => $val['mall_price'],
                'total_price' => $price,
                'mobile_fan' => $mobile_fan,
                'express_price' => $order_express_price, //单个工作运费总价
                'is_mobile' => 1,
                'js_price' => $js_price,
                'create_time' => NOW_TIME,
                'create_ip' => $ip,
                'key'=> $val['sky'],
                'key_name' => $val['key_name']
            );
            $total_canuserintegral[$val['shop_id']] += $canuserintegral; //不同企业可使用积分
            $total_price[$val['shop_id']] += $price; //不同企业的总价格
            $express_price[$val['shop_id']] += $order_express_price; //不同企业总运费
            $mm_price[$val['shop_id']] += $mobile_fan;  //不同企业的手机下单立减

        }
        $order = array('user_id' => $this->uid, 'create_time' => NOW_TIME, 'create_ip' => $ip, 'is_mobile' => 1);
        $tui = cookie('tui');
        if (!empty($tui)) {
            $tui = explode('_', $tui);
            $tuiguang = array('uid' => (int) $tui[0], 'goods_id' => (int) $tui[1]);
        }
        $defaultAddress = D('Paddress')->defaultAddress($this->uid,$type);//收货地址部分重写
        $order_ids = array();
        foreach ($ordergoods as $k => $val) {


            $order['shop_id'] = $k;
            $order['total_price'] = $total_price[$k];
            $order['mobile_fan'] = $mm_price[$k];
            $order['can_use_integral'] = $total_canuserintegral[$k];
            $order['express_price'] = $express_price[$k];//写入运费
            $order['address_id'] = $defaultAddress['id'];//写入快递ID

            $val[0]['express_price'] = $express_price[$k];//写入运费,蜂蜜7月30日二开
            $val[0]['address_id'] = $defaultAddress['id'];//写入快递,蜂蜜7月30日二开
            $shop = D('Shop')->find($k);
            $order['is_shop'] = (int) $shop['is_goods_pei'];
            if ($order_id = D('Order')->add($order)) {//这里写入订单表了
                $order_ids[] = $order_id;

                foreach ($val as $k1 => $val1) {
                    $Goods = D('Goods')->find($val1['goods_id']);
                    $val1['cate_id'] = $Goods['cate_id'];
                    $val1['weight'] = $Goods['weight'];
                    $val1['order_id'] = $order_id;

                    if(!empty($tuiguang)) {
                        if ($tuiguang['goods_id'] == $val1['goods_id']) {
                            $val1['tui_uid'] = $tuiguang['uid'];
                        }
                    }
                    D('Ordergoods')->add($val1);
                }
            }
        }
        cookie('goods_spec', null);// 清空 cookie
        if (count($order_ids) > 1) {
            $need_pay = D('Order')->useIntegral($this->uid, $order_ids);

            $logs = array(
                'type' => 'goods',
                'user_id' => $this->uid,
                'order_id' => 0,
                'order_ids' => join(',', $order_ids),
                'code' => '',
                'need_pay' => $need_pay,
                'create_time' => NOW_TIME,
                'create_ip' => get_client_ip(),
                'is_paid' => 0
            );
            $logs['log_id'] = D('Paymentlogs')->add($logs);
            $this->tuMsg('合并下单成功，接下来选择支付方式和配送地址', U('mall/paycode', array('log_id' => $logs['log_id'])));
        } else {
            $this->tuMsg('下单成功，接下来选择支付方式和配送地址', U('mall/pay', array('order_id' => $order_id,'address_id'=>$defaultAddress['id'])));
        }
        die;
    }
    public function paycode(){
        $log_id = (int) $this->_get('log_id');
        if (empty($log_id)) {
            $this->error('没有有效支付记录');
        }
        if (!($detail = D('Paymentlogs')->find($log_id))) {
            $this->error('没有有效的支付记录');
        }
        if ($detail['is_paid'] != 0 || empty($detail['order_ids']) || !empty($detail['order_id']) || empty($detail['need_pay'])) {
            $this->error('没有有效的支付记录');
        }
        $order_ids = explode(',', $detail['order_ids']);
        $ordergood = D('Ordergoods')->where(array('order_id' => array('IN', $order_ids)))->select();
        $goods_id = $shop_ids = array();
        foreach ($ordergood as $k => $val) {
            $goods_id[$val['goods_id']] = $val['goods_id'];
            $shop_ids[$val['shop_id']] = $val['shop_id'];
        }
        $this->assign('goods', D('Goods')->itemsByIds($goods_id));
        $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        $this->assign('ordergoods', $ordergood);
        //收货地址部分重写
        $defaultAddress = D('Paddress')->defaultAddress($this->uid,$type);
        $changeAddressUrl = "http://" . $_SERVER['HTTP_HOST'] . U('address/addlist', array('type' => goods, 'log_id' => $log_id));
        $this -> assign('defaultAddress', $defaultAddress);
        $this -> assign('changeAddressUrl', $changeAddressUrl);
        //重写结束
        $this->assign('payment', D('Payment')->getPayments(true));
        $this->assign('logs', $detail);
        $this->display();
    }
    public function pay(){
        if (empty($this->uid)) {
            $this->error('登录状态失效!', U('passport/login'));
            die;
        }
        $this->check_mobile();
        cookie('goods', null); //销毁cookie
        $order_id = (int) $this->_get('order_id');
        $order = D('Order')->find($order_id);
        if (empty($order) || $order['status'] != 0 || $order['user_id'] != $this->uid) {
            $this->error('该订单不存在');
            die;
        }

        $ordergood = D('Ordergoods')->where(array('order_id' => $order_id))->select();
        $goods_id = $shop_ids = array();
        foreach ($ordergood as $k => $val) {
            $goods_id[$val['goods_id']] = $val['goods_id'];
            $shop_ids[$val['shop_id']] = $val['shop_id'];
        }
        $this->assign('goods', D('Goods')->itemsByIds($goods_id));
        $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        $this->assign('ordergoods', $ordergood);

        //收货地址部分重写
        if (false == $defaultAddress = D('Paddress')->order_address_id($this->uid,$order_id)) {
            $this->error('获取用户地址出错，请先去会员中心添加商城地址后下单');
        }
        $changeAddressUrl = "http://" . $_SERVER['HTTP_HOST'] . U('address/addlist', array('type' => goods, 'order_id' => $order_id));
        $this -> assign('defaultAddress', $defaultAddress);
        $this -> assign('changeAddressUrl', $changeAddressUrl);
        //重写结束
        //如果没有优惠劵ID就去获取开始
        if(!empty($order['download_id'])){
            $this->assign('download_id', $order['download_id']);
        }else{
            $this->assign('coupon', $coupon = D('Coupon')->Obtain_Coupon($order_id,$this->uid));
        }
        $coupon = D('Coupon')->Obtain_Coupon($order_id,$this->uid);
        //获取优惠劵ID结束
        $this->assign('use_integral', $use_integral = D('Order')->GetUseIntegral($this->uid, array($order_id)));//预算积分抵扣
        $Paymentlogs = D('Paymentlogs')->getLogsByOrderId('goods', $order_id);
        if($Paymentlogs['need_pay']){
            $need_pay = $Paymentlogs['need_pay'];
        }else{
            $need_pay = $order['total_price'] + $order['express_price'] -$coupon['reduce_price'] - $use_integral - $order['mobile_fan'];
        }
        $this->assign('need_pay', $need_pay);
        $this->assign('order', $order);
        $this->assign('payment', D('Payment')->getPayments(true));
        $this->display();
    }
    public function paycode2(){
        //这里是因为原来的是按订单付，这里是合并付款逻辑部分
        $log_id = (int) $this->_get('log_id');
        if (empty($log_id)) {
            $this->tuMsg('没有有效支付记录');
        }
        if (!($detail = D('Paymentlogs')->find($log_id))) {
            $this->tuMsg('没有有效的支付记录');
        }
        if ($detail['is_paid'] != 0 || empty($detail['order_ids']) || !empty($detail['order_id']) || empty($detail['need_pay'])) {
            $this->tuMsg('没有有效的支付记录');
        }
        $order_ids = explode(',', $detail['order_ids']);
        //这里合并付款逻辑暂时不做1，做留言系统，2，做优惠劵ID，3;优惠劵减去的金额
        D('Order')->where(array('order_id' => array('IN', $order_ids)))->save(array('addr_id' => $addr_id));
        /**********************蜂蜜 修复合并付款的时候的系列订单错误问题*****************************/
        $orders = D('order')->where(array('order_id' => array('IN', $order_ids)))->select();
        foreach ($orders as $k => $val) {
            $need_pay[$val[order_id]] = $val['total_price'] - $val['mobile_fan'] - $val['use_integral'];
            D('Order')->where(array('order_id' => $val['order_id']))->save(array('need_pay' => $need_pay[$val[order_id]]));
        }
        /*****************************************************/
        if (!($code = $this->_post('code'))) {
            $this->tuMsg('请选择支付方式');
        }
        if ($code == 'wait') {
            //如果是货到付款
            D('Order')->save(array('is_daofu' => 1, 'status' => 1), array('where' => array('order_id' => array('IN', $order_ids))));
            D('Ordergoods')->save(array('is_daofu' => 1, 'status' => 1), array('where' => array('order_id' => array('IN', $order_ids))));
            D('Order')->mallSold($order_ids);//更新销量
            D('Order')->mallPeisong(array($order_ids), 1);//更新配送
            D('Sms')->mallTZshop($order_ids);//用户下单通知企业
            D('Order')->combination_goods_print($order_ids);//多企业订单打印
            $this->tuMsg('恭喜您下单成功', U('user/goods/index'));
        } else {
            $payment = D('Payment')->checkPayment($code);
            if (empty($payment)) {
                $this->tuMsg('该支付方式不存在');
            }
            //蜂蜜二开合并付款开始
            foreach($order_ids as $v){
                $need_pay = D('Order')->useIntegral($this->uid, array($v));//这个不知道能不能返回
                D('Order')->where("order_id={$v}")->save(array('need_pay' => $need_pay));//合并付款的时候更新实际付款金额
                $log_need +=$need_pay;
            }
            $detail['need_pay']= $log_need;
            $detail['code'] = $code;
            //蜂蜜二开合并付款结束
            $detail['code'] = $code;
            D('Paymentlogs')->save($detail);
            $this->tuMsg('订单设置完成，即将进入付款。', U('mall/combine', array('log_id' => $detail['log_id'])));
        }
    }
    public function combine(){
        if (empty($this->uid)) {
            $this->error('登录状态失效!', U('passport/login'));
            die;
        }
        $log_id = (int) $this->_get('log_id');
        if (!($detail = D('Paymentlogs')->find($log_id))) {
            $this->error('没有有效的支付记录');
        }

        if ($detail['is_paid'] != 0 || empty($detail['order_ids']) || !empty($detail['order_id']) || empty($detail['need_pay'])) {
            $this->error('没有有效的支付记录');
        }
        $this->assign('button', D('Payment')->getCode($detail));
        $this->assign('logs', $detail);
        $this->display();
    }
    //付款
    public function pay2(){
        if (empty($this->uid)) {
            $this->error('登录状态失效!', U('passport/login'));
            die;
        }
        $obj = D('Order');
        $order_id = (int) $this->_get('order_id');
        $order = $obj ->find($order_id);
        if (empty($order) || $order['status'] != 0 || $order['user_id'] != $this->uid) {
            $this->tuMsg('该订单不存在');
        }

        $address_id = isset($_GET['address_id']) ? intval($_GET['address_id']) : $order['address_id'];//检测配送地址ID
        if (empty($address_id)) {
            $this->tuMsg('配送的地址异常');
        }else{
            $obj ->save(array('order_id' =>$order_id,'address_id' =>$address_id));
        }

        //添加优惠劵满减的优惠劵
        $download_id = (int) $this->_post('download_id');
        if(!empty($download_id)){
            $coupon_price = D('Coupon')->Obtain_Coupon_Price($order_id,$download_id);
            if(!empty($coupon_price)){
                $obj ->where("order_id={$order_id}")->save(array('coupon_price' =>$coupon_price,'download_id' =>$download_id));
            }
        }
        //优惠劵结束
        if(!($code = $this->_post('code'))){
            $this->tuMsg('请选择支付方式');
        }
        $this->goods_mum($order_id);//检测库存
        $address = D('Paddress')->where(array('address_id' => $order['address_id']))->find();
        if ($code == 'wait'){
            //如果是货到付款
            $obj ->save(array('order_id' => $order_id, 'status' => 1,'is_daofu' => 1));
            D('Ordergoods')->save(array('is_daofu' => 1,'status' => 1), array('where' => array('order_id' => $order_id)));
            $obj ->mallSold($order_id);//更新销量
            $obj ->mallPeisong(array($order_id), 1);//更新配送
            D('Sms')->mallTZshop($order_id);//用户下单通知企业
            $obj ->combination_goods_print($order_id);//万能商城订单打印
            D('Weixintmpl')->weixin_notice_goods_user($order_id,$this->uid,0);//商城微信通知
            $this->tuMsg('恭喜您下单成功', U('user/goods/index'));
        }else{
            $payment = D('Payment')->checkPayment($code);
            if(empty($payment)){
                $this->tuMsg('该支付方式不存在');
            }


            $logs = D('Paymentlogs')->getLogsByOrderId('goods', $order_id); //写入支付记录


            if($order['is_change'] != 1){
                $need_pay = $obj->useIntegral($this->uid, array($order_id));//更新支付结果,这里加了配送费，这里是没改价的状态，这里改变的是积分状态
            }else{
                $need_pay = $order['need_pay'];//如果是改价的扫码都不加
            }


            if(empty($logs)){
                $logs = array(
                    'type' => 'goods',
                    'user_id' => $this->uid,
                    'order_id' => $order_id,
                    'code' => $code,
                    'need_pay' => $need_pay,
                    'create_time' => NOW_TIME,
                    'create_ip' => get_client_ip(),
                    'is_paid' => 0
                );
                //单个付款走的这里，为什么没写入数据库
                $logs['log_id'] = D('Paymentlogs')->add($logs);
            }else{
                $logs['need_pay'] = $need_pay;
                $logs['code'] = $code;
                D('Paymentlogs')->save($logs);
            }

            $obj ->where("order_id={$order_id}")->save(array('need_pay' => $need_pay));//再更新一次最终的价格，蜂蜜独创
            D('Weixintmpl')->weixin_notice_goods_user($order_id,$this->uid,1);//商城微信通知
            $this->tuMsg('订单设置完成，即将进入付款。', U('payment/payment', array('log_id' => $logs['log_id'])));
        }
    }
    //团购点评
    public function dianping(){
        $goods_id = (int) $this->_get('goods_id');
        if(!($detail = D('Goods')->find($goods_id))){
            $this->error('没有该工作');
            die;
        }
        if($detail['closed']){
            $this->error('该工作已经被删除');
            die;
        }

        $this->assign('next', LinkTo('mall/dianpingloading', $linkArr, array('goods_id' => $goods_id, 't' => NOW_TIME, 'p' => '0000')));
        $this->assign('detail', $detail);
        $this->display();
    }


    public function dianpingloading(){
        $goods_id = (int) $this->_get('goods_id');
        if(!($detail = D('Goods')->find($goods_id))){
            die('0');
        }
        if($detail['closed']){
            die('0');
        }
        $Goodsdianping = D('Goodsdianping');
        import('ORG.Util.Page');
        $map = array('closed' => 0, 'goods_id' => $goods_id, 'show_date' => array('ELT', TODAY));
        $count = $Goodsdianping->where($map)->count();
        $Page = new Page($count, 5);
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if($Page->totalPages < $p){
            die('0');
        }
        $list = $Goodsdianping->where($map)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = $orders_ids = array();
        foreach($list as $k => $val){
            $user_ids[$val['user_id']] = $val['user_id'];
            $orders_ids[$val['order_id']] = $val['order_id'];
        }
        if(!empty($user_ids)){
            $this->assign('users', D('Users')->itemsByIds($user_ids));
        }
        if(!empty($orders_ids)){
            $this->assign('pics', D('Goodsdianpingpics')->where(array('order_id' => array('IN', $orders_ids)))->select());
        }
        $this->assign('totalnum', $count);
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('detail', $detail);
        $this->display();
    }



    //点评详情
    public function img(){
        $order_id = (int) $this->_get('order_id');
        if(!($detail = D('Goodsdianping')->find($order_id))){
            $this->error('没有该点评');
            die;
        }
        if($detail['closed']){
            $this->error('该点评已经被删除');
            die;
        }
        $list =  D('Goodsdianpingpics')->where(array('order_id' => $order_id))->select();
        $this->assign('list', $list);
        $this->assign('detail', $detail);
        $this->display();
    }

    //付款前检测库存
    public function goods_mum($order_id){
        $order_id = (int) $order_id;
        $ordergoods_ids = D('Ordergoods')->where(array('order_id' => $order_id))->select();
        foreach($ordergoods_ids as $k => $v){
            $goods_num = D('Goods')->where(array('goods_id' => $v['goods_id']))->find();
            //也得检查下那个多规格的 这里
            $is_spec_stock = is_spec_stock($v[goods_id],$v[key],$v['num']);
            if(!$is_spec_stock){
                $spec_one_num =  get_one_spec_stock($v[goods_id],$v[key]);
                $this->tuMsg('亲！规格为<' . $v['key_name']. '>的工作库存不够了,只剩' . $spec_one_num . '件了');
            }
            if($goods_num['num'] < $v['num']){
                $this->tuMsg('工作ID' . $v['goods_id'] . '库存不足无法付款',U('user/goods/index',array('aready'=>1)));;
            }
        }
        return false;
    }
    public function jobsign(){
        $goods_id = $this->_param('goods_id');
        $good = D('goods')->find($goods_id);
        if($this->isPost()){
            if (empty($this->uid)) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '请先登录！','url'=>U('passport/login')));
            }
            $user = D('Users')->where(array('user_id'=>$this->uid))->find();
            if (empty($user)) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '请先登录！','url'=>U('passport/login')));
            }
            $jobApply = D('jobApply')->where(array('user_id'=>$this->uid,'goods_id'=>$goods_id))->find();
            if(!empty($jobApply)){
                $this->ajaxReturn(array('status' => 'error', 'msg' => '您已经报名，请勿重复报名！','url'=>U('mall/index')));
            }

//            是否已经完善信息 姓名手机号身份证号 修改为只验证手机号
            $detail = D('Usersaux')->find($this->uid);
            if(empty($user['mobile'])){
                $this->ajaxReturn(array('status' => 'merror', 'msg' => '您尚未完善手机号！'));
            }
            $data['user_id']=$this->uid;
            $data['goods_id'] = $goods_id;
            $data['creat_time'] = time();
            $data['closed'] = 0;
            $data['is_vip'] = 1;//是否vip报名 1否 0是
            //是否是VIP报名，0普通报名，1VIP报名
            $is_vip = $this->_param('is_vip');

            include_once 'Tudou/Lib/Net/Wxmesg.class.php';

            if($is_vip){
                $data['is_vip'] = 0;
                if($user['rand_id']==0){
                    $this->ajaxReturn(array('status' => 'error', 'msg' => '您还不是VIP,无法享受高价哦！！','url'=>U('wap/job/vipRouter')));
                }else{
                    if($apply_id =D('jobApply')->add($data)){
                        D('Weixinmsg')->sign_success($user['user_id'],$good['title']);
                        $this->ajaxReturn(array('status' => 'success', 'msg' => '恭喜您，报名成功！','url'=>U('user/job/signInfo',array('goods_id'=>$goods_id))));
                    }else{
                        $this->ajaxReturn(array('status' => 'error', 'msg' => '报名失败，请稍后重试！','url'=>U('wap/mall/index')));
                    }
                }
            }else{
                if($apply_id =D('jobApply')->add($data)){
                    D('Weixinmsg')->sign_success($user['user_id'],$good['title']);
                    $this->ajaxReturn(array('status' => 'success', 'msg' => '恭喜您，报名成功！','url'=>U('user/goods/index',array('aready'=>0,'goods_id'=>$goods_id))));
                }else{
                    $this->ajaxReturn(array('status' => 'error', 'msg' => '报名失败，请稍后重试！','url'=>U('wap/mall/index')));
                }
            }
        }else{
            if (empty($this->uid)) {
                $this->tuMsg('请先登录',U('passport/login'));
            }
            $user = D('Users')->where(array('user_id'=>$this->uid));
            if (empty($user)) {
                $this->tuMsg('请先登录',U('passport/login'));
            }
            $jobApply = D('jobApply')->where(array('user_id'=>$this->uid,'goods_id'=>$goods_id))->find();
            if(empty($jobApply)){
                $this->tuMsg('您尚未报名，无法查看！！',U('mall/index'));
            }
            $this->assign('good',$good);
            $this->display();
        }
    }
    public function sendsms()
    {
        parent::sendsms(); // TODO: Change the autogenerated stub
    }
    public function test(){

        $user = D('Users')->where(array('user_id'=>4))->find();
        $useraux = D('usersaux')->where(array('user_id'=>4))->find();
        $data = array(
            'url' => $this->_CONFIG['site']['host'] . '/user/distribution/subordinate.html',
            'first' => '您好，您已成功报名通知！',
            'keyword1' => $user['nickname'], //昵称
            'keyword2' => $user['mobile'],
            'keyword3' => '报名职位',
            'keyword4' => date('Y-m-d H:i:s',time()),
            'keyword5' => $useraux['card_id'],
            'remark' => '有疑问请致电：0371-03710371',
        );
        $result = D('Weixinmsg')->sign_success(159,'工作岗位');
        dump($result);
        die;
    }
    public function gps($goods_id,$type = '0'){
        $goods_id = (int) $goods_id;
        $type = (int) $this->_param('type');
        if(empty($goods_id)){
            $this->error('该岗位不存在');
        }
        $goods = D('Goods')->find($goods_id);
        $this->assign('goods', $goods);
        $this->assign('type', $type);

        $this->assign('amap', $amap= $this->bd_decrypt($goods['lng'],$goods['lat']));
        $this->display();
    }
    //BD-09(百度) 坐标转换成  GCJ-02(火星，高德) 坐标
    //@param bd_lon 百度经度
    //@param bd_lat 百度纬度
    public function bd_decrypt($bd_lon,$bd_lat){
        $x_pi = 3.14159265358979324 * 3000.0 / 180.0;
        $x = $bd_lon - 0.0065;
        $y = $bd_lat - 0.006;
        $z = sqrt($x * $x + $y * $y) - 0.00002 * sin($y * $x_pi);
        $theta = atan2($y, $x) - 0.000003 * cos($x * $x_pi);
        $data['gg_lon'] = $z * cos($theta);
        $data['gg_lat'] = $z * sin($theta);
        return $data;
    }
}