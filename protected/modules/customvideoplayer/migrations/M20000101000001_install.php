<?php
namespace humhub\modules\customvideoplayer\migrations;

use yii\db\Migration;

class M20000101000001_install extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        // Таблица за видео обработка
        $this->createTable('customvideoplayer_video', [
            'id' => $this->primaryKey(),
            'file_id' => $this->integer()->notNull(),
            'original_filename' => $this->string(255)->notNull(),
            'file_size' => $this->bigInteger(),
            'duration' => $this->decimal(10, 2),
            'status' => $this->smallInteger()->defaultValue(0),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
        ], $tableOptions);

        // Таблица за видео варианти
        $this->createTable('customvideoplayer_video_variant', [
            'id' => $this->primaryKey(),
            'video_id' => $this->integer()->notNull(),
            'quality' => $this->integer()->notNull(),
            'width' => $this->integer(),
            'height' => $this->integer(),
            'bitrate' => $this->string(50),
            'file_path' => $this->string(255)->notNull(),
            'file_size' => $this->bigInteger(),
            'duration' => $this->decimal(10, 2),
            'mime_type' => $this->string(100),
            'created_at' => $this->dateTime(),
        ], $tableOptions);

        // Индекси
        $this->createIndex('idx_cvp_video_file_id', 'customvideoplayer_video', 'file_id');
        $this->createIndex('idx_cvp_video_status', 'customvideoplayer_video', 'status');
        $this->createIndex('idx_cvp_variant_video_id', 'customvideoplayer_video_variant', 'video_id');
        $this->createIndex('idx_cvp_variant_quality', 'customvideoplayer_video_variant', 'quality');
        
        $this->addForeignKey('fk_cvp_variant_video', 
            'customvideoplayer_video_variant', 'video_id', 
            'customvideoplayer_video', 'id', 
            'CASCADE', 'CASCADE'
        );

        $this->addForeignKey('fk_cvp_video_file', 
            'customvideoplayer_video', 'file_id', 
            'file', 'id', 
            'CASCADE', 'CASCADE'
        );

        return true;
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_cvp_variant_video', 'customvideoplayer_video_variant');
        $this->dropForeignKey('fk_cvp_video_file', 'customvideoplayer_video');
        $this->dropTable('customvideoplayer_video_variant');
        $this->dropTable('customvideoplayer_video');
        
        return true;
    }
}
