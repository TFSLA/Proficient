use `psa_beta`;

#DROP TABLE IF EXISTS files;
DROP TABLE IF EXISTS files_versions;
DROP TABLE IF EXISTS new_files;

#RENAME TABLE files_old TO files;

DROP TABLE IF EXISTS files_index;  /* Esta tabla nunca se uso*/

CREATE TABLE IF NOT EXISTS `new_files` (
  `file_id` INTEGER NOT NULL DEFAULT NULL AUTO_INCREMENT,
  `file_name` VARCHAR(255) NOT NULL,
  `file_description` TEXT NOT NULL,
  `file_project` INTEGER NOT NULL,
  `file_task` INTEGER DEFAULT 0,
  `file_section` INTEGER UNSIGNED DEFAULT 0,
  `file_category` INTEGER UNSIGNED NOT NULL,
  `file_type` VARCHAR(100) NOT NULL,
  `file_owner` INTEGER NOT NULL,
  `file_date` DATETIME NOT NULL,
  `file_size` INTEGER NOT NULL DEFAULT 0,
  `file_delete_pending` BIT NOT NULL DEFAULT 0,
  `file_date_delete` DATETIME NOT NULL DEFAULT 0,
  PRIMARY KEY(`file_id`)
);

CREATE TABLE IF NOT EXISTS `files_versions` (
  `id_files_ver` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `file_id` INTEGER UNSIGNED NOT NULL,
  `version` FLOAT NOT NULL DEFAULT 1,
  `version_file_name` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `author` INTEGER UNSIGNED NOT NULL,
  `date` DATETIME NOT NULL,
  `delete_pending` BIT NOT NULL DEFAULT 0,
  `date_delete` DATETIME NOT NULL DEFAULT 0,
  PRIMARY KEY(`id_files_ver`),
  CONSTRAINT `file_id` FOREIGN KEY `file_id` (`file_id`)
    REFERENCES `new_files` (`file_id`)
);

insert into new_files (
       file_name, file_description, file_project, file_task, file_section, file_category, file_type, file_owner, file_date, file_size) select
       file_name, file_description, file_project, file_task, 0,            0,             file_type, file_owner, file_date, file_size from files;

insert into files_versions (
       file_id, description ) select
       file_id, "Carga inicial" from new_files;

UPDATE files_versions, new_files, files SET files_versions.version_file_name = files.file_real_filename, files_versions.date = files.file_date, files_versions.author = files.file_owner
WHERE new_files.file_id = files_versions.file_id and new_files.file_name=files.file_name;


RENAME TABLE files TO files_old;
RENAME TABLE new_files TO files;

DROP TABLE IF EXISTS files_category;

CREATE TABLE IF NOT EXISTS `files_category` (
  `category_id` INTEGER NOT NULL DEFAULT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  PRIMARY KEY(`category_id`)
);

insert into files_category (name) values ('Documento'), ('categoria2'), ('categoria3');