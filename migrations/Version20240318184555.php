<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240318184555 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create new fields to store node-type fields names in nodes_custom_forms, nodes_sources_documents and nodes_to_nodes tables.';
    }

    public function up(Schema $schema): void
    {
        /*
         * FIRST CREATE NEW FIELDS
         */
        $this->addSql('ALTER TABLE nodes_custom_forms ADD field_name VARCHAR(250)');
        $this->addSql('ALTER TABLE nodes_sources_documents ADD field_name VARCHAR(250)');
        $this->addSql('ALTER TABLE nodes_to_nodes ADD field_name VARCHAR(250)');
        /*
         * Need to populate new field_name with node-type field names
         */
        $this->addSql('UPDATE nodes_custom_forms SET field_name = (SELECT name FROM node_type_fields WHERE id = node_type_field_id)');
        $this->addSql('UPDATE nodes_sources_documents SET field_name = (SELECT name FROM node_type_fields WHERE id = node_type_field_id)');
        $this->addSql('UPDATE nodes_to_nodes SET field_name = (SELECT name FROM node_type_fields WHERE id = node_type_field_id)');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('Cannot convert node-type fields name back to their identifiers');
    }
}
