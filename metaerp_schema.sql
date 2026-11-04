-- phpMyAdmin SQL Dump
-- MetaERP Enterprise Database Dump (100% Dynamic Schema Sync)
-- Generated on: 2026-11-04 12:06:20
-- Compatible with MySQL 8.0+ / MariaDB 10.4+ / phpMyAdmin

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

SET FOREIGN_KEY_CHECKS=0;

-- --------------------------------------------------------
--
-- Table structure for table `access`
--

DROP TABLE IF EXISTS `access`;
CREATE TABLE `access` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `accessgroup_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `module` varchar(191) NOT NULL,
  `accessType` varchar(191) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `access`
--


-- --------------------------------------------------------
--
-- Table structure for table `accessgroups`
--

DROP TABLE IF EXISTS `accessgroups`;
CREATE TABLE `accessgroups` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `accessgroups`
--


-- --------------------------------------------------------
--
-- Table structure for table `actions`
--

DROP TABLE IF EXISTS `actions`;
CREATE TABLE `actions` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `action` varchar(191) NOT NULL,
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `addresses`
--

DROP TABLE IF EXISTS `addresses`;
CREATE TABLE `addresses` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `addressName` varchar(191) NULL DEFAULT NULL,
  `addressLine1` varchar(191) NULL DEFAULT NULL,
  `addressLine2` varchar(191) NULL DEFAULT NULL,
  `city` varchar(191) NULL DEFAULT NULL,
  `state` varchar(191) NULL DEFAULT NULL,
  `postalCode` varchar(191) NULL DEFAULT NULL,
  `postalBox` varchar(191) NULL DEFAULT NULL,
  `active` tinyint(1) NULL DEFAULT '1',
  `forGoogleMapsSearch` varchar(191) NULL DEFAULT NULL,
  `country_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `company_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `contact_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `addresses`
--


-- --------------------------------------------------------
--
-- Table structure for table `archive`
--

DROP TABLE IF EXISTS `archive`;
CREATE TABLE `archive` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `file` longtext NOT NULL,
  `fileName` varchar(191) NOT NULL,
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `bankaccounts`
--

DROP TABLE IF EXISTS `bankaccounts`;
CREATE TABLE `bankaccounts` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `contact_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `name` varchar(191) NULL DEFAULT NULL,
  `sortCode` varchar(191) NULL DEFAULT NULL,
  `accountNumber` varchar(191) NULL DEFAULT NULL,
  `iban` varchar(191) NULL DEFAULT NULL,
  `swift` varchar(191) NULL DEFAULT NULL,
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  `description` varchar(191) NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bankaccounts`
--


-- --------------------------------------------------------
--
-- Table structure for table `checkitems`
--

DROP TABLE IF EXISTS `checkitems`;
CREATE TABLE `checkitems` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `checklist_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `checked` tinyint(1) NOT NULL DEFAULT '0',
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `checkitems`
--


-- --------------------------------------------------------
--
-- Table structure for table `checklists`
--

DROP TABLE IF EXISTS `checklists`;
CREATE TABLE `checklists` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `contract_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `checklists`
--


-- --------------------------------------------------------
--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
CREATE TABLE `comments` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `contract_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `message` longtext NOT NULL,
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `comments`
--


-- --------------------------------------------------------
--
-- Table structure for table `companies`
--

DROP TABLE IF EXISTS `companies`;
CREATE TABLE `companies` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `code` varchar(191) NOT NULL,
  `countryId` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `logoUrl` varchar(191) NULL DEFAULT NULL,
  `companyNumber` varchar(191) NULL DEFAULT NULL,
  `vatNumber` varchar(191) NULL DEFAULT NULL,
  `phoneNumber` varchar(191) NULL DEFAULT NULL,
  `faxNumber` varchar(191) NULL DEFAULT NULL,
  `email` varchar(191) NULL DEFAULT NULL,
  `webSite` varchar(191) NULL DEFAULT NULL,
  `footer` varchar(191) NULL DEFAULT NULL,
  `eoriNumber` varchar(191) NULL DEFAULT NULL,
  `address_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `archiveLogoId` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `archiveFooterId` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `archiveStampId` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  `domain` varchar(191) NULL DEFAULT NULL,
  `stampUrl` varchar(191) NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `companies`
--


