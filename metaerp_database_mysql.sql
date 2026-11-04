-- phpMyAdmin SQL Dump
-- MetaERP Enterprise Modernization
-- Database: `metaerp`
-- Generated on: 2026-11-03 07:45:51

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- ----------------------------------------------------------------------------
-- OPTIONAL DATABASE CREATION (Uncomment if you haven't created a database yet):
-- CREATE DATABASE IF NOT EXISTS `metaerp` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE `metaerp`;
-- ----------------------------------------------------------------------------

SET FOREIGN_KEY_CHECKS=0;

-- --------------------------------------------------------
--
-- Table structure for table `migrations`
--

CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) not null,
  `token` varchar(255) not null,
  `created_at` timestamp null,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `failed_jobs`
--

CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint unsigned not null auto_increment,
  `uuid` varchar(255) not null,
  `connection` text not null,
  `queue` text not null,
  `payload` longtext not null,
  `exception` longtext not null,
  `failed_at` timestamp not null default CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` bigint unsigned not null auto_increment,
  `tokenable_type` varchar(255) not null,
  `tokenable_id` bigint unsigned not null,
  `name` varchar(255) not null,
  `token` varchar(64) not null,
  `abilities` text null,
  `last_used_at` timestamp null,
  `expires_at` timestamp null,
  `created_at` timestamp null,
  `updated_at` timestamp null,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `countries`
--

CREATE TABLE IF NOT EXISTS `countries` (
  `id` bigint unsigned not null auto_increment,
  `name` varchar(255) not null,
  `alpha2` varchar(2) null,
  `alpha3` varchar(3) null,
  `region` varchar(255) null,
  `subRegion` varchar(255) null,
  `intermediateRegion` varchar(255) null,
  `currencyName` varchar(255) null,
  `currencyCode` varchar(10) null,
  `currencySymbol` varchar(10) null,
  `vat` double not null default '0',
  `active` tinyint(1) not null default '1',
  `version` bigint unsigned not null default '0',
  `created_at` timestamp null,
  `updated_at` timestamp null,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `languages`
--

CREATE TABLE IF NOT EXISTS `languages` (
  `id` bigint unsigned not null auto_increment,
  `name` varchar(255) not null,
  `code` varchar(10) not null,
  `version` bigint unsigned not null default '0',
  `created_at` timestamp null,
  `updated_at` timestamp null,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `currency`
--

CREATE TABLE IF NOT EXISTS `currency` (
  `id` bigint unsigned not null auto_increment,
  `description` varchar(20) not null,
  `symbol` varchar(10) null,
  `rate` double not null default '1',
  `version` bigint unsigned not null default '0',
  `created_at` timestamp null,
  `updated_at` timestamp null,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `types`
--

CREATE TABLE IF NOT EXISTS `types` (
  `id` bigint unsigned not null auto_increment,
  `description` varchar(255) not null,
  `version` bigint unsigned not null default '0',
  `created_at` timestamp null,
  `updated_at` timestamp null,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `department`
--

CREATE TABLE IF NOT EXISTS `department` (
  `id` bigint unsigned not null auto_increment,
  `name` varchar(255) not null,
  `version` bigint unsigned not null default '0',
  `created_at` timestamp null,
  `updated_at` timestamp null,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `packtype`
--

CREATE TABLE IF NOT EXISTS `packtype` (
  `id` bigint unsigned not null auto_increment,
  `description` varchar(255) not null,
  `version` bigint unsigned not null default '0',
  `created_at` timestamp null,
  `updated_at` timestamp null,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `paymenttype`
--

CREATE TABLE IF NOT EXISTS `paymenttype` (
  `id` bigint unsigned not null auto_increment,
  `description` varchar(255) not null,
  `version` bigint unsigned not null default '0',
  `created_at` timestamp null,
  `updated_at` timestamp null,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `paymenttermstype`
--

CREATE TABLE IF NOT EXISTS `paymenttermstype` (
  `id` bigint unsigned not null auto_increment,
  `description` varchar(255) not null,
  `version` bigint unsigned not null default '0',
  `created_at` timestamp null,
  `updated_at` timestamp null,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `creditdebittype`
--

CREATE TABLE IF NOT EXISTS `creditdebittype` (
  `id` bigint unsigned not null auto_increment,
  `description` varchar(255) not null,
  `version` bigint unsigned not null default '0',
  `created_at` timestamp null,
  `updated_at` timestamp null,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint unsigned not null auto_increment,
  `name` varchar(255) not null,
  `displayName` varchar(255) null,
  `photoUrl` varchar(255) null,
  `signatureFile` longtext null,
  `signature_file_name` varchar(255) null,
  `occupation` varchar(255) null,
  `username` varchar(255) not null,
  `password` varchar(255) not null,
  `active` tinyint(1) not null default '1',
  `role` varchar(255) not null default 'ROLE_USER',
  `secret` varchar(255) null,
  `enable2fa` tinyint(1) not null default '0',
  `archiveSignatureId` bigint unsigned null,
  `version` bigint unsigned not null default '0',
  `remember_token` varchar(100) null,
  `created_at` timestamp null,
  `updated_at` timestamp null,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `accessgroups`
--

CREATE TABLE IF NOT EXISTS `accessgroups` (
  `id` bigint unsigned not null auto_increment,
  `name` varchar(255) not null,
  `active` tinyint(1) not null default '1',
  `version` bigint unsigned not null default '0',
  `created_at` timestamp null,
  `updated_at` timestamp null,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `access`
--

CREATE TABLE IF NOT EXISTS `access` (
  `id` bigint unsigned not null auto_increment,
  `accessgroup_id` bigint unsigned null,
  `module` varchar(64) not null,
  `accessType` varchar(64) not null,
  `active` tinyint(1) not null default '1',
  `version` bigint unsigned not null default '0',
  `created_at` timestamp null,
  `updated_at` timestamp null,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `addresses`
--

CREATE TABLE IF NOT EXISTS `addresses` (
  `id` bigint unsigned not null auto_increment,
  `addressName` varchar(255) null,
  `addressLine1` varchar(255) null,
  `addressLine2` varchar(255) null,
  `city` varchar(255) null,
  `state` varchar(255) null,
  `postalCode` varchar(255) null,
  `postalBox` varchar(255) null,
  `active` tinyint(1) null default '1',
  `forGoogleMapsSearch` varchar(500) null,
  `country_id` bigint unsigned null,
  `company_id` bigint unsigned null,
  `contact_id` bigint unsigned null,
  `version` bigint unsigned not null default '0',
  `created_at` timestamp null,
  `updated_at` timestamp null,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `companies`
--

CREATE TABLE IF NOT EXISTS `companies` (
  `id` bigint unsigned not null auto_increment,
  `name` varchar(255) not null,
  `code` varchar(64) not null,
  `countryId` bigint unsigned null,
  `logoUrl` varchar(500) null,
  `companyNumber` varchar(64) null,
  `vatNumber` varchar(64) null,
  `phoneNumber` varchar(64) null,
  `faxNumber` varchar(64) null,
  `email` varchar(255) null,
  `webSite` varchar(255) null,
  `footer` varchar(500) null,
  `eoriNumber` varchar(64) null,
  `address_id` bigint unsigned null,
  `archiveLogoId` bigint unsigned null,
  `archiveFooterId` bigint unsigned null,
  `archiveStampId` bigint unsigned null,
  `active` tinyint(1) not null default '1',
  `version` bigint unsigned not null default '0',
  `created_at` timestamp null,
  `updated_at` timestamp null,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `contacts`
--

CREATE TABLE IF NOT EXISTS `contacts` (
  `id` bigint unsigned not null auto_increment,
  `name` varchar(255) not null,
  `initials` varchar(64) null,
  `companyId` bigint unsigned null,
  `vat` varchar(64) null,
  `registration` varchar(64) null,
  `codeMeta` varchar(64) null,
  `currency_id` bigint unsigned null,
  `countryId` bigint unsigned null,
  `language_id` bigint unsigned null,
  `active` tinyint(1) not null default '1',
  `version` bigint unsigned not null default '0',
  `created_at` timestamp null,
  `updated_at` timestamp null,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `bankaccounts`
--

CREATE TABLE IF NOT EXISTS `bankaccounts` (
  `id` bigint unsigned not null auto_increment,
  `company_id` bigint unsigned null,
  `contact_id` bigint unsigned null,
  `name` varchar(255) null,
  `sortCode` varchar(64) null,
  `accountNumber` varchar(64) null,
  `iban` varchar(128) null,
  `swift` varchar(64) null,
  `version` bigint unsigned not null default '0',
  `created_at` timestamp null,
  `updated_at` timestamp null,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `representatives`
--

CREATE TABLE IF NOT EXISTS `representatives` (
  `id` bigint unsigned not null auto_increment,
  `company_id` bigint unsigned null,
  `contact_id` bigint unsigned null,
  `address_id` bigint unsigned null,
  `name` varchar(255) not null,
  `email` varchar(255) null,
  `phone` varchar(64) null,
  `type` varchar(64) null,
  `version` bigint unsigned not null default '0',
  `created_at` timestamp null,
  `updated_at` timestamp null,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `permissions`
--

CREATE TABLE IF NOT EXISTS `permissions` (
  `id` bigint unsigned not null auto_increment,
  `user_id` bigint unsigned null,
  `company_id` bigint unsigned null,
  `accessgroup_id` bigint unsigned null,
  `created_at` timestamp null,
  `updated_at` timestamp null,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `contact_types`
--

CREATE TABLE IF NOT EXISTS `contact_types` (
  `id` bigint unsigned not null auto_increment,
  `contact_id` bigint unsigned not null,
  `type_id` bigint unsigned not null,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `products`
--

CREATE TABLE IF NOT EXISTS `products` (
  `id` bigint unsigned not null auto_increment,
  `name` varchar(255) not null,
  `description` text null,
  `code` varchar(64) null,
  `productCategory` varchar(64) null,
  `chemicalFormula` varchar(255) null,
  `unitOfMeasurement` varchar(64) null,
  `hsCode` varchar(64) null,
  `active` tinyint(1) not null default '1',
  `version` bigint unsigned not null default '0',
  `created_at` timestamp null,
  `updated_at` timestamp null,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `contact_products`
--

CREATE TABLE IF NOT EXISTS `contact_products` (
  `id` bigint unsigned not null auto_increment,
  `contact_id` bigint unsigned not null,
  `product_id` bigint unsigned not null,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `productcontracts`
--

CREATE TABLE IF NOT EXISTS `productcontracts` (
  `id` bigint unsigned not null auto_increment,
  `contract_id` bigint unsigned null,
  `product_id` bigint unsigned null,
  `quantity` double not null default '0',
  `unitPrice` double not null default '0',
  `status` varchar(64) null,
  `version` bigint unsigned not null default '0',
  `created_at` timestamp null,
  `updated_at` timestamp null,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `models`
--

CREATE TABLE IF NOT EXISTS `models` (
  `id` bigint unsigned not null auto_increment,
  `name` varchar(255) not null,
  `description` varchar(500) null,
  `active` tinyint(1) not null default '1',
  `version` bigint unsigned not null default '0',
  `created_at` timestamp null,
  `updated_at` timestamp null,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `stages`
--

CREATE TABLE IF NOT EXISTS `stages` (
  `id` bigint unsigned not null auto_increment,
  `model_id` bigint unsigned null,
  `name` varchar(255) not null,
  `description` varchar(500) null,
  `priority` int not null default '0',
  `stageType` varchar(64) null,
  `active` tinyint(1) not null default '1',
  `version` bigint unsigned not null default '0',
  `created_at` timestamp null,
  `updated_at` timestamp null,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `actions`
--

CREATE TABLE IF NOT EXISTS `actions` (
  `id` bigint unsigned not null auto_increment,
  `name` varchar(255) not null,
  `action` varchar(64) not null,
  `version` bigint unsigned not null default '0',
  `created_at` timestamp null,
  `updated_at` timestamp null,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `stageactions`
--

CREATE TABLE IF NOT EXISTS `stageactions` (
  `id` bigint unsigned not null auto_increment,
  `stage_id` bigint unsigned not null,
  `action_id` bigint unsigned not null,
  `triggerType` varchar(64) not null,
  `priority` int not null default '0',
  `contractTypesNotAllowed` varchar(255) null,
  `version` bigint unsigned not null default '0',
  `created_at` timestamp null,
  `updated_at` timestamp null,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `contractOrderCodeSequence`
--

CREATE TABLE IF NOT EXISTS `contractOrderCodeSequence` (
  `id` bigint unsigned not null auto_increment,
  `year` int not null,
  `lastId` int not null default '0',
  `created_at` timestamp null,
  `updated_at` timestamp null,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `deal`
--

CREATE TABLE IF NOT EXISTS `deal` (
  `id` bigint unsigned not null auto_increment,
  `meta_company_id` bigint unsigned null,
  `contact_id` bigint unsigned null,
  `contact_representative_id` bigint unsigned null,
  `meta_representative_id` bigint unsigned null,
  `address_id` bigint unsigned null,
  `destination_address_id` bigint unsigned null,
  `currency_id` bigint unsigned null,
  `payment_type_id` bigint unsigned null,
  `payment_terms_type_id` bigint unsigned null,
  `bank_account_id` bigint unsigned null,
  `dollarConvertionRate` double not null default '1',
  `total` double not null default '0',
  `netTotal` double not null default '0',
  `dealPaymentStatus` varchar(64) null,
  `version` bigint unsigned not null default '0',
  `created_at` timestamp null,
  `updated_at` timestamp null,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `dealproduct`
--

CREATE TABLE IF NOT EXISTS `dealproduct` (
  `id` bigint unsigned not null auto_increment,
  `deal_id` bigint unsigned not null,
  `product_id` bigint unsigned null,
  `pack_type_id` bigint unsigned null,
  `currency_id` bigint unsigned null,
  `quantity` double not null default '0',
  `unitPrice` double not null default '0',
  `pricingType` varchar(64) null,
  `vat` double not null default '0',
  `total` double not null default '0',
  `associatedContractId` bigint unsigned null,
  `associatedProductId` bigint unsigned null,
  `status` varchar(64) null,
  `version` bigint unsigned not null default '0',
  `created_at` timestamp null,
  `updated_at` timestamp null,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `contracts`
--

CREATE TABLE IF NOT EXISTS `contracts` (
  `id` bigint unsigned not null auto_increment,
  `orderCode` varchar(100) null,
  `date` datetime null,
  `type` varchar(64) not null,
  `stage_id` bigint unsigned null,
  `purchase_id` bigint unsigned null,
  `sale_id` bigint unsigned null,
  `contractStatus` varchar(64) not null default 'ACTIVE',
  `active` tinyint(1) not null default '1',
  `loadingDate` datetime null,
  `deliveryTerms` varchar(255) null,
  `suppliesNote` text null,
  `customerPurchaseOrder` varchar(255) null,
  `modeOfPayment` varchar(255) null,
  `qualityCheck` varchar(255) null,
  `packing` varchar(255) null,
  `remarks` text null,
  `memberIds` text null,
  `version` bigint unsigned not null default '0',
  `created_at` timestamp null,
  `updated_at` timestamp null,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `packinglists`
--

CREATE TABLE IF NOT EXISTS `packinglists` (
  `id` bigint unsigned not null auto_increment,
  `contract_id` bigint unsigned not null,
  `vesselName` varchar(255) null,
  `shippingLine` varchar(255) null,
  `blNumber` varchar(64) null,
  `portOfLoading` varchar(255) null,
  `portOfDischarge` varchar(255) null,
  `status` varchar(64) not null default 'ACTIVE',
  `version` bigint unsigned not null default '0',
  `created_at` timestamp null,
  `updated_at` timestamp null,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `packs`
--

CREATE TABLE IF NOT EXISTS `packs` (
  `id` bigint unsigned not null auto_increment,
  `packing_list_id` bigint unsigned not null,
  `pack_type_id` bigint unsigned null,
  `containerNumber` varchar(64) null,
  `sealNumber` varchar(64) null,
  `grossWeight` double not null default '0',
  `netWeight` double not null default '0',
  `tareWeight` double not null default '0',
  `quantity` int not null default '1',
  `version` bigint unsigned not null default '0',
  `created_at` timestamp null,
  `updated_at` timestamp null,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `templates`
--

CREATE TABLE IF NOT EXISTS `templates` (
  `id` bigint unsigned not null auto_increment,
  `name` varchar(255) not null,
  `type` varchar(64) not null,
  `language_id` bigint unsigned null,
  `html` longtext not null,
  `keys` text null,
  `version` bigint unsigned not null default '0',
  `created_at` timestamp null,
  `updated_at` timestamp null,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `generatedfiles`
--

CREATE TABLE IF NOT EXISTS `generatedfiles` (
  `id` bigint unsigned not null auto_increment,
  `contract_id` bigint unsigned not null,
  `template_id` bigint unsigned null,
  `fileName` varchar(255) not null,
  `html` longtext not null,
  `fileType` varchar(64) not null default 'HTML',
  `user_id` bigint unsigned null,
  `version` bigint unsigned not null default '0',
  `created_at` timestamp null,
  `updated_at` timestamp null,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `uploadedfiles`
--

CREATE TABLE IF NOT EXISTS `uploadedfiles` (
  `id` bigint unsigned not null auto_increment,
  `contract_id` bigint unsigned not null,
  `fileName` varchar(255) not null,
  `filePath` varchar(500) not null,
  `fileSize` bigint unsigned not null default '0',
  `contentType` varchar(100) null,
  `created_at` timestamp null,
  `updated_at` timestamp null,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `archive`
--

CREATE TABLE IF NOT EXISTS `archive` (
  `id` bigint unsigned not null auto_increment,
  `file` longtext not null,
  `fileName` varchar(255) not null,
  `created_at` timestamp null,
  `updated_at` timestamp null,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `checklists`
--

CREATE TABLE IF NOT EXISTS `checklists` (
  `id` bigint unsigned not null auto_increment,
  `contract_id` bigint unsigned not null,
  `name` varchar(255) not null,
  `version` bigint unsigned not null default '0',
  `created_at` timestamp null,
  `updated_at` timestamp null,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `checkitems`
--

CREATE TABLE IF NOT EXISTS `checkitems` (
  `id` bigint unsigned not null auto_increment,
  `checklist_id` bigint unsigned not null,
  `name` varchar(255) not null,
  `checked` tinyint(1) not null default '0',
  `version` bigint unsigned not null default '0',
  `created_at` timestamp null,
  `updated_at` timestamp null,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `comments`
--

CREATE TABLE IF NOT EXISTS `comments` (
  `id` bigint unsigned not null auto_increment,
  `contract_id` bigint unsigned not null,
  `user_id` bigint unsigned null,
  `message` text not null,
  `created_at` timestamp null,
  `updated_at` timestamp null,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `offer`
--

CREATE TABLE IF NOT EXISTS `offer` (
  `id` bigint unsigned not null auto_increment,
  `orderCode` varchar(100) null,
  `type` varchar(64) not null,
  `meta_company_id` bigint unsigned null,
  `contact_id` bigint unsigned null,
  `product_id` bigint unsigned null,
  `quantity` double not null default '0',
  `unitPrice` double not null default '0',
  `total` double not null default '0',
  `currency_id` bigint unsigned null,
  `status` varchar(64) not null default 'OPEN',
  `validUntil` datetime null,
  `version` bigint unsigned not null default '0',
  `created_at` timestamp null,
  `updated_at` timestamp null,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `detachednote`
--

CREATE TABLE IF NOT EXISTS `detachednote` (
  `id` bigint unsigned not null auto_increment,
  `kind` varchar(64) not null,
  `number` varchar(64) null,
  `date` datetime null,
  `meta_company_id` bigint unsigned null,
  `contact_id` bigint unsigned null,
  `currency_id` bigint unsigned null,
  `total` double not null default '0',
  `version` bigint unsigned not null default '0',
  `created_at` timestamp null,
  `updated_at` timestamp null,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `detachednotedetail`
--

CREATE TABLE IF NOT EXISTS `detachednotedetail` (
  `id` bigint unsigned not null auto_increment,
  `detached_note_id` bigint unsigned not null,
  `description` varchar(255) not null,
  `quantity` double not null default '0',
  `unitPrice` double not null default '0',
  `total` double not null default '0',
  `version` bigint unsigned not null default '0',
  `created_at` timestamp null,
  `updated_at` timestamp null,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `logs`
--

CREATE TABLE IF NOT EXISTS `logs` (
  `id` bigint unsigned not null auto_increment,
  `module` varchar(64) not null,
  `dataId` bigint unsigned not null,
  `dateTime` datetime not null,
  `userId` bigint unsigned null,
  `action` varchar(64) not null,
  `history` longtext null,
  `created_at` timestamp null,
  `updated_at` timestamp null,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Indexes for dumped tables
--

alter table `failed_jobs` add unique `failed_jobs_uuid_unique`(`uuid`);
alter table `personal_access_tokens` add index `personal_access_tokens_tokenable_type_tokenable_id_index`(`tokenable_type`, `tokenable_id`);
alter table `personal_access_tokens` add unique `personal_access_tokens_token_unique`(`token`);
alter table `users` add unique `users_username_unique`(`username`);
alter table `contractOrderCodeSequence` add unique `contractordercodesequence_year_unique`(`year`);
alter table `contracts` add unique `contracts_ordercode_unique`(`orderCode`);

-- ========================================================
-- Dumping data for tables
-- ========================================================

-- Dumping data for table `migrations`
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_100000_create_password_reset_tokens_table', 1),
(2, '2019_08_19_000000_create_failed_jobs_table', 1),
(3, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(4, '2024_01_01_000001_create_metaerp_core_lookups_tables', 1),
(5, '2024_01_01_000002_create_metaerp_auth_and_access_tables', 1),
(6, '2024_01_01_000003_create_metaerp_companies_and_contacts_tables', 1),
(7, '2024_01_01_000004_create_metaerp_products_tables', 1),
(8, '2024_01_01_000005_create_metaerp_models_and_stages_tables', 1),
(9, '2024_01_01_000006_create_metaerp_deals_and_contracts_tables', 1),
(10, '2024_01_01_000007_create_metaerp_logistics_and_packing_tables', 1),
(11, '2024_01_01_000008_create_metaerp_documents_and_collaboration_tables', 1),
(12, '2024_01_01_000009_create_metaerp_offers_notes_and_logs_tables', 1);

-- Dumping data for table `access` (36 rows)
INSERT INTO `access` (`id`, `accessgroup_id`, `module`, `accessType`, `active`, `version`, `created_at`, `updated_at`) VALUES
(1, 1, 'DASHBOARD', 'CREATE', 1, 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(2, 1, 'DASHBOARD', 'READ', 1, 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(3, 1, 'DASHBOARD', 'UPDATE', 1, 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(4, 1, 'DASHBOARD', 'DELETE', 1, 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(5, 1, 'USERS', 'CREATE', 1, 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(6, 1, 'USERS', 'READ', 1, 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(7, 1, 'USERS', 'UPDATE', 1, 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(8, 1, 'USERS', 'DELETE', 1, 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(9, 1, 'PRODUCT', 'CREATE', 1, 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(10, 1, 'PRODUCT', 'READ', 1, 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(11, 1, 'PRODUCT', 'UPDATE', 1, 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(12, 1, 'PRODUCT', 'DELETE', 1, 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(13, 1, 'COMPANY', 'CREATE', 1, 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(14, 1, 'COMPANY', 'READ', 1, 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(15, 1, 'COMPANY', 'UPDATE', 1, 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(16, 1, 'COMPANY', 'DELETE', 1, 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(17, 1, 'CONTACT', 'CREATE', 1, 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(18, 1, 'CONTACT', 'READ', 1, 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(19, 1, 'CONTACT', 'UPDATE', 1, 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(20, 1, 'CONTACT', 'DELETE', 1, 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(21, 1, 'OFFER', 'CREATE', 1, 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(22, 1, 'OFFER', 'READ', 1, 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(23, 1, 'OFFER', 'UPDATE', 1, 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(24, 1, 'OFFER', 'DELETE', 1, 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(25, 1, 'CONTRACT', 'CREATE', 1, 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(26, 1, 'CONTRACT', 'READ', 1, 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(27, 1, 'CONTRACT', 'UPDATE', 1, 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(28, 1, 'CONTRACT', 'DELETE', 1, 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(29, 1, 'ACCESS_CONTROL', 'CREATE', 1, 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(30, 1, 'ACCESS_CONTROL', 'READ', 1, 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(31, 1, 'ACCESS_CONTROL', 'UPDATE', 1, 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(32, 1, 'ACCESS_CONTROL', 'DELETE', 1, 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(33, 1, 'SYSTEM_SETTINGS', 'CREATE', 1, 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(34, 1, 'SYSTEM_SETTINGS', 'READ', 1, 0, '2026-10-29 15:21:13', '2026-10-29 15:21:13'),
(35, 1, 'SYSTEM_SETTINGS', 'UPDATE', 1, 0, '2026-10-29 15:21:13', '2026-10-29 15:21:13'),
(36, 1, 'SYSTEM_SETTINGS', 'DELETE', 1, 0, '2026-10-29 15:21:13', '2026-10-29 15:21:13');

-- Dumping data for table `accessgroups` (1 rows)
INSERT INTO `accessgroups` (`id`, `name`, `active`, `version`, `created_at`, `updated_at`) VALUES
(1, 'Super Administrator', 1, 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12');

-- Dumping data for table `addresses` (2 rows)
INSERT INTO `addresses` (`id`, `addressName`, `addressLine1`, `addressLine2`, `city`, `state`, `postalCode`, `postalBox`, `active`, `forGoogleMapsSearch`, `country_id`, `company_id`, `contact_id`, `version`, `created_at`, `updated_at`) VALUES
(1, 'Headquarters', '25 Bank Street', 'Canary Wharf', 'London', 'Greater London', 'E14 5JP', NULL, 1, NULL, 1, NULL, NULL, 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(2, 'Usina Presidente Vargas', 'Rodovia Lúcio Meira, s/n', NULL, 'Volta Redonda', 'Rio de Janeiro', '27260-390', NULL, 1, NULL, 2, NULL, 1, 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12');

-- Dumping data for table `bankaccounts` (2 rows)
INSERT INTO `bankaccounts` (`id`, `company_id`, `contact_id`, `name`, `sortCode`, `accountNumber`, `iban`, `swift`, `version`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 'Barclays Bank UK', '20-00-00', '98765432', 'GB29BARC20000098765432', 'BARCGB22', 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(2, NULL, 1, 'Banco Itaú BBA', '0341', '1029384', 'BR54034100000010293840001', 'ITAUBRSP', 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12');

-- Dumping data for table `checkitems` (4 rows)
INSERT INTO `checkitems` (`id`, `checklist_id`, `name`, `checked`, `version`, `created_at`, `updated_at`) VALUES
(1, 1, 'Sanctions & AML Screening', 1, 0, '2026-10-29 15:21:13', '2026-10-29 15:21:13'),
(2, 1, 'Credit Insurance Approval', 1, 0, '2026-10-29 15:21:13', '2026-10-29 15:21:13'),
(3, 1, 'Proforma Invoice Signed by Supplier', 0, 0, '2026-10-29 15:21:13', '2026-10-29 15:21:13'),
(4, 1, 'Bill of Lading Verified', 1, 0, '2026-10-29 15:36:09', '2026-10-29 15:43:59');

-- Dumping data for table `checklists` (1 rows)
INSERT INTO `checklists` (`id`, `contract_id`, `name`, `version`, `created_at`, `updated_at`) VALUES
(1, 1, 'KYC & Trade Compliance', 0, '2026-10-29 15:21:13', '2026-10-29 15:21:13');

-- Dumping data for table `comments` (6 rows)
INSERT INTO `comments` (`id`, `contract_id`, `user_id`, `message`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Contract created successfully. Verified vessel schedule with MSC line.', '2026-10-29 15:21:13', '2026-10-29 15:21:13'),
(2, 1, 1, 'Shipment inspection scheduled for next Monday.', '2026-10-29 15:36:09', '2026-10-29 15:36:09'),
(3, 1, 1, 'Shipment inspection scheduled for next Monday.', '2026-10-29 15:37:14', '2026-10-29 15:37:14'),
(4, 1, 1, 'Shipment inspection scheduled for next Monday.', '2026-10-29 15:37:55', '2026-10-29 15:37:55'),
(5, 1, 1, 'Shipment inspection scheduled for next Monday.', '2026-10-29 15:42:43', '2026-10-29 15:42:43'),
(6, 1, 1, 'Shipment inspection scheduled for next Monday.', '2026-10-29 15:43:59', '2026-10-29 15:43:59');

-- Dumping data for table `companies` (2 rows)
INSERT INTO `companies` (`id`, `name`, `code`, `countryId`, `logoUrl`, `companyNumber`, `vatNumber`, `phoneNumber`, `faxNumber`, `email`, `webSite`, `footer`, `eoriNumber`, `address_id`, `archiveLogoId`, `archiveFooterId`, `archiveStampId`, `active`, `version`, `created_at`, `updated_at`) VALUES
(1, 'Metaenterprices Ltd', 'ME', 1, NULL, '07890123', 'GB987654321', '+44 20 7946 0199', NULL, 'operations@metaenterprices.co.uk', 'https://metaenterprices.co.uk', NULL, 'GB987654321000', 1, NULL, NULL, NULL, 1, 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(2, 'Menon Trading LLC', 'MENON', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, '2026-10-29 15:34:17', '2026-10-29 15:34:17');

-- Dumping data for table `contact_products` (2 rows)
INSERT INTO `contact_products` (`id`, `contact_id`, `product_id`) VALUES
(1, 1, 1),
(2, 1, 3);

-- Dumping data for table `contact_types` (2 rows)
INSERT INTO `contact_types` (`id`, `contact_id`, `type_id`) VALUES
(1, 1, 1),
(2, 1, 2);

-- Dumping data for table `contacts` (2 rows)
INSERT INTO `contacts` (`id`, `name`, `initials`, `companyId`, `vat`, `registration`, `codeMeta`, `currency_id`, `countryId`, `language_id`, `active`, `version`, `created_at`, `updated_at`) VALUES
(1, 'CSN Siderúrgica Nacional', 'CSN', NULL, 'BR-33042730000104', 'REG-109283', 'BR00001', 1, 2, 3, 1, 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(2, 'John Seller', NULL, 2, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, '2026-10-29 15:35:19', '2026-10-29 15:35:19');

-- Dumping data for table `contractOrderCodeSequence` (3 rows)
INSERT INTO `contractOrderCodeSequence` (`id`, `year`, `lastId`, `created_at`, `updated_at`) VALUES
(1, 2024, 100, '2026-10-29 15:21:13', '2026-10-29 15:21:13'),
(2, 2025, 200, '2026-10-29 15:21:13', '2026-10-29 15:21:13'),
(3, 2026, 11, '2026-10-29 15:21:13', '2026-10-29 15:43:59');

-- Dumping data for table `contracts` (16 rows)
INSERT INTO `contracts` (`id`, `orderCode`, `date`, `type`, `stage_id`, `purchase_id`, `sale_id`, `contractStatus`, `active`, `loadingDate`, `deliveryTerms`, `suppliesNote`, `customerPurchaseOrder`, `modeOfPayment`, `qualityCheck`, `packing`, `remarks`, `memberIds`, `version`, `created_at`, `updated_at`) VALUES
(1, 'MENON_FERROUS26-001', '2026-10-29 15:21:13', 'BUY', 2, 1, NULL, 'ACTIVE', 1, '2026-11-12 15:21:13', 'CIF Rotterdam Port', 'High purity electrolytic grade cathodes with mill test certificates.', 'PO-CSN-2026-99', 'CAD through Barclays London', 'SGS Inspection at loading port', 'Strapped bundles on wooden pallets, plastic wrapped', 'Priority shipment for Q1 2026.', NULL, 0, '2026-10-29 15:21:13', '2026-10-29 15:43:59'),
(2, 'MENONFERROUS26-2', '2026-10-29 15:36:09', 'BUY', 2, 2, NULL, 'ACTIVE', 1, '2026-11-12 15:36:09', 'CIF', NULL, NULL, 'TT in Advance', NULL, NULL, NULL, NULL, 0, '2026-10-29 15:36:09', '2026-10-29 15:36:09'),
(3, 'MENONFERROUS26-3', '2026-10-29 15:36:09', 'BUY', 2, 3, NULL, 'ACTIVE', 1, '2026-11-12 15:36:09', 'CIF', NULL, NULL, 'TT in Advance', NULL, NULL, NULL, NULL, 0, '2026-10-29 15:36:09', '2026-10-29 15:36:09'),
(4, 'CANCEL-TEST-1793288169', NULL, 'SELL', NULL, NULL, NULL, 'ACTIVE', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2026-10-29 15:36:09', '2026-10-29 15:36:09'),
(5, 'MENONFERROUS26-4', '2026-10-29 15:37:14', 'BUY', 2, 4, NULL, 'ACTIVE', 1, '2026-11-12 15:37:14', 'CIF', NULL, NULL, 'TT in Advance', NULL, NULL, NULL, NULL, 0, '2026-10-29 15:37:14', '2026-10-29 15:37:14'),
(6, 'MENONFERROUS26-5', '2026-10-29 15:37:14', 'BUY', 2, 5, NULL, 'ACTIVE', 1, '2026-11-12 15:37:14', 'CIF', NULL, NULL, 'TT in Advance', NULL, NULL, NULL, NULL, 0, '2026-10-29 15:37:14', '2026-10-29 15:37:14'),
(7, 'CANCEL-TEST-1793288234', NULL, 'SELL', NULL, NULL, NULL, 'CANCELLED', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2026-10-29 15:37:14', '2026-10-29 15:37:14'),
(8, 'MENONFERROUS26-6', '2026-10-29 15:37:55', 'BUY', 2, 6, NULL, 'ACTIVE', 1, '2026-11-12 15:37:55', 'CIF', NULL, NULL, 'TT in Advance', NULL, NULL, NULL, NULL, 0, '2026-10-29 15:37:55', '2026-10-29 15:37:55'),
(9, 'MENONFERROUS26-7', '2026-10-29 15:37:55', 'BUY', 2, 7, NULL, 'ACTIVE', 1, '2026-11-12 15:37:55', 'CIF', NULL, NULL, 'TT in Advance', NULL, NULL, NULL, NULL, 0, '2026-10-29 15:37:55', '2026-10-29 15:37:55'),
(10, 'CANCEL-TEST-1793288275', NULL, 'SELL', NULL, NULL, NULL, 'CANCELLED', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2026-10-29 15:37:55', '2026-10-29 15:37:55'),
(11, 'MENONFERROUS26-8', '2026-10-29 15:42:42', 'BUY', 2, 8, NULL, 'ACTIVE', 1, '2026-11-12 15:42:42', 'CIF', NULL, NULL, 'TT in Advance', NULL, NULL, NULL, NULL, 0, '2026-10-29 15:42:42', '2026-10-29 15:42:42'),
(12, 'MENONFERROUS26-9', '2026-10-29 15:42:43', 'BUY', 2, 9, NULL, 'ACTIVE', 1, '2026-11-12 15:42:43', 'CIF', NULL, NULL, 'TT in Advance', NULL, NULL, NULL, NULL, 0, '2026-10-29 15:42:43', '2026-10-29 15:42:43'),
(13, 'CANCEL-TEST-1793288563', NULL, 'SELL', NULL, NULL, NULL, 'CANCELLED', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2026-10-29 15:42:43', '2026-10-29 15:42:43'),
(14, 'MENONFERROUS26-10', '2026-10-29 15:43:59', 'BUY', 2, 10, NULL, 'ACTIVE', 1, '2026-11-12 15:43:59', 'CIF', NULL, NULL, 'TT in Advance', NULL, NULL, NULL, NULL, 0, '2026-10-29 15:43:59', '2026-10-29 15:43:59'),
(15, 'MENONFERROUS26-11', '2026-10-29 15:43:59', 'BUY', 6, 11, NULL, 'ACTIVE', 1, '2026-11-12 15:43:59', 'CIF', NULL, NULL, 'TT in Advance', NULL, NULL, NULL, NULL, 0, '2026-10-29 15:43:59', '2026-11-03 05:24:47'),
(16, 'CANCEL-TEST-1793288639', NULL, 'SELL', NULL, NULL, NULL, 'CANCELLED', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2026-10-29 15:43:59', '2026-10-29 15:43:59');

-- Dumping data for table `countries` (4 rows)
INSERT INTO `countries` (`id`, `name`, `alpha2`, `alpha3`, `region`, `subRegion`, `intermediateRegion`, `currencyName`, `currencyCode`, `currencySymbol`, `vat`, `active`, `version`, `created_at`, `updated_at`) VALUES
(1, 'United Kingdom', 'GB', 'GBR', 'Europe', NULL, NULL, 'Pound Sterling', 'GBP', '£', 20, 1, 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(2, 'Brazil', 'BR', 'BRA', 'South America', NULL, NULL, 'Brazilian Real', 'BRL', 'R$', 10, 1, 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(3, 'United States', 'US', 'USA', 'North America', NULL, NULL, 'US Dollar', 'USD', '$', 0, 1, 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(4, 'Germany', 'DE', 'DEU', 'Europe', NULL, NULL, 'Euro', 'EUR', '€', 19, 1, 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12');

-- Dumping data for table `creditdebittype` (3 rows)
INSERT INTO `creditdebittype` (`id`, `description`, `version`, `created_at`, `updated_at`) VALUES
(1, 'Price Adjustment', 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(2, 'Quality Discrepancy', 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(3, 'Weight Difference', 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12');

-- Dumping data for table `currency` (3 rows)
INSERT INTO `currency` (`id`, `description`, `symbol`, `rate`, `version`, `created_at`, `updated_at`) VALUES
(1, 'USD', '$', 1.25, 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(2, 'GBP', '£', 1, 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(3, 'EUR', '€', 1.15, 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12');

-- Dumping data for table `deal` (11 rows)
INSERT INTO `deal` (`id`, `meta_company_id`, `contact_id`, `contact_representative_id`, `meta_representative_id`, `address_id`, `destination_address_id`, `currency_id`, `payment_type_id`, `payment_terms_type_id`, `bank_account_id`, `dollarConvertionRate`, `total`, `netTotal`, `dealPaymentStatus`, `version`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 2, 1, 2, 1, 1, 1, 1, 1, 1, 450000, 450000, 'PENDING', 0, '2026-10-29 15:21:13', '2026-10-29 15:21:13'),
(2, 2, 2, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, 1, 368725, 368725, 'PENDING', 0, '2026-10-29 15:36:09', '2026-10-29 15:36:09'),
(3, 2, 2, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, 1, 60000, 60000, 'PENDING', 0, '2026-10-29 15:36:09', '2026-10-29 15:36:09'),
(4, 2, 2, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, 1, 368725, 368725, 'PENDING', 0, '2026-10-29 15:37:14', '2026-10-29 15:37:14'),
(5, 2, 2, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, 1, 60000, 60000, 'PENDING', 0, '2026-10-29 15:37:14', '2026-10-29 15:37:14'),
(6, 2, 2, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, 1, 368725, 368725, 'PENDING', 0, '2026-10-29 15:37:55', '2026-10-29 15:37:55'),
(7, 2, 2, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, 1, 60000, 60000, 'PENDING', 0, '2026-10-29 15:37:55', '2026-10-29 15:37:55'),
(8, 2, 2, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, 1, 368725, 368725, 'PENDING', 0, '2026-10-29 15:42:42', '2026-10-29 15:42:42'),
(9, 2, 2, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, 1, 60000, 60000, 'PENDING', 0, '2026-10-29 15:42:43', '2026-10-29 15:42:43'),
(10, 2, 2, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, 1, 368725, 368725, 'PENDING', 0, '2026-10-29 15:43:59', '2026-10-29 15:43:59'),
(11, 2, 2, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, 1, 60000, 60000, 'PENDING', 0, '2026-10-29 15:43:59', '2026-10-29 15:43:59');

-- Dumping data for table `dealproduct` (11 rows)
INSERT INTO `dealproduct` (`id`, `deal_id`, `product_id`, `pack_type_id`, `currency_id`, `quantity`, `unitPrice`, `pricingType`, `vat`, `total`, `associatedContractId`, `associatedProductId`, `status`, `version`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 3, 1, 50, 9000, 'FIXED', 0, 450000, NULL, NULL, 'PENDING', 0, '2026-10-29 15:21:13', '2026-10-29 15:21:13'),
(2, 2, 4, NULL, 1, 150.5, 2450, NULL, 0, 368725, NULL, NULL, 'PENDING', 0, '2026-10-29 15:36:09', '2026-10-29 15:36:09'),
(3, 3, 4, NULL, 1, 50, 1200, NULL, 0, 60000, NULL, NULL, 'PENDING', 0, '2026-10-29 15:36:09', '2026-10-29 15:36:09'),
(4, 4, 4, NULL, 1, 150.5, 2450, NULL, 0, 368725, NULL, NULL, 'PENDING', 0, '2026-10-29 15:37:14', '2026-10-29 15:37:14'),
(5, 5, 4, NULL, 1, 50, 1200, NULL, 0, 60000, NULL, NULL, 'PENDING', 0, '2026-10-29 15:37:14', '2026-10-29 15:37:14'),
(6, 6, 4, NULL, 1, 150.5, 2450, NULL, 0, 368725, NULL, NULL, 'PENDING', 0, '2026-10-29 15:37:55', '2026-10-29 15:37:55'),
(7, 7, 4, NULL, 1, 50, 1200, NULL, 0, 60000, NULL, NULL, 'PENDING', 0, '2026-10-29 15:37:55', '2026-10-29 15:37:55'),
(8, 8, 4, NULL, 1, 150.5, 2450, NULL, 0, 368725, NULL, NULL, 'PENDING', 0, '2026-10-29 15:42:42', '2026-10-29 15:42:42'),
(9, 9, 4, NULL, 1, 50, 1200, NULL, 0, 60000, NULL, NULL, 'PENDING', 0, '2026-10-29 15:42:43', '2026-10-29 15:42:43'),
(10, 10, 4, NULL, 1, 150.5, 2450, NULL, 0, 368725, NULL, NULL, 'PENDING', 0, '2026-10-29 15:43:59', '2026-10-29 15:43:59'),
(11, 11, 4, NULL, 1, 50, 1200, NULL, 0, 60000, NULL, NULL, 'PENDING', 0, '2026-10-29 15:43:59', '2026-10-29 15:43:59');

-- Dumping data for table `department` (4 rows)
INSERT INTO `department` (`id`, `name`, `version`, `created_at`, `updated_at`) VALUES
(1, 'Trading & Procurement', 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(2, 'Logistics & Shipping', 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(3, 'Finance & Accounting', 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(4, 'Quality Control', 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12');

-- Dumping data for table `generatedfiles` (6 rows)
INSERT INTO `generatedfiles` (`id`, `contract_id`, `template_id`, `fileName`, `html`, `fileType`, `user_id`, `version`, `created_at`, `updated_at`) VALUES
(1, 1, 5, 'CONTRACT_MENON_FERROUS26-001_20261029153906.html', '<html><body><h1>Contract MENON_FERROUS26-001</h1><p>Supplier: Metaenterprices Ltd</p><p>Total: 450,000.00 USD</p></body></html>', 'HTML', 1, 0, '2026-10-29 15:39:06', '2026-10-29 15:39:06'),
(2, 1, 5, 'CONTRACT_MENON_FERROUS26-001_20261029153906.html', '<html><body><h1>Contract MENON_FERROUS26-001</h1><p>Supplier: Metaenterprices Ltd</p><p>Total: 450,000.00 USD</p></body></html>', 'HTML', 1, 0, '2026-10-29 15:39:06', '2026-10-29 15:39:06'),
(3, 1, 5, 'CONTRACT_MENON_FERROUS26-001_20261029154243.html', '<html><body><h1>Contract MENON_FERROUS26-001</h1><p>Supplier: Metaenterprices Ltd</p><p>Total: 450,000.00 USD</p></body></html>', 'HTML', 1, 0, '2026-10-29 15:42:43', '2026-10-29 15:42:43'),
(4, 1, 5, 'CONTRACT_MENON_FERROUS26-001_20261029154243.html', '<html><body><h1>Contract MENON_FERROUS26-001</h1><p>Supplier: Metaenterprices Ltd</p><p>Total: 450,000.00 USD</p></body></html>', 'HTML', 1, 0, '2026-10-29 15:42:43', '2026-10-29 15:42:43'),
(5, 1, 5, 'CONTRACT_MENON_FERROUS26-001_20261029154359.html', '<html><body><h1>Contract MENON_FERROUS26-001</h1><p>Supplier: Metaenterprices Ltd</p><p>Total: 450,000.00 USD</p></body></html>', 'HTML', 1, 0, '2026-10-29 15:43:59', '2026-10-29 15:43:59'),
(6, 1, 5, 'CONTRACT_MENON_FERROUS26-001_20261029154359.html', '<html><body><h1>Contract MENON_FERROUS26-001</h1><p>Supplier: Metaenterprices Ltd</p><p>Total: 450,000.00 USD</p></body></html>', 'HTML', 1, 0, '2026-10-29 15:43:59', '2026-10-29 15:43:59');

-- Dumping data for table `languages` (3 rows)
INSERT INTO `languages` (`id`, `name`, `code`, `version`, `created_at`, `updated_at`) VALUES
(1, 'English', 'en', 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(2, 'Spanish', 'es', 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(3, 'Portuguese', 'pt', 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12');

-- Dumping data for table `logs` (20 rows)
INSERT INTO `logs` (`id`, `module`, `dataId`, `dateTime`, `userId`, `action`, `history`, `created_at`, `updated_at`) VALUES
(1, 'CONTRACT', 2, '2026-10-29 15:36:09', 1, 'CREATE', '{"date":"2026-10-29T15:36:09.000000Z","type":"BUY","stage_id":2,"purchase_id":2,"sale_id":null,"contractStatus":"ACTIVE","active":true,"loadingDate":"2026-11-12T15:36:09.000000Z","deliveryTerms":"CIF","modeOfPayment":"TT in Advance","updated_at":"2026-10-29T15:36:09.000000Z","created_at":"2026-10-29T15:36:09.000000Z","id":2,"orderCode":"MENONFERROUS26-2","purchase":{"id":2,"meta_company_id":2,"contact_id":2,"contact_representative_id":null,"meta_representative_id":null,"address_id":null,"destination_address_id":null,"currency_id":1,"payment_type_id":null,"payment_terms_type_id":null,"bank_account_id":null,"dollarConvertionRate":1,"total":368725,"netTotal":368725,"dealPaymentStatus":"PENDING","version":0,"created_at":"2026-10-29T15:36:09.000000Z","updated_at":"2026-10-29T15:36:09.000000Z","meta_company":{"id":2,"name":"Menon Trading LLC","code":"MENON","countryId":null,"logoUrl":null,"companyNumber":null,"vatNumber":null,"phoneNumber":null,"faxNumber":null,"email":null,"webSite":null,"footer":null,"eoriNumber":null,"address_id":null,"archiveLogoId":null,"archiveFooterId":null,"archiveStampId":null,"active":true,"version":0,"created_at":"2026-10-29T15:34:17.000000Z","updated_at":"2026-10-29T15:34:17.000000Z"},"products":[{"id":2,"deal_id":2,"product_id":4,"pack_type_id":null,"currency_id":1,"quantity":150.5,"unitPrice":2450,"pricingType":null,"vat":0,"total":368725,"associatedContractId":null,"associatedProductId":null,"status":"PENDING","version":0,"created_at":"2026-10-29T15:36:09.000000Z","updated_at":"2026-10-29T15:36:09.000000Z","product":{"id":4,"name":"Ferrous Scrap Metal","description":null,"code":"FERROUS","productCategory":"FERROUS","chemicalFormula":null,"unitOfMeasurement":null,"hsCode":null,"active":true,"version":0,"created_at":"2026-10-29T15:35:36.000000Z","updated_at":"2026-10-29T15:35:36.000000Z"}}]}}', '2026-10-29 15:36:09', '2026-10-29 15:36:09'),
(2, 'CONTRACT', 3, '2026-10-29 15:36:09', 1, 'CREATE', '{"date":"2026-10-29T15:36:09.000000Z","type":"BUY","stage_id":2,"purchase_id":3,"sale_id":null,"contractStatus":"ACTIVE","active":true,"loadingDate":"2026-11-12T15:36:09.000000Z","deliveryTerms":"CIF","modeOfPayment":"TT in Advance","updated_at":"2026-10-29T15:36:09.000000Z","created_at":"2026-10-29T15:36:09.000000Z","id":3,"orderCode":"MENONFERROUS26-3","purchase":{"id":3,"meta_company_id":2,"contact_id":2,"contact_representative_id":null,"meta_representative_id":null,"address_id":null,"destination_address_id":null,"currency_id":1,"payment_type_id":null,"payment_terms_type_id":null,"bank_account_id":null,"dollarConvertionRate":1,"total":60000,"netTotal":60000,"dealPaymentStatus":"PENDING","version":0,"created_at":"2026-10-29T15:36:09.000000Z","updated_at":"2026-10-29T15:36:09.000000Z","meta_company":{"id":2,"name":"Menon Trading LLC","code":"MENON","countryId":null,"logoUrl":null,"companyNumber":null,"vatNumber":null,"phoneNumber":null,"faxNumber":null,"email":null,"webSite":null,"footer":null,"eoriNumber":null,"address_id":null,"archiveLogoId":null,"archiveFooterId":null,"archiveStampId":null,"active":true,"version":0,"created_at":"2026-10-29T15:34:17.000000Z","updated_at":"2026-10-29T15:34:17.000000Z"},"products":[{"id":3,"deal_id":3,"product_id":4,"pack_type_id":null,"currency_id":1,"quantity":50,"unitPrice":1200,"pricingType":null,"vat":0,"total":60000,"associatedContractId":null,"associatedProductId":null,"status":"PENDING","version":0,"created_at":"2026-10-29T15:36:09.000000Z","updated_at":"2026-10-29T15:36:09.000000Z","product":{"id":4,"name":"Ferrous Scrap Metal","description":null,"code":"FERROUS","productCategory":"FERROUS","chemicalFormula":null,"unitOfMeasurement":null,"hsCode":null,"active":true,"version":0,"created_at":"2026-10-29T15:35:36.000000Z","updated_at":"2026-10-29T15:35:36.000000Z"}}]}}', '2026-10-29 15:36:09', '2026-10-29 15:36:09'),
(3, 'CONTRACT', 5, '2026-10-29 15:37:14', 1, 'CREATE', '{"date":"2026-10-29T15:37:14.000000Z","type":"BUY","stage_id":2,"purchase_id":4,"sale_id":null,"contractStatus":"ACTIVE","active":true,"loadingDate":"2026-11-12T15:37:14.000000Z","deliveryTerms":"CIF","modeOfPayment":"TT in Advance","updated_at":"2026-10-29T15:37:14.000000Z","created_at":"2026-10-29T15:37:14.000000Z","id":5,"orderCode":"MENONFERROUS26-4","purchase":{"id":4,"meta_company_id":2,"contact_id":2,"contact_representative_id":null,"meta_representative_id":null,"address_id":null,"destination_address_id":null,"currency_id":1,"payment_type_id":null,"payment_terms_type_id":null,"bank_account_id":null,"dollarConvertionRate":1,"total":368725,"netTotal":368725,"dealPaymentStatus":"PENDING","version":0,"created_at":"2026-10-29T15:37:14.000000Z","updated_at":"2026-10-29T15:37:14.000000Z","meta_company":{"id":2,"name":"Menon Trading LLC","code":"MENON","countryId":null,"logoUrl":null,"companyNumber":null,"vatNumber":null,"phoneNumber":null,"faxNumber":null,"email":null,"webSite":null,"footer":null,"eoriNumber":null,"address_id":null,"archiveLogoId":null,"archiveFooterId":null,"archiveStampId":null,"active":true,"version":0,"created_at":"2026-10-29T15:34:17.000000Z","updated_at":"2026-10-29T15:34:17.000000Z"},"products":[{"id":4,"deal_id":4,"product_id":4,"pack_type_id":null,"currency_id":1,"quantity":150.5,"unitPrice":2450,"pricingType":null,"vat":0,"total":368725,"associatedContractId":null,"associatedProductId":null,"status":"PENDING","version":0,"created_at":"2026-10-29T15:37:14.000000Z","updated_at":"2026-10-29T15:37:14.000000Z","product":{"id":4,"name":"Ferrous Scrap Metal","description":null,"code":"FERROUS","productCategory":"FERROUS","chemicalFormula":null,"unitOfMeasurement":null,"hsCode":null,"active":true,"version":0,"created_at":"2026-10-29T15:35:36.000000Z","updated_at":"2026-10-29T15:35:36.000000Z"}}]}}', '2026-10-29 15:37:14', '2026-10-29 15:37:14'),
(4, 'CONTRACT', 1, '2026-10-29 15:37:14', 1, 'STAGE_TRANSITION', '{"stage":{"from":"Under Review","to":"Draft"}}', '2026-10-29 15:37:14', '2026-10-29 15:37:14'),
(5, 'CONTRACT', 6, '2026-10-29 15:37:14', 1, 'CREATE', '{"date":"2026-10-29T15:37:14.000000Z","type":"BUY","stage_id":2,"purchase_id":5,"sale_id":null,"contractStatus":"ACTIVE","active":true,"loadingDate":"2026-11-12T15:37:14.000000Z","deliveryTerms":"CIF","modeOfPayment":"TT in Advance","updated_at":"2026-10-29T15:37:14.000000Z","created_at":"2026-10-29T15:37:14.000000Z","id":6,"orderCode":"MENONFERROUS26-5","purchase":{"id":5,"meta_company_id":2,"contact_id":2,"contact_representative_id":null,"meta_representative_id":null,"address_id":null,"destination_address_id":null,"currency_id":1,"payment_type_id":null,"payment_terms_type_id":null,"bank_account_id":null,"dollarConvertionRate":1,"total":60000,"netTotal":60000,"dealPaymentStatus":"PENDING","version":0,"created_at":"2026-10-29T15:37:14.000000Z","updated_at":"2026-10-29T15:37:14.000000Z","meta_company":{"id":2,"name":"Menon Trading LLC","code":"MENON","countryId":null,"logoUrl":null,"companyNumber":null,"vatNumber":null,"phoneNumber":null,"faxNumber":null,"email":null,"webSite":null,"footer":null,"eoriNumber":null,"address_id":null,"archiveLogoId":null,"archiveFooterId":null,"archiveStampId":null,"active":true,"version":0,"created_at":"2026-10-29T15:34:17.000000Z","updated_at":"2026-10-29T15:34:17.000000Z"},"products":[{"id":5,"deal_id":5,"product_id":4,"pack_type_id":null,"currency_id":1,"quantity":50,"unitPrice":1200,"pricingType":null,"vat":0,"total":60000,"associatedContractId":null,"associatedProductId":null,"status":"PENDING","version":0,"created_at":"2026-10-29T15:37:14.000000Z","updated_at":"2026-10-29T15:37:14.000000Z","product":{"id":4,"name":"Ferrous Scrap Metal","description":null,"code":"FERROUS","productCategory":"FERROUS","chemicalFormula":null,"unitOfMeasurement":null,"hsCode":null,"active":true,"version":0,"created_at":"2026-10-29T15:35:36.000000Z","updated_at":"2026-10-29T15:35:36.000000Z"}}]}}', '2026-10-29 15:37:14', '2026-10-29 15:37:14'),
(6, 'CONTRACT', 7, '2026-10-29 15:37:14', 1, 'CANCEL', NULL, '2026-10-29 15:37:14', '2026-10-29 15:37:14'),
(7, 'CONTRACT', 8, '2026-10-29 15:37:55', 1, 'CREATE', '{"date":"2026-10-29T15:37:55.000000Z","type":"BUY","stage_id":2,"purchase_id":6,"sale_id":null,"contractStatus":"ACTIVE","active":true,"loadingDate":"2026-11-12T15:37:55.000000Z","deliveryTerms":"CIF","modeOfPayment":"TT in Advance","updated_at":"2026-10-29T15:37:55.000000Z","created_at":"2026-10-29T15:37:55.000000Z","id":8,"orderCode":"MENONFERROUS26-6","purchase":{"id":6,"meta_company_id":2,"contact_id":2,"contact_representative_id":null,"meta_representative_id":null,"address_id":null,"destination_address_id":null,"currency_id":1,"payment_type_id":null,"payment_terms_type_id":null,"bank_account_id":null,"dollarConvertionRate":1,"total":368725,"netTotal":368725,"dealPaymentStatus":"PENDING","version":0,"created_at":"2026-10-29T15:37:55.000000Z","updated_at":"2026-10-29T15:37:55.000000Z","meta_company":{"id":2,"name":"Menon Trading LLC","code":"MENON","countryId":null,"logoUrl":null,"companyNumber":null,"vatNumber":null,"phoneNumber":null,"faxNumber":null,"email":null,"webSite":null,"footer":null,"eoriNumber":null,"address_id":null,"archiveLogoId":null,"archiveFooterId":null,"archiveStampId":null,"active":true,"version":0,"created_at":"2026-10-29T15:34:17.000000Z","updated_at":"2026-10-29T15:34:17.000000Z"},"products":[{"id":6,"deal_id":6,"product_id":4,"pack_type_id":null,"currency_id":1,"quantity":150.5,"unitPrice":2450,"pricingType":null,"vat":0,"total":368725,"associatedContractId":null,"associatedProductId":null,"status":"PENDING","version":0,"created_at":"2026-10-29T15:37:55.000000Z","updated_at":"2026-10-29T15:37:55.000000Z","product":{"id":4,"name":"Ferrous Scrap Metal","description":null,"code":"FERROUS","productCategory":"FERROUS","chemicalFormula":null,"unitOfMeasurement":null,"hsCode":null,"active":true,"version":0,"created_at":"2026-10-29T15:35:36.000000Z","updated_at":"2026-10-29T15:35:36.000000Z"}}]}}', '2026-10-29 15:37:55', '2026-10-29 15:37:55'),
(8, 'CONTRACT', 1, '2026-10-29 15:37:55', 1, 'STAGE_TRANSITION', '{"stage":{"from":"Draft","to":"Draft"}}', '2026-10-29 15:37:55', '2026-10-29 15:37:55'),
(9, 'CONTRACT', 9, '2026-10-29 15:37:55', 1, 'CREATE', '{"date":"2026-10-29T15:37:55.000000Z","type":"BUY","stage_id":2,"purchase_id":7,"sale_id":null,"contractStatus":"ACTIVE","active":true,"loadingDate":"2026-11-12T15:37:55.000000Z","deliveryTerms":"CIF","modeOfPayment":"TT in Advance","updated_at":"2026-10-29T15:37:55.000000Z","created_at":"2026-10-29T15:37:55.000000Z","id":9,"orderCode":"MENONFERROUS26-7","purchase":{"id":7,"meta_company_id":2,"contact_id":2,"contact_representative_id":null,"meta_representative_id":null,"address_id":null,"destination_address_id":null,"currency_id":1,"payment_type_id":null,"payment_terms_type_id":null,"bank_account_id":null,"dollarConvertionRate":1,"total":60000,"netTotal":60000,"dealPaymentStatus":"PENDING","version":0,"created_at":"2026-10-29T15:37:55.000000Z","updated_at":"2026-10-29T15:37:55.000000Z","meta_company":{"id":2,"name":"Menon Trading LLC","code":"MENON","countryId":null,"logoUrl":null,"companyNumber":null,"vatNumber":null,"phoneNumber":null,"faxNumber":null,"email":null,"webSite":null,"footer":null,"eoriNumber":null,"address_id":null,"archiveLogoId":null,"archiveFooterId":null,"archiveStampId":null,"active":true,"version":0,"created_at":"2026-10-29T15:34:17.000000Z","updated_at":"2026-10-29T15:34:17.000000Z"},"products":[{"id":7,"deal_id":7,"product_id":4,"pack_type_id":null,"currency_id":1,"quantity":50,"unitPrice":1200,"pricingType":null,"vat":0,"total":60000,"associatedContractId":null,"associatedProductId":null,"status":"PENDING","version":0,"created_at":"2026-10-29T15:37:55.000000Z","updated_at":"2026-10-29T15:37:55.000000Z","product":{"id":4,"name":"Ferrous Scrap Metal","description":null,"code":"FERROUS","productCategory":"FERROUS","chemicalFormula":null,"unitOfMeasurement":null,"hsCode":null,"active":true,"version":0,"created_at":"2026-10-29T15:35:36.000000Z","updated_at":"2026-10-29T15:35:36.000000Z"}}]}}', '2026-10-29 15:37:55', '2026-10-29 15:37:55'),
(10, 'CONTRACT', 10, '2026-10-29 15:37:55', 1, 'CANCEL', NULL, '2026-10-29 15:37:55', '2026-10-29 15:37:55'),
(11, 'CONTRACT', 11, '2026-10-29 15:42:42', 1, 'CREATE', '{"date":"2026-10-29T15:42:42.000000Z","type":"BUY","stage_id":2,"purchase_id":8,"sale_id":null,"contractStatus":"ACTIVE","active":true,"loadingDate":"2026-11-12T15:42:42.000000Z","deliveryTerms":"CIF","modeOfPayment":"TT in Advance","updated_at":"2026-10-29T15:42:42.000000Z","created_at":"2026-10-29T15:42:42.000000Z","id":11,"orderCode":"MENONFERROUS26-8","purchase":{"id":8,"meta_company_id":2,"contact_id":2,"contact_representative_id":null,"meta_representative_id":null,"address_id":null,"destination_address_id":null,"currency_id":1,"payment_type_id":null,"payment_terms_type_id":null,"bank_account_id":null,"dollarConvertionRate":1,"total":368725,"netTotal":368725,"dealPaymentStatus":"PENDING","version":0,"created_at":"2026-10-29T15:42:42.000000Z","updated_at":"2026-10-29T15:42:42.000000Z","meta_company":{"id":2,"name":"Menon Trading LLC","code":"MENON","countryId":null,"logoUrl":null,"companyNumber":null,"vatNumber":null,"phoneNumber":null,"faxNumber":null,"email":null,"webSite":null,"footer":null,"eoriNumber":null,"address_id":null,"archiveLogoId":null,"archiveFooterId":null,"archiveStampId":null,"active":true,"version":0,"created_at":"2026-10-29T15:34:17.000000Z","updated_at":"2026-10-29T15:34:17.000000Z"},"products":[{"id":8,"deal_id":8,"product_id":4,"pack_type_id":null,"currency_id":1,"quantity":150.5,"unitPrice":2450,"pricingType":null,"vat":0,"total":368725,"associatedContractId":null,"associatedProductId":null,"status":"PENDING","version":0,"created_at":"2026-10-29T15:42:42.000000Z","updated_at":"2026-10-29T15:42:42.000000Z","product":{"id":4,"name":"Ferrous Scrap Metal","description":null,"code":"FERROUS","productCategory":"FERROUS","chemicalFormula":null,"unitOfMeasurement":null,"hsCode":null,"active":true,"version":0,"created_at":"2026-10-29T15:35:36.000000Z","updated_at":"2026-10-29T15:35:36.000000Z"}}]}}', '2026-10-29 15:42:42', '2026-10-29 15:42:42'),
(12, 'CONTRACT', 1, '2026-10-29 15:42:42', 1, 'STAGE_TRANSITION', '{"stage":{"from":"Draft","to":"Draft"}}', '2026-10-29 15:42:42', '2026-10-29 15:42:42'),
(13, 'CONTRACT', 12, '2026-10-29 15:42:43', 1, 'CREATE', '{"date":"2026-10-29T15:42:43.000000Z","type":"BUY","stage_id":2,"purchase_id":9,"sale_id":null,"contractStatus":"ACTIVE","active":true,"loadingDate":"2026-11-12T15:42:43.000000Z","deliveryTerms":"CIF","modeOfPayment":"TT in Advance","updated_at":"2026-10-29T15:42:43.000000Z","created_at":"2026-10-29T15:42:43.000000Z","id":12,"orderCode":"MENONFERROUS26-9","purchase":{"id":9,"meta_company_id":2,"contact_id":2,"contact_representative_id":null,"meta_representative_id":null,"address_id":null,"destination_address_id":null,"currency_id":1,"payment_type_id":null,"payment_terms_type_id":null,"bank_account_id":null,"dollarConvertionRate":1,"total":60000,"netTotal":60000,"dealPaymentStatus":"PENDING","version":0,"created_at":"2026-10-29T15:42:43.000000Z","updated_at":"2026-10-29T15:42:43.000000Z","meta_company":{"id":2,"name":"Menon Trading LLC","code":"MENON","countryId":null,"logoUrl":null,"companyNumber":null,"vatNumber":null,"phoneNumber":null,"faxNumber":null,"email":null,"webSite":null,"footer":null,"eoriNumber":null,"address_id":null,"archiveLogoId":null,"archiveFooterId":null,"archiveStampId":null,"active":true,"version":0,"created_at":"2026-10-29T15:34:17.000000Z","updated_at":"2026-10-29T15:34:17.000000Z"},"products":[{"id":9,"deal_id":9,"product_id":4,"pack_type_id":null,"currency_id":1,"quantity":50,"unitPrice":1200,"pricingType":null,"vat":0,"total":60000,"associatedContractId":null,"associatedProductId":null,"status":"PENDING","version":0,"created_at":"2026-10-29T15:42:43.000000Z","updated_at":"2026-10-29T15:42:43.000000Z","product":{"id":4,"name":"Ferrous Scrap Metal","description":null,"code":"FERROUS","productCategory":"FERROUS","chemicalFormula":null,"unitOfMeasurement":null,"hsCode":null,"active":true,"version":0,"created_at":"2026-10-29T15:35:36.000000Z","updated_at":"2026-10-29T15:35:36.000000Z"}}]}}', '2026-10-29 15:42:43', '2026-10-29 15:42:43'),
(14, 'CONTRACT', 13, '2026-10-29 15:42:43', 1, 'CANCEL', NULL, '2026-10-29 15:42:43', '2026-10-29 15:42:43'),
(15, 'CONTRACT', 14, '2026-10-29 15:43:59', 1, 'CREATE', '{"date":"2026-10-29T15:43:59.000000Z","type":"BUY","stage_id":2,"purchase_id":10,"sale_id":null,"contractStatus":"ACTIVE","active":true,"loadingDate":"2026-11-12T15:43:59.000000Z","deliveryTerms":"CIF","modeOfPayment":"TT in Advance","updated_at":"2026-10-29T15:43:59.000000Z","created_at":"2026-10-29T15:43:59.000000Z","id":14,"orderCode":"MENONFERROUS26-10","purchase":{"id":10,"meta_company_id":2,"contact_id":2,"contact_representative_id":null,"meta_representative_id":null,"address_id":null,"destination_address_id":null,"currency_id":1,"payment_type_id":null,"payment_terms_type_id":null,"bank_account_id":null,"dollarConvertionRate":1,"total":368725,"netTotal":368725,"dealPaymentStatus":"PENDING","version":0,"created_at":"2026-10-29T15:43:59.000000Z","updated_at":"2026-10-29T15:43:59.000000Z","meta_company":{"id":2,"name":"Menon Trading LLC","code":"MENON","countryId":null,"logoUrl":null,"companyNumber":null,"vatNumber":null,"phoneNumber":null,"faxNumber":null,"email":null,"webSite":null,"footer":null,"eoriNumber":null,"address_id":null,"archiveLogoId":null,"archiveFooterId":null,"archiveStampId":null,"active":true,"version":0,"created_at":"2026-10-29T15:34:17.000000Z","updated_at":"2026-10-29T15:34:17.000000Z"},"products":[{"id":10,"deal_id":10,"product_id":4,"pack_type_id":null,"currency_id":1,"quantity":150.5,"unitPrice":2450,"pricingType":null,"vat":0,"total":368725,"associatedContractId":null,"associatedProductId":null,"status":"PENDING","version":0,"created_at":"2026-10-29T15:43:59.000000Z","updated_at":"2026-10-29T15:43:59.000000Z","product":{"id":4,"name":"Ferrous Scrap Metal","description":null,"code":"FERROUS","productCategory":"FERROUS","chemicalFormula":null,"unitOfMeasurement":null,"hsCode":null,"active":true,"version":0,"created_at":"2026-10-29T15:35:36.000000Z","updated_at":"2026-10-29T15:35:36.000000Z"}}]}}', '2026-10-29 15:43:59', '2026-10-29 15:43:59'),
(16, 'CONTRACT', 1, '2026-10-29 15:43:59', 1, 'STAGE_TRANSITION', '{"stage":{"from":"Draft","to":"Draft"}}', '2026-10-29 15:43:59', '2026-10-29 15:43:59'),
(17, 'CONTRACT', 15, '2026-10-29 15:43:59', 1, 'CREATE', '{"date":"2026-10-29T15:43:59.000000Z","type":"BUY","stage_id":2,"purchase_id":11,"sale_id":null,"contractStatus":"ACTIVE","active":true,"loadingDate":"2026-11-12T15:43:59.000000Z","deliveryTerms":"CIF","modeOfPayment":"TT in Advance","updated_at":"2026-10-29T15:43:59.000000Z","created_at":"2026-10-29T15:43:59.000000Z","id":15,"orderCode":"MENONFERROUS26-11","purchase":{"id":11,"meta_company_id":2,"contact_id":2,"contact_representative_id":null,"meta_representative_id":null,"address_id":null,"destination_address_id":null,"currency_id":1,"payment_type_id":null,"payment_terms_type_id":null,"bank_account_id":null,"dollarConvertionRate":1,"total":60000,"netTotal":60000,"dealPaymentStatus":"PENDING","version":0,"created_at":"2026-10-29T15:43:59.000000Z","updated_at":"2026-10-29T15:43:59.000000Z","meta_company":{"id":2,"name":"Menon Trading LLC","code":"MENON","countryId":null,"logoUrl":null,"companyNumber":null,"vatNumber":null,"phoneNumber":null,"faxNumber":null,"email":null,"webSite":null,"footer":null,"eoriNumber":null,"address_id":null,"archiveLogoId":null,"archiveFooterId":null,"archiveStampId":null,"active":true,"version":0,"created_at":"2026-10-29T15:34:17.000000Z","updated_at":"2026-10-29T15:34:17.000000Z"},"products":[{"id":11,"deal_id":11,"product_id":4,"pack_type_id":null,"currency_id":1,"quantity":50,"unitPrice":1200,"pricingType":null,"vat":0,"total":60000,"associatedContractId":null,"associatedProductId":null,"status":"PENDING","version":0,"created_at":"2026-10-29T15:43:59.000000Z","updated_at":"2026-10-29T15:43:59.000000Z","product":{"id":4,"name":"Ferrous Scrap Metal","description":null,"code":"FERROUS","productCategory":"FERROUS","chemicalFormula":null,"unitOfMeasurement":null,"hsCode":null,"active":true,"version":0,"created_at":"2026-10-29T15:35:36.000000Z","updated_at":"2026-10-29T15:35:36.000000Z"}}]}}', '2026-10-29 15:43:59', '2026-10-29 15:43:59'),
(18, 'CONTRACT', 16, '2026-10-29 15:43:59', 1, 'CANCEL', NULL, '2026-10-29 15:43:59', '2026-10-29 15:43:59'),
(19, 'CONTRACT', 15, '2026-11-03 05:24:42', 3, 'STAGE_TRANSITION', '{"stage":{"from":"Draft","to":"Under Review"}}', '2026-11-03 05:24:42', '2026-11-03 05:24:42'),
(20, 'CONTRACT', 15, '2026-11-03 05:24:47', 3, 'STAGE_TRANSITION', '{"stage":{"from":"Under Review","to":"Approved"}}', '2026-11-03 05:24:47', '2026-11-03 05:24:47');

-- Dumping data for table `models` (2 rows)
INSERT INTO `models` (`id`, `name`, `description`, `active`, `version`, `created_at`, `updated_at`) VALUES
(1, 'Standard Commodity Trading Pipeline', 'End-to-end trading pipeline from quotation to shipment and settlement', 1, 0, '2026-10-29 15:21:13', '2026-10-29 15:21:13'),
(2, 'Fast-Track Spot Trading Pipeline', 'Spot market deals pipeline', 1, 0, '2026-10-29 15:21:13', '2026-10-29 15:21:13');

-- Dumping data for table `offer` (1 rows)
INSERT INTO `offer` (`id`, `orderCode`, `type`, `meta_company_id`, `contact_id`, `product_id`, `quantity`, `unitPrice`, `total`, `currency_id`, `status`, `validUntil`, `version`, `created_at`, `updated_at`) VALUES
(1, 'OFF-2026-001', 'SELL', 1, 1, 2, 100, 2450, 245000, 1, 'OPEN', '2026-11-05 15:21:13', 0, '2026-10-29 15:21:13', '2026-10-29 15:21:13');

-- Dumping data for table `packinglists` (1 rows)
INSERT INTO `packinglists` (`id`, `contract_id`, `vesselName`, `shippingLine`, `blNumber`, `portOfLoading`, `portOfDischarge`, `status`, `version`, `created_at`, `updated_at`) VALUES
(1, 1, 'MSC LORETTO', 'Mediterranean Shipping Company', 'MSCLO88776655', 'Port of Santos, Brazil', 'Port of Rotterdam, Netherlands', 'ACTIVE', 0, '2026-10-29 15:21:13', '2026-10-29 15:21:13');

-- Dumping data for table `packs` (2 rows)
INSERT INTO `packs` (`id`, `packing_list_id`, `pack_type_id`, `containerNumber`, `sealNumber`, `grossWeight`, `netWeight`, `tareWeight`, `quantity`, `version`, `created_at`, `updated_at`) VALUES
(1, 1, 3, 'MSCU1234567', 'SL-998877', 25200, 25000, 200, 25, 0, '2026-10-29 15:21:13', '2026-10-29 15:21:13'),
(2, 1, 3, 'MSCU7654321', 'SL-998878', 25200, 25000, 200, 25, 0, '2026-10-29 15:21:13', '2026-10-29 15:21:13');

-- Dumping data for table `packtype` (4 rows)
INSERT INTO `packtype` (`id`, `description`, `version`, `created_at`, `updated_at`) VALUES
(1, 'Bags', 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(2, 'Drums', 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(3, 'Pallets', 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(4, 'Bulk', 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12');

-- Dumping data for table `paymenttermstype` (4 rows)
INSERT INTO `paymenttermstype` (`id`, `description`, `version`, `created_at`, `updated_at`) VALUES
(1, 'CAD (Cash Against Documents)', 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(2, 'Letter of Credit (LC)', 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(3, 'Net 30 Days', 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(4, '100% TT in Advance', 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12');

-- Dumping data for table `paymenttype` (3 rows)
INSERT INTO `paymenttype` (`id`, `description`, `version`, `created_at`, `updated_at`) VALUES
(1, 'Wire Transfer (Swift)', 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(2, 'Telegraphic Transfer (TT)', 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(3, 'Bank Cheque', 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12');

-- Dumping data for table `permissions` (1 rows)
INSERT INTO `permissions` (`id`, `user_id`, `company_id`, `accessgroup_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, '2026-10-29 15:21:13', '2026-10-29 15:21:13');

