<?php

namespace app\web\logic;


use app\web\model\User;
use think\Model;

class UserLogic extends Model
{
    static function isUserExist($name){
        try {
            $users = User::all(['name'=>$name]);
            if (count($users)) {
                return [true];
            } else {
                return [false];
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }

    static function getUserByLoginNameAndPassword($loginName, $password){
        try {
            $user = User::get(['loginName'=>$loginName, 'password'=>$password]);
            if ($user) {
                return [true, $user];
            } else {
                return [false];
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }

    static function createUser($info){
        try {
            $user = new User($info);
            $user->save();
            return [true, $user->id];
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    static function getUserById($id){
        try {
            $user = User::get($id);
            if ($user) {
                return [true, $user];
            } else {
                return [false];
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }

    static function getUserByRole($role){
        try {
            $users = User::where(['role'=>$role])->select();
            return [true, $users];
        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }

    static function deleteUserById($id){
        try {
            $user = User::get($id);
            if ($user) {
                $user->delete();
                return [true];
            } else {
                return [false];
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

}