-- --------------------------------------------------------
--
-- Table structure for table `contact_products`
--

DROP TABLE IF EXISTS `contact_products`;
CREATE TABLE `contact_products` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `contact_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contact_products`
--


-- --------------------------------------------------------
--
-- Table structure for table `contact_types`
--

DROP TABLE IF EXISTS `contact_types`;
CREATE TABLE `contact_types` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `contact_id` bigint(20) UNSIGNED NOT NULL,
  `type_id` bigint(20) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contact_types`
--


-- --------------------------------------------------------
--
-- Table structure for table `contacts`
--

DROP TABLE IF EXISTS `contacts`;
CREATE TABLE `contacts` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `initials` varchar(191) NULL DEFAULT NULL,
  `companyId` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `vat` varchar(191) NULL DEFAULT NULL,
  `registration` varchar(191) NULL DEFAULT NULL,
  `codeMeta` varchar(191) NULL DEFAULT NULL,
  `currency_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `countryId` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `language_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  `eoriNumber` varchar(191) NULL DEFAULT NULL,
  `website` varchar(191) NULL DEFAULT NULL,
  `additionalInformation` longtext NULL DEFAULT NULL,
  `types` varchar(191) NULL DEFAULT NULL,
  `template_language` varchar(191) NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contacts`
--


-- --------------------------------------------------------
--
-- Table structure for table `contractOrderCodeSequence`
--

DROP TABLE IF EXISTS `contractOrderCodeSequence`;
CREATE TABLE `contractOrderCodeSequence` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `year` bigint(20) UNSIGNED NOT NULL,
  `lastId` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contractOrderCodeSequence`
--


-- --------------------------------------------------------
--
-- Table structure for table `contracts`
--

DROP TABLE IF EXISTS `contracts`;
CREATE TABLE `contracts` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `orderCode` varchar(191) NULL DEFAULT NULL,
  `date` datetime NULL DEFAULT NULL,
  `type` varchar(191) NOT NULL,
  `stage_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `purchase_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `sale_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `contractStatus` varchar(191) NOT NULL DEFAULT 'ACTIVE',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `loadingDate` datetime NULL DEFAULT NULL,
  `deliveryTerms` varchar(191) NULL DEFAULT NULL,
  `suppliesNote` longtext NULL DEFAULT NULL,
  `customerPurchaseOrder` varchar(191) NULL DEFAULT NULL,
  `modeOfPayment` varchar(191) NULL DEFAULT NULL,
  `qualityCheck` varchar(191) NULL DEFAULT NULL,
  `packing` varchar(191) NULL DEFAULT NULL,
  `remarks` longtext NULL DEFAULT NULL,
  `memberIds` longtext NULL DEFAULT NULL,
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  `weightTolerance` varchar(191) NULL DEFAULT NULL,
  `orderDescription` longtext NULL DEFAULT NULL,
  `finishDate` datetime NULL DEFAULT NULL,
  `cancellationDate` datetime NULL DEFAULT NULL,
  `priceAgreed` varchar(191) NULL DEFAULT NULL,
  `transportNotes` longtext NULL DEFAULT NULL,
  `specialNotes` longtext NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contracts`
--


-- --------------------------------------------------------
--
-- Table structure for table `countries`
--

DROP TABLE IF EXISTS `countries`;
CREATE TABLE `countries` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `alpha2` varchar(191) NULL DEFAULT NULL,
  `alpha3` varchar(191) NULL DEFAULT NULL,
  `region` varchar(191) NULL DEFAULT NULL,
  `subRegion` varchar(191) NULL DEFAULT NULL,
  `intermediateRegion` varchar(191) NULL DEFAULT NULL,
  `currencyName` varchar(191) NULL DEFAULT NULL,
  `currencyCode` varchar(191) NULL DEFAULT NULL,
  `currencySymbol` varchar(191) NULL DEFAULT NULL,
  `vat` double NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `countries`
--


-- --------------------------------------------------------
--
-- Table structure for table `creditdebittype`
--

DROP TABLE IF EXISTS `creditdebittype`;
CREATE TABLE `creditdebittype` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `description` varchar(191) NOT NULL,
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `creditdebittype`
--


-- --------------------------------------------------------
--
-- Table structure for table `currency`
--

DROP TABLE IF EXISTS `currency`;
CREATE TABLE `currency` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `description` varchar(191) NOT NULL,
  `symbol` varchar(191) NULL DEFAULT NULL,
  `rate` double NOT NULL DEFAULT '1',
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  `name` varchar(191) NULL DEFAULT NULL,
  `abbreviation` varchar(191) NULL DEFAULT NULL,
  `htmlEncodeSymbol` varchar(191) NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `currency`
--


-- --------------------------------------------------------
--
-- Table structure for table `deal`
--

DROP TABLE IF EXISTS `deal`;
CREATE TABLE `deal` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `meta_company_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `contact_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `contact_representative_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `meta_representative_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `address_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `destination_address_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `currency_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `payment_type_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `payment_terms_type_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `bank_account_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `dollarConvertionRate` double NOT NULL DEFAULT '1',
  `total` double NOT NULL DEFAULT '0',
  `netTotal` double NOT NULL DEFAULT '0',
  `dealPaymentStatus` varchar(191) NULL DEFAULT NULL,
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  `paymentDate` datetime NULL DEFAULT NULL,
  `incoterms` varchar(191) NULL DEFAULT NULL,
  `customerOrderNumber` varchar(191) NULL DEFAULT NULL,
  `paymentTerms` varchar(191) NULL DEFAULT NULL,
  `advancedPayment` varchar(191) NULL DEFAULT NULL,
  `notes` longtext NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `deal`
--


-- --------------------------------------------------------
--
-- Table structure for table `dealproduct`
--

DROP TABLE IF EXISTS `dealproduct`;
CREATE TABLE `dealproduct` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `deal_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `pack_type_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `currency_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `quantity` double NOT NULL DEFAULT '0',
  `unitPrice` double NOT NULL DEFAULT '0',
  `pricingType` varchar(191) NULL DEFAULT NULL,
  `vat` double NOT NULL DEFAULT '0',
  `total` double NOT NULL DEFAULT '0',
  `associatedContractId` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `associatedProductId` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `status` varchar(191) NULL DEFAULT NULL,
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dealproduct`
--


-- --------------------------------------------------------
--
-- Table structure for table `department`
--

DROP TABLE IF EXISTS `department`;
CREATE TABLE `department` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  `description` varchar(191) NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `department`
--


-- --------------------------------------------------------
--
-- Table structure for table `detachednote`
--

DROP TABLE IF EXISTS `detachednote`;
CREATE TABLE `detachednote` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `kind` varchar(191) NOT NULL,
  `number` varchar(191) NULL DEFAULT NULL,
  `date` datetime NULL DEFAULT NULL,
  `meta_company_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `contact_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `currency_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `total` double NOT NULL DEFAULT '0',
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `detachednotedetail`
--

