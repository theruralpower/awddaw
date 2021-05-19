<?php


class SoundFavourite extends AppModel
{

    public $useTable = 'sound_favourite';

    public $belongsTo = array(
        'Sound' => array(
            'className' => 'Sound',
            'foreignKey' => 'sound_id',



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



                'SoundFavourite.id'=> $id,




            )
        ));
    }



    public function getUserFavouriteSounds($user_id)
    {
        return $this->find('all', array(
            'conditions' => array(




                'SoundFavourite.user_id'=> $user_id,




            ),

        ));
    }

    public function ifExist($data)
    {
        return $this->find('first', array(
            'conditions' => array(



                'SoundFavourite.sound_id'=> $data['sound_id'],
                'SoundFavourite.user_id'=> $data['user_id'],




            )
        ));
    }

    public function getAll()
    {
        return $this->find('all');
    }






}
?>