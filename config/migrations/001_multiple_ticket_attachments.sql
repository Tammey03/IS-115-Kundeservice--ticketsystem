ALTER TABLE ticket_attachments
    DROP PRIMARY KEY,
    ADD COLUMN attachment_id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST,
    ADD INDEX ticket_attachments_ticket_id (ticket_id);
