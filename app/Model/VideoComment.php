<?php


class VideoComment extends AppModel
{

    public $useTable = 'video_comment';

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

    public $hasMany = array(
        'VideoCommentReply' => array(
            'className' => 'VideoCommentReply',
            'foreignKey' => 'comment_id',



        ),
    );

    public function getDetails($id)
    {
        return $this->find('first', array(
            'conditions' => array(



                'VideoComment.id'=> $id,




            )
        ));
    }

    public function countComments($video_id)
    {
        return $this->find('count', array(
            'conditions' => array(



                'VideoComment.video_id'=> $video_id,




            )
        ));
    }

    public function getVideoComments($video_id)
    {
        $this->Behaviors->attach('Containable');
        return $this->find('all', array(
            'conditions' => array(



                'VideoComment.video_id'=> $video_id,




            ),
            'contain'=>array('Video','User','VideoCommentReply.User')
            
        ));
    }

    public function getAll()
    {
        return $this->find('all');
    }






}
?>