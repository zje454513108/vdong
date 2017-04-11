<?php 
    namespace Base\Model;
    use Think\Model;
    class EweiPrintUserModel extends Model{
    /*
    打印机表添加 
    Input:
        $data 添加数据
        
    Output:
        1       成功
        false   失败
   */
         public function insertPrintUser($data){
           $userList=$this->add($data);
            // dump($userList);exit;
            if ($userList) {
                return $userList;
            }else{

            return false;
            }
        }
    /*
    打印机表查询
    Input:
        where 查询条件
        
    Output:
        List 查到的数据
   */
        public function selectPrintList($where){
           $userList=$this->where($where)->select();
            return $userList;
        }
    /*
    打印机表删除
    Input:
        id 打印表ID
        
    Output:
        result 删除的ID
   */
        public function deletePrintList($where){
           $userList=$this->where($where)->delete();
            return $userList;
        }
    }