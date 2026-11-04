-- phpMyAdmin SQL Dump
-- MetaERP Enterprise Modernization Database Dump
-- Compatible with MySQL 8.0+ / MariaDB 10.4+ / phpMyAdmin
-- Generated on: 2026-11-04 09:02:14
--

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

SET FOREIGN_KEY_CHECKS=0;

-- --------------------------------------------------------
-- Table structure for table `access`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `access`;
CREATE TABLE IF NOT EXISTS `access` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `accessgroup_id` bigint(20) UNSIGNED NULL,
  `module` varchar(255) NULL,
  `accessType` varchar(255) NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `accessgroups`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `accessgroups`;
CREATE TABLE IF NOT EXISTS `accessgroups` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `actions`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `actions`;
CREATE TABLE IF NOT EXISTS `actions` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NULL,
  `action` varchar(255) NULL,
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `addresses`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `addresses`;
CREATE TABLE IF NOT EXISTS `addresses` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `addressName` varchar(255) NULL,
  `addressLine1` varchar(255) NULL,
  `addressLine2` varchar(255) NULL,
  `city` varchar(255) NULL,
  `state` varchar(255) NULL,
  `postalCode` varchar(255) NULL,
  `postalBox` varchar(255) NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `forGoogleMapsSearch` varchar(255) NULL,
  `country_id` bigint(20) UNSIGNED NULL,
  `company_id` bigint(20) UNSIGNED NULL,
  `contact_id` bigint(20) UNSIGNED NULL,
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `archive`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `archive`;
CREATE TABLE IF NOT EXISTS `archive` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `file` text NULL,
  `fileName` varchar(255) NULL,
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `bankaccounts`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `bankaccounts`;
CREATE TABLE IF NOT EXISTS `bankaccounts` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) UNSIGNED NULL,
  `contact_id` bigint(20) UNSIGNED NULL,
  `name` varchar(255) NULL,
  `sortCode` varchar(255) NULL,
  `accountNumber` varchar(255) NULL,
  `iban` varchar(255) NULL,
  `swift` varchar(255) NULL,
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  `description` varchar(255) NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `checkitems`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `checkitems`;
CREATE TABLE IF NOT EXISTS `checkitems` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `checklist_id` bigint(20) UNSIGNED NULL,
  `name` varchar(255) NULL,
  `checked` tinyint(1) NOT NULL DEFAULT '0',
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `checklists`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `checklists`;
CREATE TABLE IF NOT EXISTS `checklists` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `contract_id` bigint(20) UNSIGNED NULL,
  `name` varchar(255) NULL,
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `comments`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `comments`;
CREATE TABLE IF NOT EXISTS `comments` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `contract_id` bigint(20) UNSIGNED NULL,
  `user_id` bigint(20) UNSIGNED NULL,
  `message` text NULL,
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `companies`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `companies`;
CREATE TABLE IF NOT EXISTS `companies` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(64) NOT NULL,
  `countryId` bigint(20) UNSIGNED NULL,
  `logoUrl` varchar(255) NULL,
  `companyNumber` varchar(255) NULL,
  `vatNumber` varchar(255) NULL,
  `phoneNumber` varchar(255) NULL,
  `faxNumber` varchar(255) NULL,
  `email` varchar(255) NULL,
  `webSite` varchar(255) NULL,
  `footer` varchar(255) NULL,
  `eoriNumber` varchar(255) NULL,
  `address_id` bigint(20) UNSIGNED NULL,
  `archiveLogoId` bigint(20) UNSIGNED NULL,
  `archiveFooterId` bigint(20) UNSIGNED NULL,
  `archiveStampId` bigint(20) UNSIGNED NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  `domain` varchar(255) NULL,
  `stampUrl` varchar(255) NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `contact_products`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `contact_products`;
CREATE TABLE IF NOT EXISTS `contact_products` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `contact_id` bigint(20) UNSIGNED NULL,
  `product_id` bigint(20) UNSIGNED NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `contact_types`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `contact_types`;
CREATE TABLE IF NOT EXISTS `contact_types` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `contact_id` bigint(20) UNSIGNED NULL,
  `type_id` bigint(20) UNSIGNED NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `contacts`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `contacts`;
