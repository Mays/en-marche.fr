<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20180531141851 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE mooc_chapter (id INT UNSIGNED AUTO_INCREMENT NOT NULL, mooc_id INT UNSIGNED DEFAULT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, published TINYINT(1) NOT NULL, published_at DATETIME NOT NULL, display_order SMALLINT DEFAULT 1 NOT NULL, INDEX IDX_A3EDA0D1255EEB87 (mooc_id), UNIQUE INDEX mooc_chapter_slug (slug), UNIQUE INDEX mooc_chapter_order_display_by_mooc (display_order, mooc_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mooc_elements (id INT UNSIGNED AUTO_INCREMENT NOT NULL, chapter_id INT UNSIGNED DEFAULT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, display_order SMALLINT DEFAULT 1 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, type VARCHAR(255) NOT NULL, youtube_url VARCHAR(255) DEFAULT NULL, INDEX IDX_691284C5579F4768 (chapter_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mooc (id INT UNSIGNED AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, slug VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX mooc_slug (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mooc_chapter ADD CONSTRAINT FK_A3EDA0D1255EEB87 FOREIGN KEY (mooc_id) REFERENCES mooc (id)');
        $this->addSql('ALTER TABLE mooc_elements ADD CONSTRAINT FK_691284C5579F4768 FOREIGN KEY (chapter_id) REFERENCES mooc_chapter (id)');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE mooc_elements DROP FOREIGN KEY FK_691284C5579F4768');
        $this->addSql('ALTER TABLE mooc_chapter DROP FOREIGN KEY FK_A3EDA0D1255EEB87');
        $this->addSql('DROP TABLE mooc_chapter');
        $this->addSql('DROP TABLE mooc_elements');
        $this->addSql('DROP TABLE mooc');
    }
}
