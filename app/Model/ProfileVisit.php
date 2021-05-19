<?php



class ProfileVisit extends AppModel
{
    public $useTable = 'profile_visit';

    public $belongsTo = array(

        'Sender' => array(
            'className' => 'User',
            'foreignKey' => 'sender_id',


        ),

        'Receiver' => array(
            'className' => 'User',
            'foreignKey' => 'receiver_id',


        ),



    );

    public function getDetails($id)
    {
        return $this->find('first', array(
            'conditions' => array(

                'ProfileVisit.id' => $id





            )
        ));
    }











}