CREATE TABLE IF NOT EXISTS `contacts` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `initials` varchar(255) NULL,
  `companyId` bigint(20) UNSIGNED NULL,
  `vat` varchar(255) NULL,
  `registration` varchar(255) NULL,
  `codeMeta` varchar(255) NULL,
  `currency_id` bigint(20) UNSIGNED NULL,
  `countryId` bigint(20) UNSIGNED NULL,
  `language_id` bigint(20) UNSIGNED NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  `eoriNumber` varchar(255) NULL,
  `website` varchar(255) NULL,
  `additionalInformation` text NULL,
  `types` varchar(255) NULL,
  `template_language` varchar(255) NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `contractOrderCodeSequence`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `contractOrderCodeSequence`;
CREATE TABLE IF NOT EXISTS `contractOrderCodeSequence` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `year` int(11) NULL DEFAULT '0',
  `lastId` bigint(20) UNSIGNED NULL,
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `contracts`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `contracts`;
CREATE TABLE IF NOT EXISTS `contracts` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `orderCode` varchar(255) NULL,
  `date` datetime NULL DEFAULT NULL,
  `type` varchar(64) NOT NULL,
  `stage_id` bigint(20) UNSIGNED NULL,
  `purchase_id` bigint(20) UNSIGNED NULL,
  `sale_id` bigint(20) UNSIGNED NULL,
  `contractStatus` varchar(255) NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `loadingDate` datetime NULL DEFAULT NULL,
  `deliveryTerms` varchar(255) NULL,
  `suppliesNote` text NULL,
  `customerPurchaseOrder` varchar(255) NULL,
  `modeOfPayment` varchar(255) NULL,
  `qualityCheck` varchar(255) NULL,
  `packing` varchar(255) NULL,
  `remarks` text NULL,
  `memberIds` text NULL,
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  `weightTolerance` varchar(255) NULL,
  `orderDescription` text NULL,
  `finishDate` datetime NULL DEFAULT NULL,
  `cancellationDate` datetime NULL DEFAULT NULL,
  `priceAgreed` varchar(255) NULL,
  `transportNotes` text NULL,
  `specialNotes` text NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `countries`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `countries`;
