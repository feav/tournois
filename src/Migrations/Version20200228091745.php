<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200228091745 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE match2 ADD tournoi_id INT NOT NULL, ADD terrain_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE match2 ADD CONSTRAINT FK_6AC51147F607770A FOREIGN KEY (tournoi_id) REFERENCES tournoi (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE match2 ADD CONSTRAINT FK_6AC511478A2D8B41 FOREIGN KEY (terrain_id) REFERENCES terrain (id)');
        $this->addSql('CREATE INDEX IDX_6AC51147F607770A ON match2 (tournoi_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6AC511478A2D8B41 ON match2 (terrain_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE match2 DROP FOREIGN KEY FK_6AC51147F607770A');
        $this->addSql('ALTER TABLE match2 DROP FOREIGN KEY FK_6AC511478A2D8B41');
        $this->addSql('DROP INDEX IDX_6AC51147F607770A ON match2');
        $this->addSql('DROP INDEX UNIQ_6AC511478A2D8B41 ON match2');
        $this->addSql('ALTER TABLE match2 DROP tournoi_id, DROP terrain_id');
    }
}
