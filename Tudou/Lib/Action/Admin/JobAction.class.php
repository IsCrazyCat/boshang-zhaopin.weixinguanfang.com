<?php
class JobAction extends CommonAction
{
    public function index()
    {
        $JobApply = D('JobApply');
        import('ORG.Util.Page');
        // 导入分页类
        $map = array();
        $keyword = $this->_param('keyword', 'htmlspecialchars');
//        if ($keyword) {
//            $map['name|mobile'] = array('LIKE', '%' . $keyword . '%');
//            $this->assign('keyword', $keyword);
//        }
        $goods_id = (int) $this->_param('goods_id');
        if ($goods_id) {
            $map['goods_id'] = $goods_id;
        }
        $count = $JobApply->where($map)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 25);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $list = $JobApply->where($map)->order(array('id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $key=>$val) {
            $user = D('users')->find($val['user_id']);
            $list[$key]['user'] = $user;
        }
        $this->assign('good', D('Goods')->find($goods_id));
        $this->assign('list', $list);
        // 赋值数据集
        $this->assign('page', $show);
        // 赋值分页输出
        $this->display();
        // 输出模板
    }
    public function delete($sign_id = 0)
    {
        $goods_id = (int) $this->_param('goods_id');
        if (is_numeric($sign_id) && ($sign_id = (int) $sign_id)) {
            $obj = D('JobApply');
            $obj->delete($sign_id);
            $obj->cleanCache();
            $this->tuSuccess('删除成功', U('job/index',array('goods_id'=>$goods_id)));
        } else {
            $sign_id = $this->_post('sign_id', false);
            if (is_array($sign_id)) {
                $obj = D('JobApply');
                foreach ($sign_id as $id) {
                    $obj->delete($id);
                }
                $this->tuSuccess('删除成功', U('job/index',array('goods_id'=>$goods_id)));
            }
            $this->tuError('请选择要删除的报名列表');
        }
    }
    public function audit($sign_id = 0)
    {
        $goods_id = (int) $this->_param('goods_id');
        if (is_numeric($sign_id) && ($sign_id = (int) $sign_id)) {
            $obj = D('JobApply');
            $obj->save(array('id'=>$sign_id,'audit'=>1,'status'=>2));
            $obj->cleanCache();
            $this->tuSuccess('审核成功', U('job/index',array('goods_id'=>$goods_id)));
        } else {
            $sign_id = $this->_post('sign_id', false);
            if (is_array($sign_id)) {
                $obj = D('JobApply');
                foreach ($sign_id as $id) {
                    $obj->save(array('id'=>$sign_id,'audit'=>1,'status'=>2));
                }
                $this->tuSuccess('审核成功', U('job/index',U('job/index',array('goods_id'=>$goods_id))));
            }
            $this->tuError('请选择要审核的报名列表');
        }
    }
}