<?php



class Follower extends AppModel
{
    public $useTable = 'follower';


    public $belongsTo = array(
        'FollowingList' => array(
            'className' => 'User',
            'foreignKey' => 'receiver_id',
            //'fields' => array('User.id','User.email','User.username','User.image','User.device_token')

        ),

        'FollowerList' => array(
            'className' => 'User',
            'foreignKey' => 'sender_id',
            //'fields' => array('User.id','User.email','User.username','User.image','User.device_token')

        ),


    );






    public function getAll()
    {

        return $this->find('all',array(


            'order'=>'Follower.id DESC',
            'recursive'=> -1

        ));

    }


    public function getDetails($id){

        return $this->find('first', array(
            'conditions' => array(
                'Follower.id' => $id
            ),

            'recursive' => 0


        ));

    }

    public function ifFollowing($sender_id,$receiver_id){

        return $this->find('first', array(
            'conditions' => array(
                'Follower.sender_id' => $sender_id,
                'Follower.receiver_id' => $receiver_id,
            ),

            'recursive' => 0


        ));

    }
    public function isFollowerOrFollowed($user_id){
        $this->Behaviors->attach('Containable');
        return $this->find('all', array(
            'conditions' => array(

                "OR" => array (
                    "Follower.sender_id" => $user_id,
                    "Follower.receiver_id" => $user_id,

                ),




            ),



            'order'=>'Follower.id DESC',

        ));

    }


    public function countFollowers($user_id){

        return $this->find('count', array(
            'conditions' => array(
                'Follower.receiver_id' => $user_id
            ),

            'recursive' => 0


        ));

    }

    
    public function countFollowing($user_id){

        return $this->find('count', array(
            'conditions' => array(
                'Follower.sender_id' => $user_id
            ),

            'recursive' => 0


        ));

    }

    public function deleteFollowerAgainstUserID($user_id){


        $conditions = array(
            'OR' => array(
                'Follower.sender_id' => $user_id,
                'Follower.receiver_id' => $user_id,
            )
        );

        $this->deleteAll($conditions);
    }



    public function getUserFriends($user_id){
        $this->Behaviors->attach('Containable');
        return $this->find('all', array(
            'conditions' => array(

                "OR" => array (
                    "Friend.user_id" => $user_id,
                    "Friend.friend_id" => $user_id,

                ),

                "AND" => array (
                    "Friend.block_id" => 0


                )


            ),



            'order'=>'Friend.id DESC',

        ));

    }


    public function getUserFollowers($user_id){
        $this->Behaviors->attach('Containable');
        return $this->find('all', array(
            'conditions' => array(

                "Follower.receiver_id" => $user_id,


            ),

            'fields'=>array('FollowerList.*'),



            'order'=>'Follower.id DESC',

        ));

    }
    public function getUserFollowing($user_id){
        $this->Behaviors->attach('Containable');
        return $this->find('all', array(
            'conditions' => array(

                "Follower.sender_id" => $user_id,



            ),

            'fields'=>array('FollowingList.*'),



            'order'=>'Follower.id DESC',

        ));

    }

    public function getALLUserNotFriends($user_id){
        $this->Behaviors->attach('Containable');
        return $this->find('all', array(
            'conditions' => array(

                "AND" => array (
                    "Friend.user_id" => $user_id,
                    "Friend.friend_id" => $user_id,

                ),

                "AND" => array (
                    "Friend.block_id" => 0


                )


            ),



            'order'=>'Friend.id DESC',

        ));

    }

    public function isFriend($user_id,$friend_id){

        return $this->find('first', array(
            'conditions' => array(
                'OR' =>
                    array(
                        array('AND' => array(
                            array('Friend.user_id' => $user_id),
                            array('Friend.friend_id' => $friend_id),
                            array('Friend.block_id' => 0)
                        )),
                        array('AND' => array(
                            array('Friend.user_id' => $friend_id),
                            array('Friend.friend_id' => $user_id),
                            array('Friend.block_id' => 0)
                        )),
                    )),
            'recursive'=> -1


        ));

    }

    public function ifBlocked($user_id,$friend_id){

        return $this->find('count', array(
            'conditions' => array(
                'OR' =>
                    array(
                        array('AND' => array(
                            array('Friend.user_id' => $user_id),
                            array('Friend.friend_id' => $friend_id),
                            array('Friend.block_id !=' => 0)
                        )),
                        array('AND' => array(
                            array('Friend.user_id' => $friend_id),
                            array('Friend.friend_id' => $user_id),
                            array('Friend.block_id !=' => 0)
                        )),
                    )),

            'recursive'=> -1


        ));

    }










}

?>