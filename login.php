<?php

class login {


  private record_user $_Record_user;
  private string $_UserIP;
  private string $_UserAgent;

  private int $_id;
  private string $_name;


  public function __construct()
  {
   $this->_Record_user = new record_user();
   $this->SetUserAgent();
   $this->SetUserIP();
  }


    public function LoggedIn($user):bool{

  }


  private function UpdateRecordUser(){
      $this->_Record_user->SetId($this->GetUserID());
      $this->_Record_user->SetAgent($this->GetUserAgent());
      $this->_Record_user->SetIP($this->GetUserIP());
      $this->_Record_user->SetName($this->GetUserName());
}

  public function GetUserRecord():record_user{
      return $this->_Record_user;
  }

  private function SetUserIP():void{
      $this->_UserIP = $_SERVER['REMOTE_ADDR'];
      $this->UpdateRecordUser();
  }

  private function SetUserAgent():void{
      $this->_UserAgent = $_SERVER['HTTP_USER_AGENT'];
      $this->UpdateRecordUser();
  }

  public function GetUserIP():string{
      return $this->_UserIP;
  }

  public function GetUserAgent():string{
    return $this->_UserAgent;
  }


  public function SetUserName(string $name):void {
      $this->_name = $name;
      $this->UpdateRecordUser();
  }

  public function GetUserName():string{
     return $this->_name;
  }

  public function SetUserID(int $id):void{
   $this->_id = $id;
   $this->UpdateRecordUser();
  }

  public function GetUserID():int{
      return $this->_id;
  }

  public function CreateHash():string{
   hash();
  }


  public function CheckHash(string $hash):bool {
     $rtn = false;

      return $rtn;
  }



}