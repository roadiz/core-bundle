<?php
declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Database initialization migration for MySQL/MariaDB.
 *
 * @package RZ\Roadiz\Migrations
 */
final class Version20201203004857 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Database initialization migration for MySQL/MariaDB.';
    }

    public function up(Schema $schema) : void
    {
        $this->skipIf(
            $this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.'
        );
        $this->skipIf($schema->hasTable('nodes'), 'Database has been initialized before Doctrine Migration tool.');

        $this->addSql('CREATE TABLE attribute_group_translations (id INT AUTO_INCREMENT NOT NULL, attribute_group_id INT DEFAULT NULL, translation_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_5C704A6862D643B7 (attribute_group_id), INDEX IDX_5C704A689CAA2B25 (translation_id), INDEX IDX_5C704A685E237E06 (name), UNIQUE INDEX UNIQ_5C704A6862D643B79CAA2B25 (attribute_group_id, translation_id), UNIQUE INDEX UNIQ_5C704A685E237E069CAA2B25 (name, translation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE attribute_groups (id INT AUTO_INCREMENT NOT NULL, canonical_name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_D28C172A674D812 (canonical_name), INDEX IDX_D28C172A674D812 (canonical_name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE attribute_translations (id INT AUTO_INCREMENT NOT NULL, attribute_id INT DEFAULT NULL, translation_id INT DEFAULT NULL, label VARCHAR(255) NOT NULL, options LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', INDEX IDX_4059D4A0B6E62EFA (attribute_id), INDEX IDX_4059D4A09CAA2B25 (translation_id), INDEX IDX_4059D4A0EA750E8 (label), UNIQUE INDEX UNIQ_4059D4A0B6E62EFA9CAA2B25 (attribute_id, translation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE attribute_value_translations (id INT AUTO_INCREMENT NOT NULL, translation_id INT DEFAULT NULL, attribute_value INT DEFAULT NULL, value VARCHAR(255) DEFAULT NULL, INDEX IDX_1293849B9CAA2B25 (translation_id), INDEX IDX_1293849BFE4FBB82 (attribute_value), INDEX IDX_1293849B1D775834 (value), INDEX IDX_1293849B9CAA2B25FE4FBB82 (translation_id, attribute_value), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE attribute_values (id INT AUTO_INCREMENT NOT NULL, attribute_id INT DEFAULT NULL, node_id INT DEFAULT NULL, position DOUBLE PRECISION NOT NULL, INDEX IDX_184662BCB6E62EFA (attribute_id), INDEX IDX_184662BC460D9FD7 (node_id), INDEX IDX_184662BCB6E62EFA460D9FD7 (attribute_id, node_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE attributes (id INT AUTO_INCREMENT NOT NULL, group_id INT DEFAULT NULL, code VARCHAR(255) NOT NULL, searchable TINYINT(1) DEFAULT \'0\' NOT NULL, type INT NOT NULL, color VARCHAR(7) DEFAULT NULL, UNIQUE INDEX UNIQ_319B9E7077153098 (code), INDEX IDX_319B9E7077153098 (code), INDEX IDX_319B9E708CDE5729 (type), INDEX IDX_319B9E7094CD8C0D (searchable), INDEX IDX_319B9E70FE54D947 (group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE attributes_documents (id INT AUTO_INCREMENT NOT NULL, attribute_id INT DEFAULT NULL, document_id INT DEFAULT NULL, position DOUBLE PRECISION NOT NULL, INDEX IDX_67CCC9E0B6E62EFA (attribute_id), INDEX IDX_67CCC9E0C33F7837 (document_id), INDEX IDX_67CCC9E0462CE4F5 (position), INDEX IDX_67CCC9E0B6E62EFA462CE4F5 (attribute_id, position), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE custom_form_answers (id INT AUTO_INCREMENT NOT NULL, custom_form_id INT DEFAULT NULL, ip VARCHAR(255) NOT NULL, submitted_at DATETIME NOT NULL, INDEX IDX_1A3BB12658AFF2B0 (custom_form_id), INDEX IDX_1A3BB126A5E3B32D (ip), INDEX IDX_1A3BB1263182C73C (submitted_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE custom_form_field_attributes (id INT AUTO_INCREMENT NOT NULL, custom_form_answer_id INT DEFAULT NULL, custom_form_field_id INT DEFAULT NULL, value LONGTEXT DEFAULT NULL, INDEX IDX_B7133605F1D6C2D1 (custom_form_answer_id), INDEX IDX_B71336057F13CC0F (custom_form_field_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE custom_form_answers_documents (customformfieldattribute_id INT NOT NULL, document_id INT NOT NULL, INDEX IDX_E979F877C84CA2FC (customformfieldattribute_id), INDEX IDX_E979F877C33F7837 (document_id), PRIMARY KEY(customformfieldattribute_id, document_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE custom_form_fields (id INT AUTO_INCREMENT NOT NULL, custom_form_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, label VARCHAR(255) NOT NULL, placeholder VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, default_values LONGTEXT DEFAULT NULL, type INT NOT NULL, expanded TINYINT(1) DEFAULT \'0\' NOT NULL, field_required TINYINT(1) DEFAULT \'0\' NOT NULL, group_name VARCHAR(255) DEFAULT NULL, group_name_canonical VARCHAR(255) DEFAULT NULL, position DOUBLE PRECISION NOT NULL, INDEX IDX_4A3782EC58AFF2B0 (custom_form_id), INDEX IDX_4A3782EC462CE4F5 (position), INDEX IDX_4A3782EC77792576 (group_name), INDEX IDX_4A3782EC8CDE5729 (type), UNIQUE INDEX UNIQ_4A3782EC5E237E0658AFF2B0 (name, custom_form_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE custom_forms (id INT AUTO_INCREMENT NOT NULL, color VARCHAR(255) DEFAULT NULL, name VARCHAR(255) NOT NULL, display_name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, email LONGTEXT DEFAULT NULL, open TINYINT(1) DEFAULT \'1\' NOT NULL, close_date DATETIME DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_3E32E39E5E237E06 (name), INDEX IDX_3E32E39E8B8E8428 (created_at), INDEX IDX_3E32E39E43625D9F (updated_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE documents (id INT AUTO_INCREMENT NOT NULL, raw_document INT DEFAULT NULL, original INT DEFAULT NULL, raw TINYINT(1) DEFAULT \'0\' NOT NULL, embedId VARCHAR(255) DEFAULT NULL, embedPlatform VARCHAR(255) DEFAULT NULL, filename VARCHAR(255) DEFAULT NULL, mime_type VARCHAR(255) DEFAULT NULL, folder VARCHAR(255) NOT NULL, private TINYINT(1) DEFAULT \'0\' NOT NULL, imageWidth INT DEFAULT 0 NOT NULL, imageHeight INT DEFAULT 0 NOT NULL, average_color VARCHAR(7) DEFAULT NULL, filesize INT DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_A2B0728826CBD5A5 (raw_document), INDEX IDX_A2B072882F727085 (original), INDEX IDX_A2B072881AB3DB55 (raw), INDEX IDX_A2B07288D206C1D1 (private), INDEX IDX_A2B072881AB3DB55D206C1D1 (raw, private), INDEX IDX_A2B072882100AA2E (mime_type), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE documents_translations (id INT AUTO_INCREMENT NOT NULL, translation_id INT DEFAULT NULL, document_id INT DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, copyright LONGTEXT DEFAULT NULL, INDEX IDX_5CD2F5509CAA2B25 (translation_id), INDEX IDX_5CD2F550C33F7837 (document_id), UNIQUE INDEX UNIQ_5CD2F550C33F78379CAA2B25 (document_id, translation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE folders (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, folder_name VARCHAR(255) NOT NULL, visible TINYINT(1) DEFAULT \'1\' NOT NULL, position DOUBLE PRECISION NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_FE37D30F47BC5813 (folder_name), INDEX IDX_FE37D30F727ACA70 (parent_id), INDEX IDX_FE37D30F7AB0E859 (visible), INDEX IDX_FE37D30F462CE4F5 (position), INDEX IDX_FE37D30F8B8E8428 (created_at), INDEX IDX_FE37D30F43625D9F (updated_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE documents_folders (folder_id INT NOT NULL, document_id INT NOT NULL, INDEX IDX_617BB29C162CB942 (folder_id), INDEX IDX_617BB29CC33F7837 (document_id), PRIMARY KEY(folder_id, document_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE folders_translations (id INT AUTO_INCREMENT NOT NULL, folder_id INT DEFAULT NULL, translation_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_9F6A68B2162CB942 (folder_id), INDEX IDX_9F6A68B29CAA2B25 (translation_id), UNIQUE INDEX UNIQ_9F6A68B2162CB9429CAA2B25 (folder_id, translation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `groups` (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_F06D39705E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE groups_roles (group_id INT NOT NULL, role_id INT NOT NULL, INDEX IDX_E79D4963FE54D947 (group_id), INDEX IDX_E79D4963D60322AC (role_id), PRIMARY KEY(group_id, role_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE log (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, node_source_id INT DEFAULT NULL, username VARCHAR(255) DEFAULT NULL, message LONGTEXT NOT NULL, level INT NOT NULL, datetime DATETIME NOT NULL, client_ip VARCHAR(255) DEFAULT NULL, channel VARCHAR(255) DEFAULT NULL, additional_data JSON DEFAULT NULL, INDEX IDX_8F3F68C5A76ED395 (user_id), INDEX IDX_8F3F68C58E831402 (node_source_id), INDEX IDX_8F3F68C593F3C6CA (datetime), INDEX IDX_8F3F68C59AEACC13 (level), INDEX IDX_8F3F68C5F85E0677 (username), INDEX IDX_8F3F68C5A2F98E47 (channel), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE login_attempts (id INT AUTO_INCREMENT NOT NULL, ip_address VARCHAR(50) DEFAULT NULL, date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', blocks_login_until DATETIME DEFAULT NULL, username VARCHAR(255) NOT NULL, attempt_count INT DEFAULT NULL, INDEX IDX_9163C7FBF85E0677 (username), INDEX IDX_9163C7FBEFF8A4EEF85E0677 (blocks_login_until, username), INDEX IDX_9163C7FBEFF8A4EEF85E067722FFD58C (blocks_login_until, username, ip_address), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE node_type_fields (id INT AUTO_INCREMENT NOT NULL, node_type_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, label VARCHAR(255) NOT NULL, placeholder VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, default_values LONGTEXT DEFAULT NULL, type INT NOT NULL, expanded TINYINT(1) DEFAULT \'0\' NOT NULL, universal TINYINT(1) DEFAULT \'0\' NOT NULL, exclude_from_search TINYINT(1) DEFAULT \'0\' NOT NULL, min_length INT DEFAULT NULL, max_length INT DEFAULT NULL, indexed TINYINT(1) DEFAULT \'0\' NOT NULL, visible TINYINT(1) DEFAULT \'1\' NOT NULL, group_name VARCHAR(255) DEFAULT NULL, group_name_canonical VARCHAR(255) DEFAULT NULL, position DOUBLE PRECISION NOT NULL, INDEX IDX_1D3923596344C9E1 (node_type_id), INDEX IDX_1D3923597AB0E859 (visible), INDEX IDX_1D392359D9416D95 (indexed), INDEX IDX_1D392359462CE4F5 (position), INDEX IDX_1D39235977792576 (group_name), INDEX IDX_1D3923594BAF07A4 (group_name_canonical), INDEX IDX_1D3923598CDE5729 (type), INDEX IDX_1D392359A4B8F6E1 (universal), UNIQUE INDEX UNIQ_1D3923595E237E066344C9E1 (name, node_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE node_types (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, display_name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, visible TINYINT(1) DEFAULT \'1\' NOT NULL, publishable TINYINT(1) DEFAULT \'0\' NOT NULL, reachable TINYINT(1) DEFAULT \'1\' NOT NULL, hiding_nodes TINYINT(1) DEFAULT \'0\' NOT NULL, hiding_non_reachable_nodes TINYINT(1) DEFAULT \'0\' NOT NULL, color VARCHAR(255) DEFAULT NULL, default_ttl INT DEFAULT 0 NOT NULL, UNIQUE INDEX UNIQ_409B1BCC5E237E06 (name), INDEX IDX_409B1BCC7AB0E859 (visible), INDEX IDX_409B1BCC7697C594 (publishable), INDEX IDX_409B1BCCFB696FF0 (hiding_nodes), INDEX IDX_409B1BCC5A3C14C7 (hiding_non_reachable_nodes), INDEX IDX_409B1BCC96ED695F (reachable), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE nodes (id INT AUTO_INCREMENT NOT NULL, parent_node_id INT DEFAULT NULL, node_name VARCHAR(255) NOT NULL, dynamic_node_name TINYINT(1) DEFAULT \'1\' NOT NULL, home TINYINT(1) DEFAULT \'0\' NOT NULL, visible TINYINT(1) DEFAULT \'1\' NOT NULL, status INT NOT NULL, ttl INT DEFAULT 0 NOT NULL, locked TINYINT(1) DEFAULT \'0\' NOT NULL, priority NUMERIC(2, 1) NOT NULL, hide_children TINYINT(1) DEFAULT \'0\' NOT NULL, sterile TINYINT(1) DEFAULT \'0\' NOT NULL, children_order VARCHAR(255) NOT NULL, children_order_direction VARCHAR(4) NOT NULL, position DOUBLE PRECISION NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, nodeType_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_1D3D05FC9987F390 (node_name), INDEX IDX_1D3D05FC47D04729 (nodeType_id), INDEX IDX_1D3D05FC3445EB91 (parent_node_id), INDEX IDX_1D3D05FC7AB0E859 (visible), INDEX IDX_1D3D05FC7B00651C (status), INDEX IDX_1D3D05FCEAD2C891 (locked), INDEX IDX_1D3D05FCF32D8BE6 (sterile), INDEX IDX_1D3D05FC462CE4F5 (position), INDEX IDX_1D3D05FC8B8E8428 (created_at), INDEX IDX_1D3D05FC43625D9F (updated_at), INDEX IDX_1D3D05FC50E2D3D2 (hide_children), INDEX IDX_1D3D05FC9987F3907B00651C (node_name, status), INDEX IDX_1D3D05FC7AB0E8597B00651C (visible, status), INDEX IDX_1D3D05FC7AB0E8597B00651C3445EB91 (visible, status, parent_node_id), INDEX IDX_1D3D05FC7AB0E8593445EB91 (visible, parent_node_id), INDEX IDX_1D3D05FC71D60CD0 (home), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE nodes_tags (node_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_5B5CB38C460D9FD7 (node_id), INDEX IDX_5B5CB38CBAD26311 (tag_id), PRIMARY KEY(node_id, tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE stack_types (node_id INT NOT NULL, nodetype_id INT NOT NULL, INDEX IDX_DE24E53460D9FD7 (node_id), INDEX IDX_DE24E53886D7EB5 (nodetype_id), PRIMARY KEY(node_id, nodetype_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE nodes_custom_forms (id INT AUTO_INCREMENT NOT NULL, node_id INT DEFAULT NULL, custom_form_id INT DEFAULT NULL, node_type_field_id INT DEFAULT NULL, position DOUBLE PRECISION NOT NULL, INDEX IDX_4D401A0C460D9FD7 (node_id), INDEX IDX_4D401A0C58AFF2B0 (custom_form_id), INDEX IDX_4D401A0C47705282 (node_type_field_id), INDEX IDX_4D401A0C462CE4F5 (position), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE nodes_sources (id INT AUTO_INCREMENT NOT NULL, node_id INT DEFAULT NULL, translation_id INT DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, published_at DATETIME DEFAULT NULL, meta_title VARCHAR(255) NOT NULL, meta_keywords LONGTEXT NOT NULL, meta_description LONGTEXT NOT NULL, discr VARCHAR(255) NOT NULL, INDEX IDX_7C7DED6D460D9FD7 (node_id), INDEX IDX_7C7DED6D9CAA2B25 (translation_id), INDEX IDX_7C7DED6D4AD26064 (discr), INDEX IDX_7C7DED6D4AD260649CAA2B25 (discr, translation_id), INDEX IDX_7C7DED6DE0D4FDE14AD260649CAA2B25 (published_at, discr, translation_id), INDEX IDX_7C7DED6D2B36786B (title), INDEX IDX_7C7DED6DE0D4FDE1 (published_at), INDEX IDX_7C7DED6DE0D4FDE19CAA2B25 (published_at, translation_id), INDEX IDX_7C7DED6D460D9FD79CAA2B25E0D4FDE1 (node_id, translation_id, published_at), INDEX IDX_7C7DED6D2B36786BE0D4FDE1 (title, published_at), INDEX IDX_7C7DED6D2B36786BE0D4FDE19CAA2B25 (title, published_at, translation_id), UNIQUE INDEX UNIQ_7C7DED6D460D9FD79CAA2B25 (node_id, translation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE nodes_sources_documents (id INT AUTO_INCREMENT NOT NULL, ns_id INT DEFAULT NULL, document_id INT DEFAULT NULL, node_type_field_id INT DEFAULT NULL, position DOUBLE PRECISION NOT NULL, INDEX IDX_1CD104F7AA2D61 (ns_id), INDEX IDX_1CD104F7C33F7837 (document_id), INDEX IDX_1CD104F747705282 (node_type_field_id), INDEX IDX_1CD104F7462CE4F5 (position), INDEX IDX_1CD104F7AA2D6147705282 (ns_id, node_type_field_id), INDEX IDX_1CD104F7AA2D6147705282462CE4F5 (ns_id, node_type_field_id, position), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE nodes_to_nodes (id INT AUTO_INCREMENT NOT NULL, node_a_id INT DEFAULT NULL, node_b_id INT DEFAULT NULL, node_type_field_id INT DEFAULT NULL, position DOUBLE PRECISION NOT NULL, INDEX IDX_761F9A91FC7ADECE (node_a_id), INDEX IDX_761F9A91EECF7120 (node_b_id), INDEX IDX_761F9A9147705282 (node_type_field_id), INDEX IDX_761F9A91462CE4F5 (position), INDEX IDX_761F9A91FC7ADECE47705282 (node_a_id, node_type_field_id), INDEX IDX_761F9A91FC7ADECE47705282462CE4F5 (node_a_id, node_type_field_id, position), INDEX IDX_761F9A91EECF712047705282 (node_b_id, node_type_field_id), INDEX IDX_761F9A91EECF712047705282462CE4F5 (node_b_id, node_type_field_id, position), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE redirections (id INT AUTO_INCREMENT NOT NULL, ns_id INT DEFAULT NULL, query VARCHAR(255) NOT NULL, redirectUri VARCHAR(255) DEFAULT NULL, type INT NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_38F5ECE424BDB5EB (query), INDEX IDX_38F5ECE4AA2D61 (ns_id), INDEX IDX_38F5ECE48B8E8428 (created_at), INDEX IDX_38F5ECE443625D9F (updated_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE roles (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_B63E2EC75E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE settings (id INT AUTO_INCREMENT NOT NULL, setting_group_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, value LONGTEXT DEFAULT NULL, visible TINYINT(1) DEFAULT \'1\' NOT NULL, encrypted TINYINT(1) DEFAULT \'0\' NOT NULL, type INT NOT NULL, defaultValues LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_E545A0C55E237E06 (name), INDEX IDX_E545A0C550DDE1BD (setting_group_id), INDEX IDX_E545A0C58CDE5729 (type), INDEX IDX_E545A0C55E237E06 (name), INDEX IDX_E545A0C57AB0E859 (visible), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE settings_groups (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, in_menu TINYINT(1) DEFAULT \'0\' NOT NULL, UNIQUE INDEX UNIQ_FFD519025E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tags (id INT AUTO_INCREMENT NOT NULL, parent_tag_id INT DEFAULT NULL, color VARCHAR(7) DEFAULT \'#000000\' NOT NULL, tag_name VARCHAR(255) NOT NULL, visible TINYINT(1) DEFAULT \'1\' NOT NULL, children_order VARCHAR(255) DEFAULT \'position\' NOT NULL, children_order_direction VARCHAR(4) DEFAULT \'ASC\' NOT NULL, locked TINYINT(1) DEFAULT \'0\' NOT NULL, position DOUBLE PRECISION NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_6FBC9426B02CC1B0 (tag_name), INDEX IDX_6FBC9426F5C1A0D7 (parent_tag_id), INDEX IDX_6FBC94267AB0E859 (visible), INDEX IDX_6FBC9426EAD2C891 (locked), INDEX IDX_6FBC9426462CE4F5 (position), INDEX IDX_6FBC94268B8E8428 (created_at), INDEX IDX_6FBC942643625D9F (updated_at), INDEX IDX_6FBC9426F5C1A0D77AB0E859 (parent_tag_id, visible), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tags_translations (id INT AUTO_INCREMENT NOT NULL, tag_id INT DEFAULT NULL, translation_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, INDEX IDX_95D326DCBAD26311 (tag_id), INDEX IDX_95D326DC9CAA2B25 (translation_id), UNIQUE INDEX UNIQ_95D326DCBAD263119CAA2B25 (tag_id, translation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tags_translations_documents (id INT AUTO_INCREMENT NOT NULL, tag_translation_id INT DEFAULT NULL, document_id INT DEFAULT NULL, position DOUBLE PRECISION NOT NULL, INDEX IDX_6E886F1F22010F1 (tag_translation_id), INDEX IDX_6E886F1FC33F7837 (document_id), INDEX IDX_6E886F1F462CE4F5 (position), INDEX IDX_6E886F1F22010F1462CE4F5 (tag_translation_id, position), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE translations (id INT AUTO_INCREMENT NOT NULL, locale VARCHAR(10) NOT NULL, override_locale VARCHAR(10) DEFAULT NULL, name VARCHAR(255) NOT NULL, default_translation TINYINT(1) DEFAULT \'0\' NOT NULL, available TINYINT(1) DEFAULT \'1\' NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_C6B7DA874180C698 (locale), UNIQUE INDEX UNIQ_C6B7DA873F824FD6 (override_locale), UNIQUE INDEX UNIQ_C6B7DA875E237E06 (name), INDEX IDX_C6B7DA87A58FA485 (available), INDEX IDX_C6B7DA87609A56D9 (default_translation), INDEX IDX_C6B7DA878B8E8428 (created_at), INDEX IDX_C6B7DA8743625D9F (updated_at), INDEX IDX_C6B7DA87A58FA485609A56D9 (available, default_translation), INDEX IDX_C6B7DA87A58FA4854180C698 (available, locale), INDEX IDX_C6B7DA87A58FA4853F824FD6 (available, override_locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE url_aliases (id INT AUTO_INCREMENT NOT NULL, ns_id INT DEFAULT NULL, alias VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_E261ED65E16C6B94 (alias), INDEX IDX_E261ED65AA2D61 (ns_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_log_entries (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, action VARCHAR(8) NOT NULL, logged_at DATETIME NOT NULL, object_id VARCHAR(64) DEFAULT NULL, object_class VARCHAR(191) NOT NULL, version INT NOT NULL, data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', username VARCHAR(191) DEFAULT NULL, INDEX IDX_BC2E42C7A76ED395 (user_id), INDEX log_class_lookup_idx (object_class), INDEX log_date_lookup_idx (logged_at), INDEX log_user_lookup_idx (username), INDEX log_version_lookup_idx (object_id, object_class, version), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB ROW_FORMAT = DYNAMIC');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, chroot_id INT DEFAULT NULL, facebook_name VARCHAR(255) DEFAULT NULL, picture_url LONGTEXT DEFAULT NULL, enabled TINYINT(1) DEFAULT \'1\' NOT NULL, confirmation_token VARCHAR(255) DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, username VARCHAR(255) NOT NULL, salt VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, expired TINYINT(1) DEFAULT \'0\' NOT NULL, locked TINYINT(1) DEFAULT \'0\' NOT NULL, credentials_expires_at DATETIME DEFAULT NULL, credentials_expired TINYINT(1) DEFAULT \'0\' NOT NULL, expires_at DATETIME DEFAULT NULL, locale VARCHAR(7) DEFAULT NULL, email VARCHAR(255) NOT NULL, firstName VARCHAR(255) DEFAULT NULL, lastName VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, company VARCHAR(255) DEFAULT NULL, job VARCHAR(255) DEFAULT NULL, birthday DATETIME DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_1483A5E9C05FB297 (confirmation_token), UNIQUE INDEX UNIQ_1483A5E9F85E0677 (username), UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email), INDEX IDX_1483A5E96483A539 (chroot_id), INDEX IDX_1483A5E950F9BB84 (enabled), INDEX IDX_1483A5E9194FED4B (expired), INDEX IDX_1483A5E9F9D83E2 (expires_at), INDEX IDX_1483A5E94180C698 (locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users_roles (user_id INT NOT NULL, role_id INT NOT NULL, INDEX IDX_51498A8EA76ED395 (user_id), INDEX IDX_51498A8ED60322AC (role_id), PRIMARY KEY(user_id, role_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users_groups (user_id INT NOT NULL, group_id INT NOT NULL, INDEX IDX_FF8AB7E0A76ED395 (user_id), INDEX IDX_FF8AB7E0FE54D947 (group_id), PRIMARY KEY(user_id, group_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE attribute_group_translations ADD CONSTRAINT FK_5C704A6862D643B7 FOREIGN KEY (attribute_group_id) REFERENCES attribute_groups (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE attribute_group_translations ADD CONSTRAINT FK_5C704A689CAA2B25 FOREIGN KEY (translation_id) REFERENCES translations (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE attribute_translations ADD CONSTRAINT FK_4059D4A0B6E62EFA FOREIGN KEY (attribute_id) REFERENCES attributes (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE attribute_translations ADD CONSTRAINT FK_4059D4A09CAA2B25 FOREIGN KEY (translation_id) REFERENCES translations (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE attribute_value_translations ADD CONSTRAINT FK_1293849B9CAA2B25 FOREIGN KEY (translation_id) REFERENCES translations (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE attribute_value_translations ADD CONSTRAINT FK_1293849BFE4FBB82 FOREIGN KEY (attribute_value) REFERENCES attribute_values (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE attribute_values ADD CONSTRAINT FK_184662BCB6E62EFA FOREIGN KEY (attribute_id) REFERENCES attributes (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE attribute_values ADD CONSTRAINT FK_184662BC460D9FD7 FOREIGN KEY (node_id) REFERENCES nodes (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE attributes ADD CONSTRAINT FK_319B9E70FE54D947 FOREIGN KEY (group_id) REFERENCES attribute_groups (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE attributes_documents ADD CONSTRAINT FK_67CCC9E0B6E62EFA FOREIGN KEY (attribute_id) REFERENCES attributes (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE attributes_documents ADD CONSTRAINT FK_67CCC9E0C33F7837 FOREIGN KEY (document_id) REFERENCES documents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE custom_form_answers ADD CONSTRAINT FK_1A3BB12658AFF2B0 FOREIGN KEY (custom_form_id) REFERENCES custom_forms (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE custom_form_field_attributes ADD CONSTRAINT FK_B7133605F1D6C2D1 FOREIGN KEY (custom_form_answer_id) REFERENCES custom_form_answers (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE custom_form_field_attributes ADD CONSTRAINT FK_B71336057F13CC0F FOREIGN KEY (custom_form_field_id) REFERENCES custom_form_fields (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE custom_form_answers_documents ADD CONSTRAINT FK_E979F877C84CA2FC FOREIGN KEY (customformfieldattribute_id) REFERENCES custom_form_field_attributes (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE custom_form_answers_documents ADD CONSTRAINT FK_E979F877C33F7837 FOREIGN KEY (document_id) REFERENCES documents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE custom_form_fields ADD CONSTRAINT FK_4A3782EC58AFF2B0 FOREIGN KEY (custom_form_id) REFERENCES custom_forms (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE documents ADD CONSTRAINT FK_A2B0728826CBD5A5 FOREIGN KEY (raw_document) REFERENCES documents (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE documents ADD CONSTRAINT FK_A2B072882F727085 FOREIGN KEY (original) REFERENCES documents (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE documents_translations ADD CONSTRAINT FK_5CD2F5509CAA2B25 FOREIGN KEY (translation_id) REFERENCES translations (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE documents_translations ADD CONSTRAINT FK_5CD2F550C33F7837 FOREIGN KEY (document_id) REFERENCES documents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE folders ADD CONSTRAINT FK_FE37D30F727ACA70 FOREIGN KEY (parent_id) REFERENCES folders (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE documents_folders ADD CONSTRAINT FK_617BB29C162CB942 FOREIGN KEY (folder_id) REFERENCES folders (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE documents_folders ADD CONSTRAINT FK_617BB29CC33F7837 FOREIGN KEY (document_id) REFERENCES documents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE folders_translations ADD CONSTRAINT FK_9F6A68B2162CB942 FOREIGN KEY (folder_id) REFERENCES folders (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE folders_translations ADD CONSTRAINT FK_9F6A68B29CAA2B25 FOREIGN KEY (translation_id) REFERENCES translations (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE groups_roles ADD CONSTRAINT FK_E79D4963FE54D947 FOREIGN KEY (group_id) REFERENCES `groups` (id)');
        $this->addSql('ALTER TABLE groups_roles ADD CONSTRAINT FK_E79D4963D60322AC FOREIGN KEY (role_id) REFERENCES roles (id)');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C58E831402 FOREIGN KEY (node_source_id) REFERENCES nodes_sources (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE node_type_fields ADD CONSTRAINT FK_1D3923596344C9E1 FOREIGN KEY (node_type_id) REFERENCES node_types (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE nodes ADD CONSTRAINT FK_1D3D05FC47D04729 FOREIGN KEY (nodeType_id) REFERENCES node_types (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE nodes ADD CONSTRAINT FK_1D3D05FC3445EB91 FOREIGN KEY (parent_node_id) REFERENCES nodes (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE nodes_tags ADD CONSTRAINT FK_5B5CB38C460D9FD7 FOREIGN KEY (node_id) REFERENCES nodes (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE nodes_tags ADD CONSTRAINT FK_5B5CB38CBAD26311 FOREIGN KEY (tag_id) REFERENCES tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE stack_types ADD CONSTRAINT FK_DE24E53460D9FD7 FOREIGN KEY (node_id) REFERENCES nodes (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE stack_types ADD CONSTRAINT FK_DE24E53886D7EB5 FOREIGN KEY (nodetype_id) REFERENCES node_types (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE nodes_custom_forms ADD CONSTRAINT FK_4D401A0C460D9FD7 FOREIGN KEY (node_id) REFERENCES nodes (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE nodes_custom_forms ADD CONSTRAINT FK_4D401A0C58AFF2B0 FOREIGN KEY (custom_form_id) REFERENCES custom_forms (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE nodes_custom_forms ADD CONSTRAINT FK_4D401A0C47705282 FOREIGN KEY (node_type_field_id) REFERENCES node_type_fields (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE nodes_sources ADD CONSTRAINT FK_7C7DED6D460D9FD7 FOREIGN KEY (node_id) REFERENCES nodes (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE nodes_sources ADD CONSTRAINT FK_7C7DED6D9CAA2B25 FOREIGN KEY (translation_id) REFERENCES translations (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE nodes_sources_documents ADD CONSTRAINT FK_1CD104F7AA2D61 FOREIGN KEY (ns_id) REFERENCES nodes_sources (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE nodes_sources_documents ADD CONSTRAINT FK_1CD104F7C33F7837 FOREIGN KEY (document_id) REFERENCES documents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE nodes_sources_documents ADD CONSTRAINT FK_1CD104F747705282 FOREIGN KEY (node_type_field_id) REFERENCES node_type_fields (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE nodes_to_nodes ADD CONSTRAINT FK_761F9A91FC7ADECE FOREIGN KEY (node_a_id) REFERENCES nodes (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE nodes_to_nodes ADD CONSTRAINT FK_761F9A91EECF7120 FOREIGN KEY (node_b_id) REFERENCES nodes (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE nodes_to_nodes ADD CONSTRAINT FK_761F9A9147705282 FOREIGN KEY (node_type_field_id) REFERENCES node_type_fields (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE redirections ADD CONSTRAINT FK_38F5ECE4AA2D61 FOREIGN KEY (ns_id) REFERENCES nodes_sources (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE settings ADD CONSTRAINT FK_E545A0C550DDE1BD FOREIGN KEY (setting_group_id) REFERENCES settings_groups (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE tags ADD CONSTRAINT FK_6FBC9426F5C1A0D7 FOREIGN KEY (parent_tag_id) REFERENCES tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tags_translations ADD CONSTRAINT FK_95D326DCBAD26311 FOREIGN KEY (tag_id) REFERENCES tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tags_translations ADD CONSTRAINT FK_95D326DC9CAA2B25 FOREIGN KEY (translation_id) REFERENCES translations (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tags_translations_documents ADD CONSTRAINT FK_6E886F1F22010F1 FOREIGN KEY (tag_translation_id) REFERENCES tags_translations (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tags_translations_documents ADD CONSTRAINT FK_6E886F1FC33F7837 FOREIGN KEY (document_id) REFERENCES documents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE url_aliases ADD CONSTRAINT FK_E261ED65AA2D61 FOREIGN KEY (ns_id) REFERENCES nodes_sources (id)');
        $this->addSql('ALTER TABLE user_log_entries ADD CONSTRAINT FK_BC2E42C7A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E96483A539 FOREIGN KEY (chroot_id) REFERENCES nodes (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE users_roles ADD CONSTRAINT FK_51498A8EA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE users_roles ADD CONSTRAINT FK_51498A8ED60322AC FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE users_groups ADD CONSTRAINT FK_FF8AB7E0A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE users_groups ADD CONSTRAINT FK_FF8AB7E0FE54D947 FOREIGN KEY (group_id) REFERENCES `groups` (id)');
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }

    /**
     * Temporary workaround
     *
     * @return bool
     * @see https://github.com/doctrine/migrations/issues/1104
     */
    public function isTransactional(): bool
    {
        return false;
    }
}
