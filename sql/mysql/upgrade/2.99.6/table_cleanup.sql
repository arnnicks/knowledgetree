-- Drop unused tables
DROP TABLE IF EXISTS `browse_criteria`;
DROP TABLE IF EXISTS `dependant_document_instance`;
DROP TABLE IF EXISTS `dependant_document_template`;
DROP TABLE IF EXISTS `groups_folders_approval_link`;
DROP TABLE IF EXISTS `groups_folders_link`;
DROP TABLE IF EXISTS `metadata_lookup_condition`;
DROP TABLE IF EXISTS `metadata_lookup_condition_chain`;
DROP TABLE IF EXISTS `zseq_metadata_lookup_condition`;
DROP TABLE IF EXISTS `zseq_metadata_lookup_condition_chain`;
DROP TABLE IF EXISTS `zseq_search_document_user_link`;
DROP TABLE IF EXISTS `zseq_web_documents`;
DROP TABLE IF EXISTS `zseq_web_documents_status_lookup`;
DROP TABLE IF EXISTS `zseq_web_sites`;

-- Make sure sequence tables are MyISAM to avoid transaction-safety.
ALTER TABLE `zseq_active_sessions` TYPE=MyISAM;
ALTER TABLE `zseq_archive_restoration_request` TYPE=MyISAM;
ALTER TABLE `zseq_archiving_settings` TYPE=MyISAM;
ALTER TABLE `zseq_archiving_type_lookup` TYPE=MyISAM;
ALTER TABLE `zseq_browse_criteria` TYPE=MyISAM;
ALTER TABLE `zseq_data_types` TYPE=MyISAM;
ALTER TABLE `zseq_dependant_document_instance` TYPE=MyISAM;
ALTER TABLE `zseq_dependant_document_template` TYPE=MyISAM;
ALTER TABLE `zseq_discussion_comments` TYPE=MyISAM;
ALTER TABLE `zseq_discussion_threads` TYPE=MyISAM;
ALTER TABLE `zseq_document_archiving_link` TYPE=MyISAM;
ALTER TABLE `zseq_document_fields` TYPE=MyISAM;
ALTER TABLE `zseq_document_fields_link` TYPE=MyISAM;
ALTER TABLE `zseq_document_link` TYPE=MyISAM;
ALTER TABLE `zseq_document_link_types` TYPE=MyISAM;
ALTER TABLE `zseq_document_subscriptions` TYPE=MyISAM;
ALTER TABLE `zseq_document_transaction_types_lookup` TYPE=MyISAM;
ALTER TABLE `zseq_document_transactions` TYPE=MyISAM;
ALTER TABLE `zseq_document_type_fields_link` TYPE=MyISAM;
ALTER TABLE `zseq_document_types_lookup` TYPE=MyISAM;
ALTER TABLE `zseq_documents` TYPE=MyISAM;
ALTER TABLE `zseq_folder_doctypes_link` TYPE=MyISAM;
ALTER TABLE `zseq_folder_subscriptions` TYPE=MyISAM;
ALTER TABLE `zseq_folders` TYPE=MyISAM;
ALTER TABLE `zseq_folders_users_roles_link` TYPE=MyISAM;
ALTER TABLE `zseq_groups_folders_approval_link` TYPE=MyISAM;
ALTER TABLE `zseq_groups_folders_link` TYPE=MyISAM;
ALTER TABLE `zseq_groups_groups_link` TYPE=MyISAM;
ALTER TABLE `zseq_groups_lookup` TYPE=MyISAM;
ALTER TABLE `zseq_groups_units_link` TYPE=MyISAM;
ALTER TABLE `zseq_help` TYPE=MyISAM;
ALTER TABLE `zseq_help_replacement` TYPE=MyISAM;
ALTER TABLE `zseq_links` TYPE=MyISAM;
ALTER TABLE `zseq_metadata_lookup` TYPE=MyISAM;
ALTER TABLE `zseq_mime_types` TYPE=MyISAM;
ALTER TABLE `zseq_news` TYPE=MyISAM;
ALTER TABLE `zseq_organisations_lookup` TYPE=MyISAM;
ALTER TABLE `zseq_permission_assignments` TYPE=MyISAM;
ALTER TABLE `zseq_permission_descriptors` TYPE=MyISAM;
ALTER TABLE `zseq_permission_lookup_assignments` TYPE=MyISAM;
ALTER TABLE `zseq_permission_lookups` TYPE=MyISAM;
ALTER TABLE `zseq_permission_objects` TYPE=MyISAM;
ALTER TABLE `zseq_permissions` TYPE=MyISAM;
ALTER TABLE `zseq_roles` TYPE=MyISAM;
ALTER TABLE `zseq_status_lookup` TYPE=MyISAM;
ALTER TABLE `zseq_system_settings` TYPE=MyISAM;
ALTER TABLE `zseq_time_period` TYPE=MyISAM;
ALTER TABLE `zseq_time_unit_lookup` TYPE=MyISAM;
ALTER TABLE `zseq_units_lookup` TYPE=MyISAM;
ALTER TABLE `zseq_units_organisations_link` TYPE=MyISAM;
ALTER TABLE `zseq_upgrades` TYPE=MyISAM;
ALTER TABLE `zseq_users` TYPE=MyISAM;
ALTER TABLE `zseq_users_groups_link` TYPE=MyISAM;

