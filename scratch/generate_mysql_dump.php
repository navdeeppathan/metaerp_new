<?php
/**
 * MetaERP MySQL Export Generator for phpMyAdmin
 */

$sqlitePath = __DIR__ . '/../database/database.sqlite';
$pdo = new PDO('sqlite:' . $sqlitePath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Destination SQL file
$outputPath = __DIR__ . '/../metaerp_database_mysql.sql';
$out = fopen($outputPath, 'w');

function writeSql($str) {
    global $out;
    fwrite($out, $str . "\n");
}

writeSql("-- phpMyAdmin SQL Dump");
writeSql("-- MetaERP Enterprise Modernization Database Dump");
writeSql("-- Compatible with MySQL 8.0+ / MariaDB 10.4+ / phpMyAdmin");
writeSql("-- Generated on: " . date('Y-m-d H:i:s'));
writeSql("--");
writeSql("");
writeSql("SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";");
writeSql("START TRANSACTION;");
writeSql("SET time_zone = \"+00:00\";");
writeSql("");
writeSql("/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;");
writeSql("/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;");
writeSql("/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;");
writeSql("/*!40101 SET NAMES utf8mb4 */;");
writeSql("");
writeSql("--");
writeSql("-- Database: `metaerp` (or your phpMyAdmin target database)");
writeSql("-- Optional creation: Uncomment if needed");
writeSql("-- CREATE DATABASE IF NOT EXISTS `metaerp` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
writeSql("-- USE `metaerp`;");
writeSql("--");
writeSql("");
writeSql("SET FOREIGN_KEY_CHECKS=0;");
writeSql("");

// Table schemas in structured MySQL DDL
$tableSchemas = [
    'migrations' => "CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    'password_reset_tokens' => "CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    'failed_jobs' => "CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    'personal_access_tokens' => "CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    'countries' => "CREATE TABLE IF NOT EXISTS `countries` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(10) NOT NULL,
  `currencyId` bigint(20) UNSIGNED NULL,
  `phoneCode` varchar(20) NULL,
  `capital` varchar(255) NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `countries_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    'languages' => "CREATE TABLE IF NOT EXISTS `languages` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(10) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `languages_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    'currency' => "CREATE TABLE IF NOT EXISTS `currency` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(10) NOT NULL,
  `currencySymbol` varchar(10) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `currency_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    'types' => "CREATE TABLE IF NOT EXISTS `types` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    'department' => "CREATE TABLE IF NOT EXISTS `department` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    'packtype' => "CREATE TABLE IF NOT EXISTS `packtype` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    'paymenttype' => "CREATE TABLE IF NOT EXISTS `paymenttype` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    'paymenttermstype' => "CREATE TABLE IF NOT EXISTS `paymenttermstype` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    'creditdebittype' => "CREATE TABLE IF NOT EXISTS `creditdebittype` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    'users' => "CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `displayName` varchar(255) NULL,
  `photoUrl` varchar(500) NULL,
  `signatureFile` longtext NULL,
  `signature_file_name` varchar(255) NULL,
  `occupation` varchar(255) NULL,
  `username` varchar(191) NOT NULL,
  `email` varchar(191) NULL,
  `password` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `role` varchar(64) NOT NULL DEFAULT 'ROLE_USER',
  `secret` varchar(255) NULL,
  `enable2fa` tinyint(1) NOT NULL DEFAULT '0',
  `archiveSignatureId` bigint(20) UNSIGNED NULL,
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `remember_token` varchar(100) NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_unique` (`username`),
  KEY `users_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    'accessgroups' => "CREATE TABLE IF NOT EXISTS `accessgroups` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    'access' => "CREATE TABLE IF NOT EXISTS `access` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `access_group_id` bigint(20) UNSIGNED NOT NULL,
  `module` varchar(64) NOT NULL,
  `can_read` tinyint(1) NOT NULL DEFAULT '1',
  `can_write` tinyint(1) NOT NULL DEFAULT '0',
  `can_delete` tinyint(1) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    'addresses' => "CREATE TABLE IF NOT EXISTS `addresses` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `address_line_1` varchar(255) NOT NULL,
  `address_line_2` varchar(255) NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) NULL,
  `postal_code` varchar(30) NULL,
  `country_id` bigint(20) UNSIGNED NULL,
  `type` varchar(64) NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    'companies' => "CREATE TABLE IF NOT EXISTS `companies` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(64) NOT NULL,
  `countryId` bigint(20) UNSIGNED NULL,
  `logoUrl` varchar(500) NULL,
  `companyNumber` varchar(64) NULL,
  `vatNumber` varchar(64) NULL,
  `phoneNumber` varchar(64) NULL,
  `faxNumber` varchar(64) NULL,
  `email` varchar(255) NULL,
  `webSite` varchar(255) NULL,
  `footer` varchar(500) NULL,
  `eoriNumber` varchar(64) NULL,
  `address_id` bigint(20) UNSIGNED NULL,
  `archiveLogoId` bigint(20) UNSIGNED NULL,
  `archiveFooterId` bigint(20) UNSIGNED NULL,
  `archiveStampId` bigint(20) UNSIGNED NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `domain` varchar(255) NULL,
  `stampUrl` varchar(500) NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    'contacts' => "CREATE TABLE IF NOT EXISTS `contacts` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `initials` varchar(64) NULL,
  `companyId` bigint(20) UNSIGNED NULL,
  `vat` varchar(64) NULL,
  `registration` varchar(64) NULL,
  `codeMeta` varchar(64) NULL,
  `currency_id` bigint(20) UNSIGNED NULL,
  `countryId` bigint(20) UNSIGNED NULL,
  `language_id` bigint(20) UNSIGNED NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `eoriNumber` varchar(64) NULL,
  `website` varchar(255) NULL,
  `additionalInformation` text NULL,
  `types` varchar(64) NULL,
  `template_language` varchar(64) NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    'bankaccounts' => "CREATE TABLE IF NOT EXISTS `bankaccounts` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) UNSIGNED NULL,
  `contact_id` bigint(20) UNSIGNED NULL,
  `name` varchar(255) NOT NULL,
  `sortCode` varchar(64) NULL,
  `accountNumber` varchar(64) NULL,
  `iban` varchar(64) NULL,
  `swift` varchar(64) NULL,
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `description` varchar(255) NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    'representatives' => "CREATE TABLE IF NOT EXISTS `representatives` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) UNSIGNED NULL,
  `contact_id` bigint(20) UNSIGNED NULL,
  `address_id` bigint(20) UNSIGNED NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NULL,
  `phone` varchar(64) NULL,
  `type` varchar(64) NULL,
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `job_title` varchar(255) NULL,
  `phone2` varchar(64) NULL,
  `mobile` varchar(64) NULL,
  `department` varchar(64) NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    'permissions' => "CREATE TABLE IF NOT EXISTS `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL,
  `can_read` tinyint(1) NOT NULL DEFAULT '1',
  `can_write` tinyint(1) NOT NULL DEFAULT '0',
  `can_delete` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    'contact_types' => "CREATE TABLE IF NOT EXISTS `contact_types` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `contact_id` bigint(20) UNSIGNED NOT NULL,
  `type_id` bigint(20) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    'products' => "CREATE TABLE IF NOT EXISTS `products` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text NULL,
  `code` varchar(64) NULL,
  `productCategory` varchar(64) NULL,
  `chemicalFormula` varchar(255) NULL,
  `unitOfMeasurement` varchar(64) NULL,
  `hsCode` varchar(64) NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `basel_code` varchar(64) NULL,
  `ec_waste_code` varchar(64) NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    'contact_products' => "CREATE TABLE IF NOT EXISTS `contact_products` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `contact_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    'productcontracts' => "CREATE TABLE IF NOT EXISTS `productcontracts` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `contract_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` double(12,4) NOT NULL DEFAULT '0.0000',
  `price` double(12,4) NOT NULL DEFAULT '0.0000',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    'models' => "CREATE TABLE IF NOT EXISTS `models` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    'stages' => "CREATE TABLE IF NOT EXISTS `stages` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `model_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NULL,
  `priority` int(11) NOT NULL DEFAULT '0',
  `stageType` varchar(64) NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    'actions' => "CREATE TABLE IF NOT EXISTS `actions` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    'stageactions' => "CREATE TABLE IF NOT EXISTS `stageactions` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `stage_id` bigint(20) UNSIGNED NOT NULL,
  `action_id` bigint(20) UNSIGNED NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    'contractOrderCodeSequence' => "CREATE TABLE IF NOT EXISTS `contractOrderCodeSequence` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_code` varchar(64) NOT NULL,
  `category` varchar(64) NOT NULL,
  `year` int(11) NOT NULL,
  `last_sequence` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    'deal' => "CREATE TABLE IF NOT EXISTS `deal` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `meta_company_id` bigint(20) UNSIGNED NULL,
  `contact_id` bigint(20) UNSIGNED NULL,
  `contact_representative_id` bigint(20) UNSIGNED NULL,
  `meta_representative_id` bigint(20) UNSIGNED NULL,
  `address_id` bigint(20) UNSIGNED NULL,
  `destination_address_id` bigint(20) UNSIGNED NULL,
  `currency_id` bigint(20) UNSIGNED NULL,
  `payment_type_id` bigint(20) UNSIGNED NULL,
  `payment_terms_type_id` bigint(20) UNSIGNED NULL,
  `bank_account_id` bigint(20) UNSIGNED NULL,
  `dollarConvertionRate` double(12,4) NULL DEFAULT '1.0000',
  `total` double(14,4) NULL DEFAULT '0.0000',
  `netTotal` double(14,4) NULL DEFAULT '0.0000',
  `dealPaymentStatus` varchar(64) NULL DEFAULT 'OPEN',
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `paymentDate` date NULL,
  `incoterms` varchar(64) NULL,
  `customerOrderNumber` varchar(64) NULL,
  `paymentTerms` varchar(255) NULL,
  `advancedPayment` varchar(64) NULL,
  `notes` text NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    'dealproduct' => "CREATE TABLE IF NOT EXISTS `dealproduct` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `deal_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `pack_type_id` bigint(20) UNSIGNED NULL,
  `currency_id` bigint(20) UNSIGNED NULL,
  `quantity` double(12,4) NOT NULL DEFAULT '0.0000',
  `unitPrice` double(12,4) NOT NULL DEFAULT '0.0000',
  `pricingType` varchar(64) NULL,
  `vat` double(8,4) NULL DEFAULT '0.0000',
  `total` double(14,4) NULL DEFAULT '0.0000',
  `associatedContractId` bigint(20) UNSIGNED NULL,
  `associatedProductId` bigint(20) UNSIGNED NULL,
  `status` varchar(64) NULL DEFAULT 'PENDING',
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    'contracts' => "CREATE TABLE IF NOT EXISTS `contracts` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `orderCode` varchar(100) NULL,
  `date` datetime NULL,
  `type` varchar(64) NOT NULL,
  `stage_id` bigint(20) UNSIGNED NULL,
  `purchase_id` bigint(20) UNSIGNED NULL,
  `sale_id` bigint(20) UNSIGNED NULL,
  `contractStatus` varchar(64) NOT NULL DEFAULT 'ACTIVE',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `loadingDate` datetime NULL,
  `deliveryTerms` varchar(255) NULL,
  `suppliesNote` text NULL,
  `customerPurchaseOrder` varchar(255) NULL,
  `modeOfPayment` varchar(255) NULL,
  `qualityCheck` varchar(255) NULL,
  `packing` varchar(255) NULL,
  `remarks` text NULL,
  `memberIds` text NULL,
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `weightTolerance` varchar(64) NULL,
  `orderDescription` text NULL,
  `finishDate` datetime NULL,
  `cancellationDate` datetime NULL,
  `priceAgreed` varchar(64) NULL,
  `transportNotes` text NULL,
  `specialNotes` text NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    'packinglists' => "CREATE TABLE IF NOT EXISTS `packinglists` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `contract_id` bigint(20) UNSIGNED NOT NULL,
  `vesselName` varchar(255) NULL,
  `shippingLine` varchar(255) NULL,
  `blNumber` varchar(64) NULL,
  `portOfLoading` varchar(255) NULL,
  `portOfDischarge` varchar(255) NULL,
  `status` varchar(64) NULL DEFAULT 'OPEN',
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `packingListCode` varchar(64) NULL,
  `deliveryNote` varchar(64) NULL,
  `date` date NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    'packs' => "CREATE TABLE IF NOT EXISTS `packs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `packing_list_id` bigint(20) UNSIGNED NOT NULL,
  `pack_type_id` bigint(20) UNSIGNED NULL,
  `containerNumber` varchar(64) NULL,
  `sealNumber` varchar(64) NULL,
  `grossWeight` double(12,4) NULL DEFAULT '0.0000',
  `netWeight` double(12,4) NULL DEFAULT '0.0000',
  `tareWeight` double(12,4) NULL DEFAULT '0.0000',
  `quantity` int(11) NULL DEFAULT '0',
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `product_id` bigint(20) UNSIGNED NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    'templates' => "CREATE TABLE IF NOT EXISTS `templates` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `type` varchar(64) NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    'generatedfiles' => "CREATE TABLE IF NOT EXISTS `generatedfiles` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `contract_id` bigint(20) UNSIGNED NOT NULL,
  `template_id` bigint(20) UNSIGNED NULL,
  `fileName` varchar(255) NOT NULL,
  `fileUrl` varchar(500) NOT NULL,
  `fileType` varchar(64) NULL,
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    'uploadedfiles' => "CREATE TABLE IF NOT EXISTS `uploadedfiles` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `contract_id` bigint(20) UNSIGNED NOT NULL,
  `fileName` varchar(255) NOT NULL,
  `fileUrl` varchar(500) NOT NULL,
  `fileType` varchar(64) NULL,
  `fileSize` bigint(20) UNSIGNED NULL,
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    'archive' => "CREATE TABLE IF NOT EXISTS `archive` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `entity_type` varchar(64) NOT NULL,
  `entity_id` bigint(20) UNSIGNED NOT NULL,
  `fileUrl` varchar(500) NOT NULL,
  `fileName` varchar(255) NULL,
  `fileType` varchar(64) NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    'checklists' => "CREATE TABLE IF NOT EXISTS `checklists` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `contract_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    'checkitems' => "CREATE TABLE IF NOT EXISTS `checkitems` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `checklist_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `isChecked` tinyint(1) NOT NULL DEFAULT '0',
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    'comments' => "CREATE TABLE IF NOT EXISTS `comments` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `contract_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    'offer' => "CREATE TABLE IF NOT EXISTS `offer` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `orderCode` varchar(64) NULL,
  `type` varchar(64) NOT NULL DEFAULT 'SELL',
  `meta_company_id` bigint(20) UNSIGNED NULL,
  `contact_id` bigint(20) UNSIGNED NULL,
  `product_id` bigint(20) UNSIGNED NULL,
  `quantity` double(12,4) NULL DEFAULT '0.0000',
  `unitPrice` double(12,4) NULL DEFAULT '0.0000',
  `total` double(14,4) NULL DEFAULT '0.0000',
  `currency_id` bigint(20) UNSIGNED NULL,
  `status` varchar(64) NULL DEFAULT 'OPEN',
  `validUntil` datetime NULL,
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `subject` varchar(255) NULL,
  `code` varchar(64) NULL,
  `contract_code` varchar(64) NULL,
  `offer_date` datetime NULL,
  `accepted_at` datetime NULL,
  `description` text NULL,
  `internal_notes` text NULL,
  `delivery_plan` varchar(255) NULL,
  `quotational_period` varchar(255) NULL,
  `weights` varchar(255) NULL,
  `payment_description` varchar(255) NULL,
  `terms` varchar(255) NULL,
  `extra_information` text NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    'detachednote' => "CREATE TABLE IF NOT EXISTS `detachednote` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    'detachednotedetail' => "CREATE TABLE IF NOT EXISTS `detachednotedetail` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `detached_note_id` bigint(20) UNSIGNED NOT NULL,
  `content` text NOT NULL,
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    'logs' => "CREATE TABLE IF NOT EXISTS `logs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `module` varchar(64) NOT NULL,
  `dataId` bigint(20) UNSIGNED NOT NULL,
  `dateTime` datetime NOT NULL,
  `userId` bigint(20) UNSIGNED NOT NULL,
  `action` varchar(64) NOT NULL,
  `history` text NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
];

echo "Exporting tables and data...\n";

foreach ($tableSchemas as $tableName => $createSql) {
    writeSql("-- --------------------------------------------------------");
    writeSql("--");
    writeSql("-- Table structure for table `{$tableName}`");
    writeSql("--");
    writeSql("");
    writeSql("DROP TABLE IF EXISTS `{$tableName}`;");
    writeSql($createSql);
    writeSql("");

    // Check if table exists in SQLite
    $chk = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='{$tableName}'")->fetch();
    if (!$chk) {
        continue;
    }

    // Get column names from SQLite
    $cols = $pdo->query("PRAGMA table_info(\"{$tableName}\")")->fetchAll(PDO::FETCH_ASSOC);
    $colNames = array_map(function($c) { return $c['name']; }, $cols);

    // Fetch all rows
    $rows = $pdo->query("SELECT * FROM \"{$tableName}\"")->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($rows)) {
        writeSql("--");
        writeSql("-- Dumping data for table `{$tableName}`");
        writeSql("--");
        writeSql("");

        $quotedCols = array_map(function($c) { return "`{$c}`"; }, $colNames);
        $colListStr = implode(', ', $quotedCols);

        $batchSize = 50;
        $totalRows = count($rows);
        
        for ($i = 0; $i < $totalRows; $i += $batchSize) {
            $slice = array_slice($rows, $i, $batchSize);
            $valuesRows = [];

            foreach ($slice as $row) {
                $vals = [];
                foreach ($colNames as $col) {
                    $val = $row[$col];
                    if ($val === null) {
                        $vals[] = "NULL";
                    } elseif (is_numeric($val) && !is_string($val)) {
                        $vals[] = $val;
                    } elseif (is_bool($val)) {
                        $vals[] = $val ? "1" : "0";
                    } else {
                        // Check if numeric string that isn't a phone number, code, or leading zero
                        if (is_numeric($val) && strlen($val) < 15 && !str_starts_with((string)$val, '0') && !str_contains((string)$val, '+') && !str_contains((string)$val, '-') && !str_contains((string)$val, ':')) {
                            // Can be numeric or string
                            $vals[] = "'" . addcslashes($val, "\0..\37'\x5c") . "'";
                        } else {
                            $vals[] = "'" . addcslashes($val, "\0..\37'\x5c") . "'";
                        }
                    }
                }
                $valuesRows[] = "(" . implode(', ', $vals) . ")";
            }

            writeSql("INSERT INTO `{$tableName}` ({$colListStr}) VALUES");
            writeSql(implode(",\n", $valuesRows) . ";");
            writeSql("");
        }
    }
}

writeSql("SET FOREIGN_KEY_CHECKS=1;");
writeSql("COMMIT;");
writeSql("");
writeSql("/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;");
writeSql("/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;");
writeSql("/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;");

fclose($out);
echo "SQL Export completed successfully! Saved to: " . realpath($outputPath) . "\n";
echo "File size: " . filesize($outputPath) . " bytes\n";
