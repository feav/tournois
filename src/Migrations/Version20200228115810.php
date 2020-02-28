<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200228115810 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE terrain2 (id INT AUTO_INCREMENT NOT NULL, tournoi_id INT NOT NULL, nom VARCHAR(255) NOT NULL, occupe TINYINT(1) NOT NULL, INDEX IDX_A67B4B44F607770A (tournoi_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE terrain2 ADD CONSTRAINT FK_A67B4B44F607770A FOREIGN KEY (tournoi_id) REFERENCES tournoi (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE match2 ADD terrain2_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE match2 ADD CONSTRAINT FK_6AC51147C588DEF0 FOREIGN KEY (terrain2_id) REFERENCES terrain2 (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6AC51147C588DEF0 ON match2 (terrain2_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE match2 DROP FOREIGN KEY FK_6AC51147C588DEF0');
        $this->addSql('DROP TABLE terrain2');
        $this->addSql('DROP INDEX UNIQ_6AC51147C588DEF0 ON match2');
        $this->addSql('ALTER TABLE match2 DROP terrain2_id');
    }
}