DROP TABLE IF EXISTS `detachednotedetail`;
CREATE TABLE `detachednotedetail` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `detached_note_id` bigint(20) UNSIGNED NOT NULL,
  `description` varchar(191) NOT NULL,
  `quantity` double NOT NULL DEFAULT '0',
  `unitPrice` double NOT NULL DEFAULT '0',
  `total` double NOT NULL DEFAULT '0',
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) NOT NULL,
  `connection` longtext NOT NULL,
  `queue` longtext NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `generatedfiles`
--

DROP TABLE IF EXISTS `generatedfiles`;
CREATE TABLE `generatedfiles` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `contract_id` bigint(20) UNSIGNED NOT NULL,
  `template_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `fileName` varchar(191) NOT NULL,
  `html` longtext NOT NULL,
  `fileType` varchar(191) NOT NULL DEFAULT 'HTML',
  `user_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `generatedfiles`
--


-- --------------------------------------------------------
--
-- Table structure for table `languages`
--

DROP TABLE IF EXISTS `languages`;
CREATE TABLE `languages` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `code` varchar(191) NOT NULL,
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `languages`
--


-- --------------------------------------------------------
--
-- Table structure for table `logs`
--

DROP TABLE IF EXISTS `logs`;
CREATE TABLE `logs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `module` varchar(191) NOT NULL,
  `dataId` bigint(20) UNSIGNED NOT NULL,
  `dateTime` datetime NOT NULL,
  `userId` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `action` varchar(191) NOT NULL,
  `history` longtext NULL DEFAULT NULL,
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `logs`
--


-- --------------------------------------------------------
--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) NOT NULL,
  `batch` bigint(20) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--


-- --------------------------------------------------------
--
-- Table structure for table `models`
--

DROP TABLE IF EXISTS `models`;
CREATE TABLE `models` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `description` varchar(191) NULL DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `models`
--


-- --------------------------------------------------------
--
-- Table structure for table `offer`
--

DROP TABLE IF EXISTS `offer`;
CREATE TABLE `offer` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `orderCode` varchar(191) NULL DEFAULT NULL,
  `type` varchar(191) NOT NULL,
  `meta_company_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `contact_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `product_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `quantity` double NOT NULL DEFAULT '0',
  `unitPrice` double NOT NULL DEFAULT '0',
  `total` double NOT NULL DEFAULT '0',
  `currency_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `status` varchar(191) NOT NULL DEFAULT 'OPEN',
  `validUntil` datetime NULL DEFAULT NULL,
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  `subject` varchar(191) NULL DEFAULT NULL,
  `code` varchar(191) NULL DEFAULT NULL,
  `contract_code` varchar(191) NULL DEFAULT NULL,
  `offer_date` datetime NULL DEFAULT NULL,
  `accepted_at` datetime NULL DEFAULT NULL,
  `description` longtext NULL DEFAULT NULL,
  `internal_notes` longtext NULL DEFAULT NULL,
  `delivery_plan` varchar(191) NULL DEFAULT NULL,
  `quotational_period` varchar(191) NULL DEFAULT NULL,
  `weights` varchar(191) NULL DEFAULT NULL,
  `payment_description` varchar(191) NULL DEFAULT NULL,
  `terms` varchar(191) NULL DEFAULT NULL,
  `extra_information` longtext NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `offer`
--


-- --------------------------------------------------------
--
-- Table structure for table `packinglists`
--

DROP TABLE IF EXISTS `packinglists`;
CREATE TABLE `packinglists` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `contract_id` bigint(20) UNSIGNED NOT NULL,
  `vesselName` varchar(191) NULL DEFAULT NULL,
  `shippingLine` varchar(191) NULL DEFAULT NULL,
  `blNumber` varchar(191) NULL DEFAULT NULL,
  `portOfLoading` varchar(191) NULL DEFAULT NULL,
  `portOfDischarge` varchar(191) NULL DEFAULT NULL,
  `status` varchar(191) NOT NULL DEFAULT 'ACTIVE',
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  `packingListCode` varchar(191) NULL DEFAULT NULL,
  `deliveryNote` varchar(191) NULL DEFAULT NULL,
  `date` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `packinglists`
--


-- --------------------------------------------------------
--
-- Table structure for table `packs`
--

DROP TABLE IF EXISTS `packs`;
CREATE TABLE `packs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `packing_list_id` bigint(20) UNSIGNED NOT NULL,
  `pack_type_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `containerNumber` varchar(191) NULL DEFAULT NULL,
  `sealNumber` varchar(191) NULL DEFAULT NULL,
  `grossWeight` double NOT NULL DEFAULT '0',
  `netWeight` double NOT NULL DEFAULT '0',
  `tareWeight` double NOT NULL DEFAULT '0',
  `quantity` bigint(20) UNSIGNED NOT NULL DEFAULT '1',
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  `product_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `packs`
--