ALTER TABLE `active_sessions` TYPE=InnoDB;
ALTER TABLE `archive_restoration_request` TYPE=InnoDB;
ALTER TABLE `archiving_settings` TYPE=InnoDB;
ALTER TABLE `archiving_type_lookup` TYPE=InnoDB;
ALTER TABLE `data_types` TYPE=InnoDB;
ALTER TABLE `discussion_comments` TYPE=InnoDB;
ALTER TABLE `discussion_threads` TYPE=InnoDB;
ALTER TABLE `document_archiving_link` TYPE=InnoDB;
ALTER TABLE `document_fields` TYPE=InnoDB;
ALTER TABLE `document_fields_link` TYPE=InnoDB;
ALTER TABLE `document_link` TYPE=InnoDB;
ALTER TABLE `document_subscriptions` TYPE=InnoDB;
ALTER TABLE `document_transaction_types_lookup` TYPE=InnoDB;
ALTER TABLE `document_transactions` TYPE=InnoDB;
ALTER TABLE `document_type_fields_link` TYPE=InnoDB;
ALTER TABLE `document_types_lookup` TYPE=InnoDB;
ALTER TABLE `documents` TYPE=InnoDB;
ALTER TABLE `folder_doctypes_link` TYPE=InnoDB;
ALTER TABLE `folder_subscriptions` TYPE=InnoDB;
ALTER TABLE `folders` TYPE=InnoDB;
ALTER TABLE `folders_users_roles_link` TYPE=InnoDB;
ALTER TABLE `groups_lookup` TYPE=InnoDB;
ALTER TABLE `groups_units_link` TYPE=InnoDB;
ALTER TABLE `help` TYPE=InnoDB;
ALTER TABLE `links` TYPE=InnoDB;
ALTER TABLE `metadata_lookup` TYPE=InnoDB;
ALTER TABLE `mime_types` TYPE=InnoDB;
ALTER TABLE `news` TYPE=InnoDB;
ALTER TABLE `organisations_lookup` TYPE=InnoDB;
ALTER TABLE `roles` TYPE=InnoDB;
ALTER TABLE `status_lookup` TYPE=InnoDB;
ALTER TABLE `system_settings` TYPE=InnoDB;
ALTER TABLE `time_period` TYPE=InnoDB;
ALTER TABLE `time_unit_lookup` TYPE=InnoDB;
ALTER TABLE `units_lookup` TYPE=InnoDB;
ALTER TABLE `units_organisations_link` TYPE=InnoDB;
ALTER TABLE `users` TYPE=InnoDB;
ALTER TABLE `users_groups_link` TYPE=InnoDB;
ALTER TABLE `web_documents` TYPE=InnoDB;
ALTER TABLE `web_documents_status_lookup` TYPE=InnoDB;
ALTER TABLE `web_sites` TYPE=InnoDB;

