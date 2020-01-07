<?php
class PhoneAction extends CommonAction{
    public function index(){
        $phones = D('Convenientphonemaps');
        import('ORG.Util.Page');
        $map = array('community_id' => $this->community_id);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['name'] = array('LIKE', '%' . $keyword . '%');
        }
        $phone = $phones->where($map)->select();
        $phone_ids = array();
        foreach ($phone as $val) {
            $phone_ids[$val['phone_id']] = $val['phone_id'];
        }
        $count = $phones->where($map)->count();
        $Page = new Page($count, 16);
        $show = $Page->show();
        if (!empty($phone_ids)) {
            $this->assign('list', D('Convenientphone')->limit($Page->firstRow . ',' . $Page->listRows)->itemsByIds($phone_ids));
        }
        $this->assign('page', $show);
        $this->display();
    }
    public function delete(){
        if (IS_AJAX) {
            $phone_id = (int) $_POST['phone_id'];
            $obj = D('Convenientphonemaps');
            if (empty($phone_id)) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '便民电话不存在'));
            }
            if (!($detail = $obj->find($phone_id))) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '便民电话不存在'));
            }
            if ($detail['community_id'] != $this->community_id) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '不是您小区的'));
            }
            $obj->delete($phone_id);
            $this->ajaxReturn(array('status' => 'success', 'msg' => '删除成功'));
        }
    }
    public function create(){
        if ($this->isPost()) {
            $data = $this->checkCreate();
            $obj = D('Convenientphone');
            if ($phone_id = $obj->add($data)) {
                $this->tuSuccess('添加成功', U('phone/index'));
            }
            $this->tuError('操作失败');
        } else {
            $this->display();
        }
    }
    public function checkCreate()
    {
        $data = $this->checkFields($this->_post('data', false), array('name', 'phone', 'expiry_date', 'orderby'));
        $data['community_id'] = (int) $this->community_id;
        $data['name'] = htmlspecialchars($data['name']);
        $data['phone'] = htmlspecialchars($data['phone']);
        $data['expiry_date'] = htmlspecialchars($data['expiry_date']);
        if (empty($data['name'])) {
            $this->tuError('名称不能为空');
        }
        if (empty($data['phone'])) {
            $this->tuError('电话不能为空');
        }
        if (empty($data['expiry_date'])) {
            $this->tuError('过期时间不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['name'])) {
            $this->tuError('名称含有敏感词：' . $words);
        }
        $data['orderby'] = (int) $data['orderby'];
        return $data;
    }
    public function edit($phone_id){
        if ($phone_id = (int) $phone_id) {
            $obj = D('Convenientphone');
            if (empty($phone_id)) {
                $this->ajaxReturn(array('status' => 'success', 'msg' => '便民电话不存在'));
            }
            if (!($detail = $obj->find($phone_id))) {
                $this->ajaxReturn(array('status' => 'success', 'msg' => '便民电话不存在'));
            }
            $map = array('community_id' => $this->community_id);
            $phones = D('Convenientphonemaps');
            $phone = $phones->where($map)->select();
            $phone_ids = array();
            foreach ($phone as $val) {
                $phone_ids[$val['phone_id']] = $val['phone_id'];
            }
            if (!$phone_ids) {
                $this->tuError('不能操作其他小区电话');
            }
            if ($this->isPost()) {
                $data = $this->checkEdit();
                $data['phone_id'] = $phone_id;
                if (false !== $obj->save($data)) {
                    $obj->cleanCache();
                    $this->tuSuccess('编辑成功', U('phone/index'));
                }
                $this->tuError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->tuError('请选择要编辑的便民电话');
        }
    }
    public function checkEdit(){
        $data = $this->checkFields($this->_post('data', false), array('name', 'phone', 'expiry_date', 'orderby'));
        $data['name'] = htmlspecialchars($data['name']);
        $data['phone'] = htmlspecialchars($data['phone']);
        $data['expiry_date'] = htmlspecialchars($data['expiry_date']);
        if (empty($data['name'])) {
            $this->tuError('名称不能为空');
        }
        if (empty($data['phone'])) {
            $this->tuError('电话不能为空');
        }
        if (empty($data['expiry_date'])) {
            $this->tuError('过期时间不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['name'])) {
            $this->tuError('名称含有敏感词：' . $words);
        }
        $data['orderby'] = (int) $data['orderby'];
        return $data;
    }
}