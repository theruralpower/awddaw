<?php


class BlockUser extends AppModel
{

    public $useTable = 'block_user';



    public function getDetails($id)
    {
        return $this->find('first', array(
            'conditions' => array(



                'BlockUser.id'=> $id,




            )
        ));
    }

    public function ifAlreadyBlocked($data)
    {
        return $this->find('first', array(
            'conditions' => array(



                'BlockUser.user_id'=> $data['user_id'],
                'BlockUser.block_user_id'=> $data['block_user_id'],




            )
        ));
    }







}
?>