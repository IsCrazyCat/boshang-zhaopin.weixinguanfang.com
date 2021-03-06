<?php
class GoodstagAction extends CommonAction{
    private $create_fields = array('cate_name','type_id','commId','photo','rate','orderby');
    private $edit_fields = array('cate_name','type_id','commId', 'photo','rate', 'orderby');
	
	public function _initialize(){
        parent::_initialize();
//        $this->assign('commIds',$commIds = D('Goodstag')->getCommIds());//商务部类型
//		$this->assign('goodstypes',$goodstypes = M('TpGoodsType')->select());//全部工作类型
    }
	
	
	
    public function index(){
        $obj = D('Goodstag');
        $list = $obj->fetchAll();
//		foreach($list as $key => $v){
//			$list[$key]['goods_num'] = $obj->getCateGoodsNum($v['cate_id']);
//			$list[$key]['commIdName'] = $obj->getCommIdName($v['commId']);
//			$TpGoodsType = M('TpGoodsType')->where(array('id'=>$v['type_id']))->find();
//			$list[$key]['name'] = $TpGoodsType['name'];
//		}
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function create($parent_id = 0){
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Goodstag');
            $data['parent_id'] = $parent_id;
            if ($obj->add($data)) {
                $obj->cleanCache();
                $this->tuSuccess('添加成功', U('goodstag/index'));
            }
            $this->tuError('操作失败');
        }else{
            $this->assign('parent_id', $parent_id);
            $this->display();
        }
    }
	
	
    public function child($parent_id = 0){
        $datas = D('Goodstag')->fetchAll();
        $str = '';
        foreach ($datas as $var) {
            if ($var['parent_id'] == 0 && $var['cate_id'] == $parent_id) {
                foreach ($datas as $var2) {
                    if ($var2['parent_id'] == $var['cate_id']) {
                        $str .= '<option value="' . $var2['cate_id'] . '">' . $var2['cate_name'] . '(' . $var2['rate'] . ')‰' . '</option>' . '';
                        foreach ($datas as $var3) {
                            if ($var3['parent_id'] == $var2['cate_id']) {
                                $str .= '<option value="' . $var3['cate_id'] . '">  --' . $var3['cate_name'] . '(' . $var3['rate'] . ')‰' . '</option>' . '';
                            }
                        }
                    }
                }
            }
        }
        echo $str;
        die;
    }
    private function createCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['cate_name'] = htmlspecialchars($data['cate_name']);
        if(empty($data['cate_name'])){
            $this->tuError('名称不能为空');
        }
		$data['type_id'] = (int) $data['type_id'];
		$data['commId'] = (int) $data['commId'];
		$data['photo'] = htmlspecialchars($data['photo']);
        $data['orderby'] = (int) $data['orderby'];
        return $data;
    }
    public function edit($cate_id = 0){
        if ($cate_id = (int) $cate_id) {
            $obj = D('Goodstag');
            if (!($detail = $obj->find($cate_id))) {
                $this->tuError('请选择要编辑的企业分类');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['cate_id'] = $cate_id;
                if (false !== $obj->save($data)) {
                    $obj->cleanCache();
                    $this->tuSuccess('操作成功', U('Goodstag/index'));
                }
                $this->tuError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->tuError('请选择要编辑的企业分类');
        }
    }
    private function editCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['cate_name'] = htmlspecialchars($data['cate_name']);
        if(empty($data['cate_name'])){
            $this->tuError('名称不能为空');
        }
		$data['type_id'] = (int) $data['type_id'];
		$data['commId'] = (int) $data['commId'];
		$data['photo'] = htmlspecialchars($data['photo']);
        $data['orderby'] = (int) $data['orderby'];
        return $data;
    }
    public function createone($parent_id = 0){
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Goodstag');
            $data['parent_id'] = $parent_id;
            if ($obj->add($data)) {
                $obj->cleanCache();
                $this->tuSuccess('添加成功', U('Goodstag/index'));
            }
            $this->tuError('操作失败');
        } else {
            $this->assign('parent_id', $parent_id);
            $this->display();
        }
    }
    private function createoneCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['cate_name'] = htmlspecialchars($data['cate_name']);
        if (empty($data['cate_name'])) {
            $this->tuError('名称不能为空');
        }
		$data['photo'] = htmlspecialchars($data['photo']);
        $data['orderby'] = (int) $data['orderby'];
        return $data;
    }
    public function editone($cate_id = 0){
        if ($cate_id = (int) $cate_id) {
            $obj = D('Goodstag');
            if (!($detail = $obj->find($cate_id))) {
                $this->tuError('请选择要编辑的企业分类');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['cate_id'] = $cate_id;
                if (false !== $obj->save($data)) {
                    $obj->cleanCache();
                    $this->tuSuccess('操作成功', U('Goodstag/index'));
                }
                $this->tuError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->tuError('请选择要编辑的企业分类');
        }
    }
    private function editoneCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['cate_name'] = htmlspecialchars($data['cate_name']);
        if (empty($data['cate_name'])) {
            $this->tuError('名称不能为空');
        }
        $data['orderby'] = (int) $data['orderby'];
        return $data;
    }
    public function delete($cate_id = 0){
        if (is_numeric($cate_id) && ($cate_id = (int) $cate_id)) {
            $obj = D('Goodstag');
			if(false == $obj->check_parent_id($cate_id)){
				$this->tuError($obj->getError());
			}
			if(false == $obj->check_cate_id_goods($cate_id)){
				$this->tuError($obj->getError());
			}
            $obj->delete($cate_id);
            $obj->cleanCache();
            $this->tuSuccess('删除成功', U('Goodstag/index'));
        } else {
            $cate_id = $this->_post('cate_id', false);
            if (is_array($cate_id)) {
                $obj = D('Goodstag');
                foreach ($cate_id as $id) {
                    $obj->delete($id);
                }
                $obj->cleanCache();
                $this->tuSuccess('删除成功', U('Goodstag/index'));
            }
            $this->tuError('请选择要删除的企业分类');
        }
    }
    public function delattr($attr_id){
        if (empty($attr_id)) {
            $this->tuError('操作失败');
        }
        if (!($detail = D('Goodstagattr')->find($attr_id))) {
            $this->tuError('操作失败');
        }
        D('Goodstagattr')->delete($attr_id);
        $this->tuSuccess('删除成功', U('Goodstag/setting', array('cate_id' => $detail['cate_id'])));
    }
    public function ajax($cate_id, $goods_id = 0){
        if (!($cate_id = (int) $cate_id)) {
            $this->error('请选择正确的分类');
        }
        if (!($detail = D('Goodstag')->find($cate_id))) {
            $this->error('请选择正确的分类');
        }
        $this->assign('cate', $detail);
        $this->assign('attrs', D('Goodstagattr')->order(array('orderby' => 'asc'))->where(array('cate_id' => $cate_id))->select());
        if ($goods_id) {
            $this->assign('detail', D('Goods')->find($goods_id));
            $this->assign('maps', D('Goodstagattr')->getAttrs($goods_id));
        }
        $this->display();
    }
    public function setting($cate_id){
        if (!($cate_id = (int) $cate_id)) {
            $this->error('请选择正确的分类');
        }
        if (!($detail = D('Goodstag')->find($cate_id))) {
            $this->error('请选择正确的分类');
        }
        if ($this->isPost()) {
            $obj = D('Goodstagattr');
            $data = $this->_post('data', false);
            foreach ($data as $key => $val) {
                foreach ($val as $k => $v) {
                    if (!empty($v['attr_name'])) {
                        $obj->add(array('cate_id' => $cate_id, 'type' => htmlspecialchars($key), 'attr_name' => htmlspecialchars($v['attr_name']), 'orderby' => (int) $v['orderby']));
                    }
                }
            }
            $old = $this->_post('old', false);
            foreach ($old as $key => $val) {
                $obj->save(array('attr_id' => (int) $key, 'attr_name' => htmlspecialchars($val['attr_name']), 'orderby' => (int) $val['orderby']));
            }
            $this->tuSuccess('操作成功', U('Goodstag/setting', array('cate_id' => $cate_id)));
        } else {
            $this->assign('detail', $detail);
            $this->assign('attrs', D('Goodstagattr')->order(array('orderby' => 'asc'))->where(array('cate_id' => $cate_id))->select());
            $this->display();
        }
    }
    public function update(){
        $orderby = $this->_post('orderby', false);
        $obj = D('Goodstag');
        foreach ($orderby as $key => $val) {
            $data = array('cate_id' => (int) $key, 'orderby' => (int) $val);
            $obj->save($data);
        }
        $obj->cleanCache();
        $this->tuSuccess('更新成功', U('Goodstag/index'));
    }
}