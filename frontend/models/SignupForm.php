<?php

namespace frontend\models;

use Yii;
use yii\base\Model;
use common\models\User;

/**
 * Signup form
 */
class SignupForm extends Model
{
    public $username;
    public $email;
    public $password;
    public $first_name;
    public $last_name;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username', 'email', 'password', 'first_name', 'last_name'], 'required'],
            [['username'], 'trim'],
            [['username'], 'unique', 'targetClass' => '\common\models\User', 'message' => 'This username has already been taken.'],
            [['username'], 'string', 'min' => 2, 'max' => 255],

            [['email'], 'trim'],
            [['email'], 'email'],
            [['email'], 'string', 'max' => 255],
            [['email'], 'unique', 'targetClass' => '\common\models\User', 'message' => 'This email address has already been taken.'],

            [['password'], 'string', 'min' => Yii::$app->params['user.passwordMinLength']],

            [['first_name', 'last_name'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'username' => 'Username',
            'email' => 'Email',
            'password' => 'Password',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
        ];
    }

    /**
     * Signs user up
     */
    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }

        $user = new User();
        $user->username = $this->username;
        $user->email = $this->email;
        $user->first_name = $this->first_name;
        $user->last_name = $this->last_name;
        $user->role = User::ROLE_USER;
        $user->status = User::STATUS_ACTIVE;
        $user->setPassword($this->password);
        $user->generateAuthKey();

        return $user->save() ? $user : null;
    }
}
