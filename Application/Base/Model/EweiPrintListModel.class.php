<?php 
    namespace Base\Model;
    use Think\Model;
    class EweiPrintListModel extends Model{
    /*
    添加打印记录
    Input:
        data 添加数据
        
    Output:
        ID 添加成功的ID
        false 失败返回false
   */
        public function insertPrintList($data){
           $userList=$this->add($data);
            // dump($userList);exit;
            if ($userList) {
                return $userList;
            }else{

            return false;
            }
        }
    /*
    打印记录查询
    Input:
        where 查询条件
        num 从分页的第几条开始
        limit 条数
        
    Output:
        List 查到的数据
   */
        public function selectPrintList($where,$num,$limit){
           $userList=$this->where($where)->order('id')->limit($num,$limit)->select();
            return $userList;
        }
    }