<?php
use WHMCS\Database\Capsule;

class AnsibleComand
{
    public function getPostIds()
    {
        $ids=[];
        foreach ($_POST['delete'] as $key=>$element){
            $ids[]=$key;
        }
        return $ids;
    }
    public function deleteAnsible($post,$LANG){
        extract($post);
        foreach ($delete as $key=>$item){
            Capsule::table('mod_onconfiguratorAddon')
                ->where('id',$key)
                ->delete();
        }
        $this->printSuccess($LANG['datadel'],$LANG['datadeldb']);
    }

    function getIds()
    {
        $ids=[];
        print_r($_GET['ansibles']);
        foreach ($_GET['ansibles'] as $key=>$element){
            $ids[]=$element;
        }
        return $ids;
    }

    public function saveData($post)
    {
        extract($post);
        $os='/';
        foreach ($check as $key=>$ch){
            $os.=$key.'/';
        }

        if(Capsule::table( 'mod_onconfiguratorAddon' )
            ->insert(['name'=>$name,'descriptions'=>$descriptions,'body'=>$body,'os'=>$os,'Addon'=>$addonid])){
            return true;
        }
        else {
            return false;
        }

    }
    public function printSuccess($bigText,$text)
    {
        printf('<div class="successbox">
        <strong class="title">%s</strong><br>%s
        </div>',$bigText,$text);
    }

    public function printError($text,$errorText)
    {
        printf('<div class="errorbox">
        <strong class="title">%s</strong><br>
        %s</div>',$text,$errorText);
    }

    public function checkData($post,$LANG)
    {
        extract($post);
        if(empty($name)){
            return $LANG['errorname'];
        }
        if(Capsule::table( 'mod_onconfiguratorAddon' )->where('name',$name)->first()){
            return $LANG['errorname2'];
        }
        if (empty($body)){
            return $LANG['errorname3'];
        }
        return false;
    }

    public function preProcessing($post,$LANG)
    {
        $error=$this->checkdata($post,$LANG);
        if($error){
            $this->printError($error);
            return $error;
        }
        if ($this->saveData($post)){
            $this->printSuccess($LANG['addent'],$LANG['addyou']);
        }

    }

    function updateAnsible($post,$LANG){
        $os='/';
        extract($post);
        if(empty($name)){
            $this->printError($LANG['errorname']);
            return;
        }
        if(Capsule::table( 'mod_onconfiguratorAddon' )->where('name',$name)->first()){
            $this->printError($LANG['errorname2']);
            return;
        }
        if (empty($body)){
            $this->printError($LANG['errorname3']);
            return;
        }
        foreach ($post['check'] as $key=>$ch){
            $os.=$key.'/';
        }
        if(Capsule::table('mod_onconfiguratorAddon')
            ->where('id', $post['idupdate'])
            ->update(['name'=>$post['name'],
                'descriptions'=>$post['descriptions'],
                'Addon'=>$post['addonid'],
                'body'=>$post['body'],
                'os'=>$os]))
        {
            $this->printSuccess($LANG['updok'],$LANG['updok2']);
        }else{
            $this->printSuccess($LANG['updok'],$LANG['updok2']);
        }
        extract($post);
    }
}