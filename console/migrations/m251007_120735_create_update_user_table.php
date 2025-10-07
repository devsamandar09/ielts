<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%update_user}}`.
 */
class m251007_120735_create_update_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%user}}', 'first_name', $this->string(100)->after('username'));
        $this->addColumn('{{%user}}', 'last_name', $this->string(100)->after('first_name'));
        $this->addColumn('{{%user}}', 'phone', $this->string(20)->after('last_name'));
        $this->addColumn('{{%user}}', 'role', $this->string(20)->defaultValue('user')->after('status'));
        $this->addColumn('{{%user}}', 'avatar', $this->string(255)->after('role'));
        $this->addColumn('{{%user}}', 'last_login_at', $this->integer()->after('updated_at'));

        // Index qo'shish
        $this->createIndex('idx-user-role', '{{%user}}', 'role');
    }



    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%user}}', 'first_name');
        $this->dropColumn('{{%user}}', 'last_name');
        $this->dropColumn('{{%user}}', 'phone');
        $this->dropColumn('{{%user}}', 'role');
        $this->dropColumn('{{%user}}', 'avatar');
        $this->dropColumn('{{%user}}', 'last_login_at');

    }
}
