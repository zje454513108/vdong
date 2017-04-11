<?php 
namespace Commission\Model;
use Think\Model;

class EweiShopMemberModel extends Model{

   /*
    根据传入的where条件进行查找 
    Input:
        uniacid 商户ID
        openid 用户ID
      
    Output:
        
        data 微信支付的信息
   */
    public function screeningData($where){
      $data=$this->where($where)->find();
      return $data;
     }
     public function getMember($uniacid,$openid,$field,$getCredit = false){
        $uid = intval($openid);
        if (empty($uid)) {
            $where['uniacid'] = $uniacid;
            $where['openid'] = $openid;
            $info = $this->field($field)->where($where)->find();
            
        } else {
            $where['uniacid'] = $uniacid;
            $where['id'] = $openid;
            $info = $this->field($field)->where($where)->find();
        }
        if ($getCredit) {
            $info['credit1'] = $this->getCredit($openid, 'credit1');
            $info['credit2'] = $this->getCredit($openid, 'credit2');
        }
        return $info;
    }
     //新增
    public function insert($data){
       $result= $this->add($data);
       return $result;
    }
    // 更新
    public function update($where,$data){
       $result= $this->where($where)->save($data);
       return $result;
    }
    // 查询多条信息
    public function getListByWhere($where,$field,$order){
       $field = $field ? :'*';
       $order = $order ? :'';
       $list= $this->field($field)->where($where)->order($order)->select();
       return $list;
    }
    // 查询单条信息
    public function getItemByWhere($where,$field){
       $field = $field ? :'*';
       $query= $this->field($field)->where($where)->find();
       return $query;
    }
    
    /**
     * 获取用户信息(单用户)
     * @param type $token
     * @param type $openid
     * @return string
     */
    public function getInfo($token, $openid) {
        $info = $this->where("uniacid = %d and openid = '%s'",array($token,$openid))->find();
        return $info;
    }
    
    /**
     * 获取分销商
     * @param type $token
     * @param type $openid
     * @return type 返回数组
     */
    public function getAgentArr($token, $openid,$type = '',$val=''){
        $where = array(
            'uniacid'   =>$token,
            'isagent'   =>1,
            'status'    =>1
        );
        if($type == 'in'){
            $where['agentid']   = array('in',$val);
        }else{
            $where['agentid']   =$openid;
        }
        $data = $this
                ->where($where)
                ->getField('id',true);
        return $data;
    }
    
    /**
     * 获取下线的数量
     * @param type $token
     * @param type $openid
     * @return type
     */
    public function getCustomerCount($token,$agentid){
        $count = $this
                ->where("agentid=%d and ((isagent=1 and status=0) or isagent=0) and uniacid=%d",array($agentid,$token))
                ->count();
        return $count;
    }
    
    /**
     * 获取分销商用户id
     * @param type $token
     * @param type $openid
     * @param type $type
     * @param type $val
     * @return type
     */
    public function getAgentinfo($token, $openid,$type = '',$val=''){
        $where = array(
            'uniacid'   =>$token,
        );
        if($type == 'in'){
            $where['agentid']   = array('in',$val);
        }else{
            $where['agentid']   =$openid;
        }
        $data = $this
                ->where($where)
                ->getField('id',true);
        return $data;
    }
}