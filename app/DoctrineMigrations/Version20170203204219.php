<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170203204219 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(255) NOT NULL, username_canonical VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, email_canonical VARCHAR(255) NOT NULL, enabled TINYINT(1) NOT NULL, salt VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, locked TINYINT(1) NOT NULL, expired TINYINT(1) NOT NULL, expires_at DATETIME DEFAULT NULL, confirmation_token VARCHAR(255) DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', credentials_expired TINYINT(1) NOT NULL, credentials_expire_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D64992FC23A8 (username_canonical), UNIQUE INDEX UNIQ_8D93D649A0D96FBF (email_canonical), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE portfolio (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) DEFAULT NULL, userId INT DEFAULT NULL, INDEX FK_portfolio_user (userId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE portfolio_symbol (portfolioId INT NOT NULL, symbolId INT NOT NULL, INDEX IDX_EA23DBCC60C1F35E (portfolioId), INDEX IDX_EA23DBCC20796057 (symbolId), PRIMARY KEY(portfolioId, symbolId)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE stock (id INT AUTO_INCREMENT NOT NULL, date DATE DEFAULT NULL, open NUMERIC(10, 6) DEFAULT NULL, high NUMERIC(10, 6) DEFAULT NULL, low NUMERIC(10, 6) DEFAULT NULL, close NUMERIC(10, 6) DEFAULT NULL, `change` NUMERIC(10, 6) DEFAULT NULL, volume BIGINT DEFAULT NULL, symbolId INT DEFAULT NULL, INDEX FK_symbol_symbolInfo (symbolId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE symbol (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(10) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE portfolio ADD CONSTRAINT FK_A9ED106264B64DCC FOREIGN KEY (userId) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE portfolio_symbol ADD CONSTRAINT FK_EA23DBCC60C1F35E FOREIGN KEY (portfolioId) REFERENCES portfolio (id)');
        $this->addSql('ALTER TABLE portfolio_symbol ADD CONSTRAINT FK_EA23DBCC20796057 FOREIGN KEY (symbolId) REFERENCES symbol (id)');
        $this->addSql('ALTER TABLE stock ADD CONSTRAINT FK_4B36566020796057 FOREIGN KEY (symbolId) REFERENCES symbol (id)');
        
        $this->addSql("INSERT INTO `symbol` (`id`, `name`) VALUES (1, 'YHOO');");
        $this->addSql("INSERT INTO `symbol` (`id`, `name`) VALUES (2, 'GOOG');");
        $this->addSql("INSERT INTO `symbol` (`id`, `name`) VALUES (3, 'AAPL');");
        $this->addSql("INSERT INTO `symbol` (`id`, `name`) VALUES (4, 'KO');");
        $this->addSql("INSERT INTO `symbol` (`id`, `name`) VALUES (5, 'BA');");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE portfolio DROP FOREIGN KEY FK_A9ED106264B64DCC');
        $this->addSql('ALTER TABLE portfolio_symbol DROP FOREIGN KEY FK_EA23DBCC60C1F35E');
        $this->addSql('ALTER TABLE portfolio_symbol DROP FOREIGN KEY FK_EA23DBCC20796057');
        $this->addSql('ALTER TABLE stock DROP FOREIGN KEY FK_4B36566020796057');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE portfolio');
        $this->addSql('DROP TABLE portfolio_symbol');
        $this->addSql('DROP TABLE stock');
        $this->addSql('DROP TABLE symbol');
    }
}
