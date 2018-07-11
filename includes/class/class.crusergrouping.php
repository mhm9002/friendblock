<?php

class crUserGrouping{

    const GROUP_SIZE = 50;

    public static function updateGroups(){
        global $db;
        
        $threshold = date('Y-m-d H:i:s', time()-(60*60*24*3));

        $query = $db->prepare ('SELECT id FROM '.TABLE_USERS.' ORDER BY id desc LIMIT 1');
        $overall_size = $db->getVar($query); 

        $group_count = ceil($overall_size/crUserGrouping::GROUP_SIZE);

        $query = $db->prepare('SELECT gID FROM '.TABLE_INACTIVE_GROUPS. ' ORDER BY gID desc LIMIT 1');
        $last_group_no = $db->getVar($query);

        //initiate for the first time
        if (!$last_group_no)
            $last_group_no = -1;
        
        if ($group_count> $last_group_no+1){
            //add new groups
            for ($i=$last_group_no+1; $i<$group_count; $i++){
                $db->insertFromArray(TABLE_INACTIVE_GROUPS,['gID'=>$i,'checked'=>false,'dateChecked'=>$threshold]);
            }
        }

    }

    public static function getNextGroup(){
        global $db;

        $threshold = date('Y-m-d H:i:s', time()-(60*60*24*3));;
        $groupUsers = [];

        $query = $db->prepare('SELECT gID FROM '. TABLE_INACTIVE_GROUPS.' 
        WHERE checked=0 OR dateChecked<%s ORDER BY gID ASC LIMIT 1',$threshold);

        $group_id = $db->getVar($query);

        //var_dump($group_id);

        if (isset($group_id)) {
            $startingID = $group_id*crUserGrouping::GROUP_SIZE;
            $endingID = ($group_id + 1)*crUserGrouping::GROUP_SIZE;
            for($i=$startingID; $i<$endingID; $i++){
                $query = $db->prepare('SELECT id FROM '.TABLE_USERS.' WHERE id=%d',$i);
                $user = $db->getVar($query);
            
                if ($user){
                    array_push($groupUsers,$i);
                }
            }
        
            //mark group as checked
            $db->updateFromArray(TABLE_INACTIVE_GROUPS, ['checked' => true, 'dateChecked'=>date('Y-m-d H:i:s')], ['gID' => $group_id]);
        }
            
        return $groupUsers;

    }
    
}


?>