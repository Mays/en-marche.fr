<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180531171229 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql('CREATE TABLE quizz_link (id INT UNSIGNED AUTO_INCREMENT NOT NULL, quizz_id INT UNSIGNED NOT NULL, title VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, INDEX IDX_C09B04EFBA934BCD (quizz_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE quizz_file (id INT UNSIGNED AUTO_INCREMENT NOT NULL, quizz_id INT UNSIGNED NOT NULL, title VARCHAR(255) NOT NULL, path VARCHAR(255) NOT NULL, INDEX IDX_7AA8AB0EBA934BCD (quizz_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE quizz_link ADD CONSTRAINT FK_C09B04EFBA934BCD FOREIGN KEY (quizz_id) REFERENCES mooc_elements (id)');
        $this->addSql('ALTER TABLE quizz_file ADD CONSTRAINT FK_7AA8AB0EBA934BCD FOREIGN KEY (quizz_id) REFERENCES mooc_elements (id)');
        $this->addSql('ALTER TABLE mooc_elements ADD title VARCHAR(255) DEFAULT NULL, ADD description LONGTEXT DEFAULT NULL, ADD type_form LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DROP TABLE quizz_link');
        $this->addSql('DROP TABLE quizz_file');
        $this->addSql('ALTER TABLE mooc_elements DROP title, DROP description, DROP type_form');
    }
}