-- --------------------------------------------------------
--
-- Table structure for table `packtype`
--

DROP TABLE IF EXISTS `packtype`;
CREATE TABLE `packtype` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `description` varchar(191) NOT NULL,
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `packtype`
--


-- --------------------------------------------------------
--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(191) NOT NULL,
  `token` varchar(191) NOT NULL,
  `created_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `paymenttermstype`
--

DROP TABLE IF EXISTS `paymenttermstype`;
CREATE TABLE `paymenttermstype` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `description` varchar(191) NOT NULL,
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `paymenttermstype`
--


-- --------------------------------------------------------
--
-- Table structure for table `paymenttype`
--

DROP TABLE IF EXISTS `paymenttype`;
CREATE TABLE `paymenttype` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `description` varchar(191) NOT NULL,
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `paymenttype`
--


-- --------------------------------------------------------
--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `company_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `accessgroup_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--


-- --------------------------------------------------------
--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(191) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `token` varchar(191) NOT NULL,
  `abilities` longtext NULL DEFAULT NULL,
  `last_used_at` datetime NULL DEFAULT NULL,
  `expires_at` datetime NULL DEFAULT NULL,
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `personal_access_tokens`
--


-- --------------------------------------------------------
--
-- Table structure for table `productcontracts`
--

DROP TABLE IF EXISTS `productcontracts`;
CREATE TABLE `productcontracts` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `contract_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `product_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `quantity` double NOT NULL DEFAULT '0',
  `unitPrice` double NOT NULL DEFAULT '0',
  `status` varchar(191) NULL DEFAULT NULL,
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `description` longtext NULL DEFAULT NULL,
  `code` varchar(191) NULL DEFAULT NULL,
  `productCategory` varchar(191) NULL DEFAULT NULL,
  `chemicalFormula` varchar(191) NULL DEFAULT NULL,
  `unitOfMeasurement` varchar(191) NULL DEFAULT NULL,
  `hsCode` varchar(191) NULL DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  `basel_code` varchar(191) NULL DEFAULT NULL,
  `ec_waste_code` varchar(191) NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--


-- --------------------------------------------------------
--
-- Table structure for table `representatives`
--

DROP TABLE IF EXISTS `representatives`;
CREATE TABLE `representatives` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `contact_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `address_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `name` varchar(191) NOT NULL,
  `email` varchar(191) NULL DEFAULT NULL,
  `phone` varchar(191) NULL DEFAULT NULL,
  `type` varchar(191) NULL DEFAULT NULL,
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  `job_title` varchar(191) NULL DEFAULT NULL,
  `phone2` varchar(191) NULL DEFAULT NULL,
  `mobile` varchar(191) NULL DEFAULT NULL,
  `department` varchar(191) NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `representatives`
--


-- --------------------------------------------------------
--
-- Table structure for table `stageactions`
--

DROP TABLE IF EXISTS `stageactions`;
CREATE TABLE `stageactions` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `stage_id` bigint(20) UNSIGNED NOT NULL,
  `action_id` bigint(20) UNSIGNED NOT NULL,
  `triggerType` varchar(191) NOT NULL,
  `priority` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `contractTypesNotAllowed` varchar(191) NULL DEFAULT NULL,
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `stages`
--

DROP TABLE IF EXISTS `stages`;
CREATE TABLE `stages` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `model_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `name` varchar(191) NOT NULL,
  `description` varchar(191) NULL DEFAULT NULL,
  `priority` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `stageType` varchar(191) NULL DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stages`
--


-- --------------------------------------------------------
--
-- Table structure for table `templates`
--

DROP TABLE IF EXISTS `templates`;
CREATE TABLE `templates` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `type` varchar(191) NOT NULL,
  `language_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `html` longtext NOT NULL,
  `keys` longtext NULL DEFAULT NULL,
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `templates`
--


