<?php


class VideoLike extends AppModel
{

    public $useTable = 'video_like';

    public $belongsTo = array(
        'Video' => array(
            'className' => 'Video',
            'foreignKey' => 'video_id',



        ),

        'User' => array(
            'className' => 'User',
            'foreignKey' => 'user_id',



        ),

    );

    public function getDetails($id)
    {
        return $this->find('first', array(
            'conditions' => array(



                'VideoLike.id'=> $id,




            )
        ));
    }

    public function countLikes($video_id)
    {
        return $this->find('count', array(
            'conditions' => array(



                'VideoLike.video_id'=> $video_id,




            )
        ));
    }

    public function countUserAllVideoLikes($user_id)
    {
        return $this->find('count', array(
            'conditions' => array(



                'VideoLike.user_id'=> $user_id,




            )
        ));
    }

   


    public function getUserAllVideoLikes($user_id)
    {
        $this->Behaviors->attach('Containable');
        return $this->find('all', array(
            'contain' => array('Video.Sound','Video.User'),
            'conditions' => array(



                'VideoLike.user_id'=> $user_id,




            )
        ));
    }

    public function ifExist($data)
    {
        return $this->find('first', array(
            'conditions' => array(



                'VideoLike.video_id'=> $data['video_id'],
                'VideoLike.user_id'=> $data['user_id'],




            )
        ));
    }



    public function getAll()
    {
        return $this->find('all');
    }






}
?>