-- Dumping data for table `personal_access_tokens` (3 rows)
INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 'App\\Models\\User', 1, 'META_ERP_TOKEN', '46c2982646c58f5c191eba255f03192bb66b872af9a0c7e9008434de0f0a18d0', '["*"]', NULL, NULL, '2026-10-29 15:39:37', '2026-10-29 15:39:37'),
(2, 'App\\Models\\User', 1, 'META_ERP_TOKEN', 'a36fa53f1b4cbd15c8637658a5b9c6c779c5f34f954064fc5b5864725f7d2e5e', '["*"]', NULL, NULL, '2026-10-29 15:42:42', '2026-10-29 15:42:42'),
(3, 'App\\Models\\User', 1, 'META_ERP_TOKEN', '5f662c380e1bf3d7a4f150e10fa750d975a9403ab0e5c5903e312599e50373d8', '["*"]', NULL, NULL, '2026-10-29 15:43:58', '2026-10-29 15:43:58');

-- Dumping data for table `products` (4 rows)
INSERT INTO `products` (`id`, `name`, `description`, `code`, `productCategory`, `chemicalFormula`, `unitOfMeasurement`, `hsCode`, `active`, `version`, `created_at`, `updated_at`) VALUES
(1, 'Copper Cathodes Grade A', 'Electrolytic Copper Cathodes 99.99% purity', 'CU-CAT-A', 'NON_FERROUS', 'Cu 99.99%', 'MT', '74031100', 1, 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(2, 'Aluminium Ingots 99.7%', 'Primary Aluminium Ingots grade P1020A', 'AL-ING-997', 'NON_FERROUS', 'Al 99.7%', 'MT', '76011000', 1, 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(3, 'Heavy Melting Scrap (HMS 1&2)', 'ISRI 200-206 specification ferrous scrap steel', 'FE-HMS-8020', 'FERROUS', 'Fe > 92%', 'MT', '72044900', 1, 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(4, 'Ferrous Scrap Metal', NULL, 'FERROUS', 'FERROUS', NULL, NULL, NULL, 1, 0, '2026-10-29 15:35:36', '2026-10-29 15:35:36');

-- Dumping data for table `representatives` (2 rows)
INSERT INTO `representatives` (`id`, `company_id`, `contact_id`, `address_id`, `name`, `email`, `phone`, `type`, `version`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, NULL, 'James Harrison', 'james.harrison@metaenterprices.co.uk', '+44 7700 900123', 'SALES', 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(2, NULL, 1, NULL, 'Carlos Alberto Silva', 'carlos.silva@csn.com.br', '+55 24 3344 5500', 'SALES', 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12');

-- Dumping data for table `stages` (10 rows)
INSERT INTO `stages` (`id`, `model_id`, `name`, `description`, `priority`, `stageType`, `active`, `version`, `created_at`, `updated_at`) VALUES
(1, 2, 'Draft', 'New trade contract draft', 1, 'DRAFT', 1, 0, '2026-10-29 15:21:13', '2026-10-29 15:21:13'),
(2, 1, 'Draft', 'New trade contract draft', 1, 'DRAFT', 1, 0, '2026-10-29 15:21:13', '2026-10-29 15:21:13'),
(3, 2, 'Under Review', 'Trade terms and KYC verification', 2, 'REVIEW', 1, 0, '2026-10-29 15:21:13', '2026-10-29 15:21:13'),
(4, 1, 'Under Review', 'Trade terms and KYC verification', 2, 'REVIEW', 1, 0, '2026-10-29 15:21:13', '2026-10-29 15:21:13'),
(5, 2, 'Approved', 'Counterparty signed and executed', 3, 'APPROVED', 1, 0, '2026-10-29 15:21:13', '2026-10-29 15:21:13'),
(6, 1, 'Approved', 'Counterparty signed and executed', 3, 'APPROVED', 1, 0, '2026-10-29 15:21:13', '2026-10-29 15:21:13'),
(7, 2, 'Logistics', 'Vessel booking and container packing', 4, 'LOGISTICS', 1, 0, '2026-10-29 15:21:13', '2026-10-29 15:21:13'),
(8, 1, 'Logistics', 'Vessel booking and container packing', 4, 'LOGISTICS', 1, 0, '2026-10-29 15:21:13', '2026-10-29 15:21:13'),
(9, 2, 'Completed', 'Discharged and payments settled', 5, 'COMPLETED', 1, 0, '2026-10-29 15:21:13', '2026-10-29 15:21:13'),
(10, 1, 'Completed', 'Discharged and payments settled', 5, 'COMPLETED', 1, 0, '2026-10-29 15:21:13', '2026-10-29 15:21:13');

-- Dumping data for table `templates` (8 rows)
INSERT INTO `templates` (`id`, `name`, `type`, `language_id`, `html`, `keys`, `version`, `created_at`, `updated_at`) VALUES
(1, 'Standard Purchase Order', 'PURCHASE_ORDER', 1, '<html><head><style>body{font-family:Arial,sans-serif;margin:40px;color:#333;}.header{border-bottom:2px solid #2563eb;padding-bottom:10px;}.title{font-size:24px;font-weight:bold;color:#1e3a8a;}</style></head><body><div class="header"><div class="title">PURCHASE ORDER</div><div>Company: {meta_company_name} (Reg: {meta_company_number})</div><div>Order Code: <strong>{contract_order_code}</strong> | Date: {contract_date}</div></div><p>To Supplier: <strong>{contract_buyer_name}</strong></p><div>{contract_product_table}</div><p style="font-size:18px;font-weight:bold;">Total Amount: {contract_total_amount}</p></body></html>', '{meta_company_name},{meta_company_number},{contract_buyer_name},{contract_order_code},{contract_date},{contract_product_table},{contract_total_amount}', 0, '2026-10-29 15:21:13', '2026-10-29 15:21:13'),
(2, 'Standard Sales Contract', 'CONTRACT_OF_SALES', 1, '<html><head><style>body{font-family:Arial,sans-serif;margin:40px;color:#333;}.header{border-bottom:2px solid #059669;padding-bottom:10px;}.title{font-size:24px;font-weight:bold;color:#065f46;}</style></head><body><div class="header"><div class="title">CONTRACT OF SALES</div><div>Seller: {meta_company_name}</div><div>Contract Code: <strong>{contract_order_code}</strong> | Date: {contract_date}</div></div><p>Buyer: <strong>{contract_buyer_name}</strong></p><div>{contract_product_table}</div><p style="font-size:18px;font-weight:bold;">Total Value: {contract_total_amount}</p></body></html>', '{meta_company_name},{contract_buyer_name},{contract_order_code},{contract_date},{contract_product_table},{contract_total_amount}', 0, '2026-10-29 15:21:13', '2026-10-29 15:21:13'),
(3, 'Commercial Invoice', 'COMMERCIAL_INVOICE', 1, '<html><head><style>body{font-family:Arial,sans-serif;margin:40px;color:#333;}</style></head><body><h2>COMMERCIAL INVOICE</h2><p>Invoice for Contract: {contract_order_code}</p><div>{contract_product_table}</div><h3>Total Due: {contract_total_amount}</h3></body></html>', '{meta_company_name},{contract_buyer_name},{contract_order_code},{contract_date},{contract_product_table},{contract_total_amount}', 0, '2026-10-29 15:21:13', '2026-10-29 15:21:13'),
(4, 'Packing List', 'PACKING_LIST', 1, '<html><head><style>body{font-family:Arial,sans-serif;margin:40px;color:#333;}</style></head><body><h2>CONSIGNMENT PACKING LIST</h2><p>Contract: {contract_order_code} | Vessel: {packing_list_vessel} | B/L: {packing_list_bl}</p><div>{packing_list_table}</div></body></html>', '{meta_company_name},{contract_order_code},{packing_list_vessel},{packing_list_bl},{packing_list_table}', 0, '2026-10-29 15:21:13', '2026-10-29 15:21:13'),
(5, 'Purchase Contract Template', 'CONTRACT', NULL, '<html><body><h1>Contract {contract_order_code}</h1><p>Supplier: {meta_company_name}</p><p>Total: {contract_total_amount}</p></body></html>', '{meta_company_name},{contract_order_code}', 0, '2026-10-29 15:39:06', '2026-10-29 15:39:06'),
(6, 'Strict Document Template 1793288346', 'INVOICE', NULL, '<div>Invoice: {missing_custom_token}</div>', '{missing_custom_token}', 0, '2026-10-29 15:39:06', '2026-10-29 15:39:06'),
(7, 'Strict Document Template 1793288563', 'INVOICE', NULL, '<div>Invoice: {missing_custom_token}</div>', '{missing_custom_token}', 0, '2026-10-29 15:42:43', '2026-10-29 15:42:43'),
(8, 'Strict Document Template 1793288639', 'INVOICE', NULL, '<div>Invoice: {missing_custom_token}</div>', '{missing_custom_token}', 0, '2026-10-29 15:43:59', '2026-10-29 15:43:59');

-- Dumping data for table `types` (4 rows)
INSERT INTO `types` (`id`, `description`, `version`, `created_at`, `updated_at`) VALUES
(1, 'Buyer', 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(2, 'Seller', 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(3, 'Broker', 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12'),
(4, 'Transporter', 0, '2026-10-29 15:21:12', '2026-10-29 15:21:12');

-- Dumping data for table `users` (3 rows)
INSERT INTO `users` (`id`, `name`, `displayName`, `photoUrl`, `signatureFile`, `signature_file_name`, `occupation`, `username`, `password`, `active`, `role`, `secret`, `enable2fa`, `archiveSignatureId`, `version`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Administrator', 'System Admin', NULL, NULL, NULL, 'Chief Executive', 'admin', '$2b$10$0Z8nfavfrOSDfsQ.IuLV/uazPP8Iavdui/xr7eOTZVnNJzj/DVFVm', 1, 'ROLE_ADMIN', 'JBSWY3DPEHPK3PXP', 0, NULL, 0, NULL, '2026-10-29 15:21:13', '2026-11-03 05:19:21'),
(2, 'Test User', 'Test User', NULL, NULL, NULL, NULL, 'testuser', '$2y$04$laGyNIknQIC27XsdLpcAdeuXNrfTaKgx.QAtVAnNmFC7xrOf9/mQy', 1, 'ROLE_USER', NULL, 0, NULL, 0, NULL, '2026-10-29 15:33:23', '2026-11-03 05:19:21'),
(3, 'Navdeep Birla', 'Navdeep Birla', NULL, NULL, NULL, 'Trading Specialist', 'navdeepwork475@gmail.com', '$2y$10$H9KEKykHxiG78XH9oeOqPOLX1YyrZ2.C7o1EBZ92U9dkhANnjU2Nq', 1, 'ROLE_USER', 'F6TZQOHOTN6RRMXL', 0, NULL, 0, NULL, '2026-11-03 05:24:04', '2026-11-03 05:24:04');

-- ========================================================
-- Constraints for dumped tables (Foreign Keys)
-- ========================================================

alter table `access` add constraint `access_accessgroup_id_foreign` foreign key (`accessgroup_id`) references `accessgroups` (`id`) on delete cascade;
alter table `addresses` add constraint `addresses_country_id_foreign` foreign key (`country_id`) references `countries` (`id`) on delete set null;
alter table `companies` add constraint `companies_address_id_foreign` foreign key (`address_id`) references `addresses` (`id`) on delete set null;
alter table `contacts` add constraint `contacts_currency_id_foreign` foreign key (`currency_id`) references `currency` (`id`) on delete set null;
alter table `contacts` add constraint `contacts_language_id_foreign` foreign key (`language_id`) references `languages` (`id`) on delete set null;
alter table `bankaccounts` add constraint `bankaccounts_company_id_foreign` foreign key (`company_id`) references `companies` (`id`) on delete cascade;
alter table `bankaccounts` add constraint `bankaccounts_contact_id_foreign` foreign key (`contact_id`) references `contacts` (`id`) on delete cascade;
alter table `representatives` add constraint `representatives_company_id_foreign` foreign key (`company_id`) references `companies` (`id`) on delete cascade;
alter table `representatives` add constraint `representatives_contact_id_foreign` foreign key (`contact_id`) references `contacts` (`id`) on delete cascade;
alter table `permissions` add constraint `permissions_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete cascade;
alter table `permissions` add constraint `permissions_company_id_foreign` foreign key (`company_id`) references `companies` (`id`) on delete cascade;
alter table `permissions` add constraint `permissions_accessgroup_id_foreign` foreign key (`accessgroup_id`) references `accessgroups` (`id`) on delete cascade;
alter table `contact_types` add constraint `contact_types_contact_id_foreign` foreign key (`contact_id`) references `contacts` (`id`) on delete cascade;
alter table `contact_types` add constraint `contact_types_type_id_foreign` foreign key (`type_id`) references `types` (`id`) on delete cascade;
alter table `contact_products` add constraint `contact_products_contact_id_foreign` foreign key (`contact_id`) references `contacts` (`id`) on delete cascade;
alter table `contact_products` add constraint `contact_products_product_id_foreign` foreign key (`product_id`) references `products` (`id`) on delete cascade;
alter table `productcontracts` add constraint `productcontracts_product_id_foreign` foreign key (`product_id`) references `products` (`id`) on delete set null;
alter table `stages` add constraint `stages_model_id_foreign` foreign key (`model_id`) references `models` (`id`) on delete cascade;
alter table `stageactions` add constraint `stageactions_stage_id_foreign` foreign key (`stage_id`) references `stages` (`id`) on delete cascade;
alter table `stageactions` add constraint `stageactions_action_id_foreign` foreign key (`action_id`) references `actions` (`id`) on delete cascade;
alter table `deal` add constraint `deal_meta_company_id_foreign` foreign key (`meta_company_id`) references `companies` (`id`) on delete set null;
alter table `deal` add constraint `deal_contact_id_foreign` foreign key (`contact_id`) references `contacts` (`id`) on delete set null;
alter table `deal` add constraint `deal_contact_representative_id_foreign` foreign key (`contact_representative_id`) references `representatives` (`id`) on delete set null;
alter table `deal` add constraint `deal_meta_representative_id_foreign` foreign key (`meta_representative_id`) references `representatives` (`id`) on delete set null;
alter table `deal` add constraint `deal_address_id_foreign` foreign key (`address_id`) references `addresses` (`id`) on delete set null;
alter table `deal` add constraint `deal_destination_address_id_foreign` foreign key (`destination_address_id`) references `addresses` (`id`) on delete set null;
alter table `deal` add constraint `deal_currency_id_foreign` foreign key (`currency_id`) references `currency` (`id`) on delete set null;
alter table `deal` add constraint `deal_payment_type_id_foreign` foreign key (`payment_type_id`) references `paymenttype` (`id`) on delete set null;
alter table `deal` add constraint `deal_payment_terms_type_id_foreign` foreign key (`payment_terms_type_id`) references `paymenttermstype` (`id`) on delete set null;
alter table `deal` add constraint `deal_bank_account_id_foreign` foreign key (`bank_account_id`) references `bankaccounts` (`id`) on delete set null;
alter table `dealproduct` add constraint `dealproduct_deal_id_foreign` foreign key (`deal_id`) references `deal` (`id`) on delete cascade;
alter table `dealproduct` add constraint `dealproduct_product_id_foreign` foreign key (`product_id`) references `products` (`id`) on delete set null;
alter table `dealproduct` add constraint `dealproduct_pack_type_id_foreign` foreign key (`pack_type_id`) references `packtype` (`id`) on delete set null;
alter table `dealproduct` add constraint `dealproduct_currency_id_foreign` foreign key (`currency_id`) references `currency` (`id`) on delete set null;
alter table `contracts` add constraint `contracts_stage_id_foreign` foreign key (`stage_id`) references `stages` (`id`) on delete set null;
alter table `contracts` add constraint `contracts_purchase_id_foreign` foreign key (`purchase_id`) references `deal` (`id`) on delete set null;
alter table `contracts` add constraint `contracts_sale_id_foreign` foreign key (`sale_id`) references `deal` (`id`) on delete set null;
alter table `packinglists` add constraint `packinglists_contract_id_foreign` foreign key (`contract_id`) references `contracts` (`id`) on delete cascade;
alter table `packs` add constraint `packs_packing_list_id_foreign` foreign key (`packing_list_id`) references `packinglists` (`id`) on delete cascade;
alter table `packs` add constraint `packs_pack_type_id_foreign` foreign key (`pack_type_id`) references `packtype` (`id`) on delete set null;
alter table `templates` add constraint `templates_language_id_foreign` foreign key (`language_id`) references `languages` (`id`) on delete set null;
alter table `generatedfiles` add constraint `generatedfiles_contract_id_foreign` foreign key (`contract_id`) references `contracts` (`id`) on delete cascade;
alter table `generatedfiles` add constraint `generatedfiles_template_id_foreign` foreign key (`template_id`) references `templates` (`id`) on delete set null;
alter table `generatedfiles` add constraint `generatedfiles_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete set null;
alter table `uploadedfiles` add constraint `uploadedfiles_contract_id_foreign` foreign key (`contract_id`) references `contracts` (`id`) on delete cascade;
alter table `checklists` add constraint `checklists_contract_id_foreign` foreign key (`contract_id`) references `contracts` (`id`) on delete cascade;
alter table `checkitems` add constraint `checkitems_checklist_id_foreign` foreign key (`checklist_id`) references `checklists` (`id`) on delete cascade;
alter table `comments` add constraint `comments_contract_id_foreign` foreign key (`contract_id`) references `contracts` (`id`) on delete cascade;
alter table `comments` add constraint `comments_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete set null;
alter table `offer` add constraint `offer_meta_company_id_foreign` foreign key (`meta_company_id`) references `companies` (`id`) on delete set null;
alter table `offer` add constraint `offer_contact_id_foreign` foreign key (`contact_id`) references `contacts` (`id`) on delete set null;
alter table `offer` add constraint `offer_product_id_foreign` foreign key (`product_id`) references `products` (`id`) on delete set null;
alter table `offer` add constraint `offer_currency_id_foreign` foreign key (`currency_id`) references `currency` (`id`) on delete set null;
alter table `detachednote` add constraint `detachednote_meta_company_id_foreign` foreign key (`meta_company_id`) references `companies` (`id`) on delete set null;
alter table `detachednote` add constraint `detachednote_contact_id_foreign` foreign key (`contact_id`) references `contacts` (`id`) on delete set null;
alter table `detachednote` add constraint `detachednote_currency_id_foreign` foreign key (`currency_id`) references `currency` (`id`) on delete set null;
alter table `detachednotedetail` add constraint `detachednotedetail_detached_note_id_foreign` foreign key (`detached_note_id`) references `detachednote` (`id`) on delete cascade;

SET FOREIGN_KEY_CHECKS=1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