-- --------------------------------------------------------
--
-- Table structure for table `types`
--

DROP TABLE IF EXISTS `types`;
CREATE TABLE `types` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `description` varchar(191) NOT NULL,
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `types`
--


-- --------------------------------------------------------
--
-- Table structure for table `uploadedfiles`
--

DROP TABLE IF EXISTS `uploadedfiles`;
CREATE TABLE `uploadedfiles` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `contract_id` bigint(20) UNSIGNED NOT NULL,
  `fileName` varchar(191) NOT NULL,
  `filePath` varchar(191) NOT NULL,
  `fileSize` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `contentType` varchar(191) NULL DEFAULT NULL,
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `displayName` varchar(191) NULL DEFAULT NULL,
  `photoUrl` varchar(191) NULL DEFAULT NULL,
  `signatureFile` longtext NULL DEFAULT NULL,
  `signature_file_name` varchar(191) NULL DEFAULT NULL,
  `occupation` varchar(191) NULL DEFAULT NULL,
  `username` varchar(191) NOT NULL,
  `password` varchar(191) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `role` varchar(191) NOT NULL DEFAULT 'ROLE_USER',
  `secret` varchar(191) NULL DEFAULT NULL,
  `enable2fa` tinyint(1) NOT NULL DEFAULT '0',
  `archiveSignatureId` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `version` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `remember_token` varchar(191) NULL DEFAULT NULL,
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  `email` varchar(191) NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--


SET FOREIGN_KEY_CHECKS=1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