CREATE TABLE IF NOT EXISTS `countries` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NULL,
  `alpha2` varchar(255) NULL,
  `alpha3` varchar(255) NULL,
  `region` varchar(255) NULL,
  `subRegion` varchar(255) NULL,
  `intermediateRegion` varchar(255) NULL,
  `currencyName` varchar(255) NULL,
  `currencyCode` varchar(255) NULL,
  `currencySymbol` varchar(255) NULL,
  `vat` double(12,4) NULL DEFAULT '0.0000',
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `creditdebittype`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `creditdebittype`;
CREATE TABLE IF NOT EXISTS `creditdebittype` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `description` varchar(255) NULL,
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `currency`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `currency`;
CREATE TABLE IF NOT EXISTS `currency` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `description` varchar(255) NULL,
  `symbol` varchar(255) NULL,
  `rate` double(12,4) NULL DEFAULT '0.0000',
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `deal`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `deal`;
CREATE TABLE IF NOT EXISTS `deal` (
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
  `dollarConvertionRate` double(12,4) NULL DEFAULT '0.0000',
  `total` double(12,4) NULL DEFAULT '0.0000',
  `netTotal` double(12,4) NULL DEFAULT '0.0000',
  `dealPaymentStatus` varchar(255) NULL,
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  `paymentDate` date NULL DEFAULT NULL,
  `incoterms` varchar(255) NULL,
  `customerOrderNumber` varchar(255) NULL,
  `paymentTerms` varchar(255) NULL,
  `advancedPayment` varchar(255) NULL,
  `notes` text NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `dealproduct`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `dealproduct`;
CREATE TABLE IF NOT EXISTS `dealproduct` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `deal_id` bigint(20) UNSIGNED NULL,
  `product_id` bigint(20) UNSIGNED NULL,
  `pack_type_id` bigint(20) UNSIGNED NULL,
  `currency_id` bigint(20) UNSIGNED NULL,
  `quantity` double(12,4) NULL DEFAULT '0.0000',
  `unitPrice` double(12,4) NULL DEFAULT '0.0000',
  `pricingType` varchar(255) NULL,
  `vat` double(12,4) NULL DEFAULT '0.0000',
  `total` double(12,4) NULL DEFAULT '0.0000',
  `associatedContractId` bigint(20) UNSIGNED NULL,
  `associatedProductId` bigint(20) UNSIGNED NULL,
  `status` varchar(255) NULL,
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `department`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `department`;
CREATE TABLE IF NOT EXISTS `department` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NULL,
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `detachednote`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `detachednote`;
CREATE TABLE IF NOT EXISTS `detachednote` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `kind` varchar(255) NULL,
  `number` varchar(255) NULL,
  `date` datetime NULL DEFAULT NULL,
  `meta_company_id` bigint(20) UNSIGNED NULL,
  `contact_id` bigint(20) UNSIGNED NULL,
  `currency_id` bigint(20) UNSIGNED NULL,
  `total` double(12,4) NULL DEFAULT '0.0000',
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `detachednotedetail`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `detachednotedetail`;
CREATE TABLE IF NOT EXISTS `detachednotedetail` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `detached_note_id` bigint(20) UNSIGNED NULL,
  `description` varchar(255) NULL,
  `quantity` double(12,4) NULL DEFAULT '0.0000',
  `unitPrice` double(12,4) NULL DEFAULT '0.0000',
  `total` double(12,4) NULL DEFAULT '0.0000',
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `failed_jobs`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NULL,
  `queue` text NULL,
  `payload` longtext NULL,
  `exception` longtext NULL,
  `failed_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `generatedfiles`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `generatedfiles`;
CREATE TABLE IF NOT EXISTS `generatedfiles` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `contract_id` bigint(20) UNSIGNED NULL,
  `template_id` bigint(20) UNSIGNED NULL,
  `fileName` varchar(255) NULL,
  `html` longtext NULL,
  `fileType` varchar(255) NULL,
  `user_id` bigint(20) UNSIGNED NULL,
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `languages`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `languages`;
CREATE TABLE IF NOT EXISTS `languages` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NULL,
  `code` varchar(255) NULL,
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `logs`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `logs`;
CREATE TABLE IF NOT EXISTS `logs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `module` varchar(255) NULL,
  `dataId` bigint(20) UNSIGNED NULL,
  `dateTime` datetime NULL DEFAULT NULL,
  `userId` bigint(20) UNSIGNED NULL,
  `action` varchar(255) NULL,
  `history` text NULL,
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `migrations`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `models`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `models`;
CREATE TABLE IF NOT EXISTS `models` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NULL,
  `description` varchar(255) NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `offer`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `offer`;
CREATE TABLE IF NOT EXISTS `offer` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `orderCode` varchar(255) NULL,
  `type` varchar(255) NULL,
  `meta_company_id` bigint(20) UNSIGNED NULL,
  `contact_id` bigint(20) UNSIGNED NULL,
  `product_id` bigint(20) UNSIGNED NULL,
  `quantity` double(12,4) NULL DEFAULT '0.0000',
  `unitPrice` double(12,4) NULL DEFAULT '0.0000',
  `total` double(12,4) NULL DEFAULT '0.0000',
  `currency_id` bigint(20) UNSIGNED NULL,
  `status` varchar(255) NULL,
  `validUntil` datetime NULL DEFAULT NULL,
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  `subject` varchar(255) NULL,
  `code` varchar(255) NULL,
  `contract_code` varchar(255) NULL,
  `offer_date` datetime NULL DEFAULT NULL,
  `accepted_at` datetime NULL DEFAULT NULL,
  `description` text NULL,
  `internal_notes` text NULL,
  `delivery_plan` varchar(255) NULL,
  `quotational_period` varchar(255) NULL,
  `weights` varchar(255) NULL,
  `payment_description` varchar(255) NULL,
  `terms` varchar(255) NULL,
  `extra_information` text NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `packinglists`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `packinglists`;
CREATE TABLE IF NOT EXISTS `packinglists` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `contract_id` bigint(20) UNSIGNED NULL,
  `vesselName` varchar(255) NULL,
  `shippingLine` varchar(255) NULL,
  `blNumber` varchar(255) NULL,
  `portOfLoading` varchar(255) NULL,
  `portOfDischarge` varchar(255) NULL,
  `status` varchar(255) NULL,
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  `packingListCode` varchar(255) NULL,
  `deliveryNote` varchar(255) NULL,
  `date` date NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `packs`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `packs`;
CREATE TABLE IF NOT EXISTS `packs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `packing_list_id` bigint(20) UNSIGNED NULL,
  `pack_type_id` bigint(20) UNSIGNED NULL,
  `containerNumber` varchar(255) NULL,
  `sealNumber` varchar(255) NULL,
  `grossWeight` double(12,4) NULL DEFAULT '0.0000',
  `netWeight` double(12,4) NULL DEFAULT '0.0000',
  `tareWeight` double(12,4) NULL DEFAULT '0.0000',
  `quantity` int(11) NULL DEFAULT '0',
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  `product_id` bigint(20) UNSIGNED NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `packtype`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `packtype`;
CREATE TABLE IF NOT EXISTS `packtype` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `description` varchar(255) NULL,
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `password_reset_tokens`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `paymenttermstype`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `paymenttermstype`;
CREATE TABLE IF NOT EXISTS `paymenttermstype` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `description` varchar(255) NULL,
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `paymenttype`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `paymenttype`;
CREATE TABLE IF NOT EXISTS `paymenttype` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `description` varchar(255) NULL,
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `permissions`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `permissions`;
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NULL,
  `company_id` bigint(20) UNSIGNED NULL,
  `accessgroup_id` bigint(20) UNSIGNED NULL,
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `personal_access_tokens`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `personal_access_tokens`;
CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NULL,
  `tokenable_id` bigint(20) UNSIGNED NULL,
  `name` varchar(255) NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text NULL,
  `last_used_at` datetime NULL DEFAULT NULL,
  `expires_at` datetime NULL DEFAULT NULL,
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `productcontracts`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `productcontracts`;
CREATE TABLE IF NOT EXISTS `productcontracts` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `contract_id` bigint(20) UNSIGNED NULL,
  `product_id` bigint(20) UNSIGNED NULL,
  `quantity` double(12,4) NULL DEFAULT '0.0000',
  `unitPrice` double(12,4) NULL DEFAULT '0.0000',
  `status` varchar(255) NULL,
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `products`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text NULL,
  `code` varchar(255) NULL,
  `productCategory` varchar(255) NULL,
  `chemicalFormula` varchar(255) NULL,
  `unitOfMeasurement` varchar(255) NULL,
  `hsCode` varchar(255) NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  `basel_code` varchar(255) NULL,
  `ec_waste_code` varchar(255) NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `representatives`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `representatives`;
CREATE TABLE IF NOT EXISTS `representatives` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) UNSIGNED NULL,
  `contact_id` bigint(20) UNSIGNED NULL,
  `address_id` bigint(20) UNSIGNED NULL,
  `name` varchar(255) NULL,
  `email` varchar(255) NULL,
  `phone` varchar(255) NULL,
  `type` varchar(255) NULL,
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  `job_title` varchar(255) NULL,
  `phone2` varchar(255) NULL,
  `mobile` varchar(255) NULL,
  `department` varchar(255) NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `stageactions`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `stageactions`;
CREATE TABLE IF NOT EXISTS `stageactions` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `stage_id` bigint(20) UNSIGNED NULL,
  `action_id` bigint(20) UNSIGNED NULL,
  `triggerType` varchar(255) NULL,
  `priority` int(11) NULL DEFAULT '0',
  `contractTypesNotAllowed` varchar(255) NULL,
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `stages`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `stages`;
CREATE TABLE IF NOT EXISTS `stages` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `model_id` bigint(20) UNSIGNED NULL,
  `name` varchar(255) NULL,
  `description` varchar(255) NULL,
  `priority` int(11) NULL DEFAULT '0',
  `stageType` varchar(255) NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `templates`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `templates`;
CREATE TABLE IF NOT EXISTS `templates` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NULL,
  `type` varchar(255) NULL,
  `language_id` bigint(20) UNSIGNED NULL,
  `html` longtext NULL,
  `keys` text NULL,
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `types`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `types`;
CREATE TABLE IF NOT EXISTS `types` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `description` varchar(255) NULL,
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `uploadedfiles`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `uploadedfiles`;
CREATE TABLE IF NOT EXISTS `uploadedfiles` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `contract_id` bigint(20) UNSIGNED NULL,
  `fileName` varchar(255) NULL,
  `filePath` varchar(255) NULL,
  `fileSize` int(11) NULL DEFAULT '0',
  `contentType` varchar(255) NULL,
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `displayName` varchar(255) NULL,
  `photoUrl` varchar(255) NULL,
  `signatureFile` longtext NULL,
  `signature_file_name` varchar(255) NULL,
  `occupation` varchar(255) NULL,
  `username` varchar(191) NOT NULL,
  `password` varchar(255) NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `role` varchar(255) NULL,
  `secret` varchar(255) NULL,
  `enable2fa` tinyint(1) NOT NULL DEFAULT '0',
  `archiveSignatureId` bigint(20) UNSIGNED NULL,
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `remember_token` varchar(255) NULL,
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  `email` varchar(255) NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_unique` (`username`),
  KEY `users_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


SET FOREIGN_KEY_CHECKS=1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;