<?php
class LogsAction extends CommonAction{
 
    public function moneylogs(){
        $this->display();
    }
	
	 public function money_data(){
        $Usermoneylogs = D('Usermoneylogs');
        import('ORG.Util.Page');
        $map = array('user_id' => $this->uid);
        $count = $Usermoneylogs->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
		
		$var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $Usermoneylogs->where($map)->order(array('log_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
	
    public function cashlogs(){
        $Userscash = D('Userscash');
        import('ORG.Util.Page');
        $map = array('user_id' => $this->uid, 'type' => user);
        $count = $Userscash->where($map)->count();
        $Page = new Page($count, 16);
        $show = $Page->show();
        $list = $Userscash->where($map)->order(array('cash_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function integral(){
        $this->display();
    }
	
	 public function integral_data(){
        $subsidyMoneyLogs = D('subsidyMoney');
        import('ORG.Util.Page');
        $map = array('user_id' => $this->uid);
        $count = $subsidyMoneyLogs->where($map)->count();
        $Page = new Page($count, 16);
        $show = $Page->show();
		
		$var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
		
		
        $list = $subsidyMoneyLogs->where($map)->order(array('money_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
	
	 public function prestige(){
        $this->display();
    }
	
	 public function prestige_data(){
        $obj = D('Userprestigelogs');
        import('ORG.Util.Page');
        $map = array('user_id' => $this->uid);
        $count = $obj->where($map)->count();
        $Page = new Page($count, 16);
        $show = $Page->show();
		
		$var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $obj->where($map)->order(array('log_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
	
    public function rechargecard(){
        $Rechargecard = D('Rechargecard');
        import('ORG.Util.Page');
        $map = array('user_id' => $this->uid, 'is_used' => 1);
        $count = $Rechargecard->where($map)->count();
        $Page = new Page($count, 16);
        $show = $Page->show();
        $list = $Rechargecard->where($map)->order(array('card_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
}