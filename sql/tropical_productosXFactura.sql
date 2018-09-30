CREATE DATABASE  IF NOT EXISTS `tropical` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `tropical`;
-- MySQL dump 10.13  Distrib 5.7.17, for Win64 (x86_64)
--
-- Host: 104.131.5.198    Database: tropical
-- ------------------------------------------------------
-- Server version	5.5.56-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `productosXFactura`
--

DROP TABLE IF EXISTS `productosXFactura`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `productosXFactura` (
  `id` char(36) NOT NULL,
  `idFactura` char(36) NOT NULL,
  `idPrecio` char(36) DEFAULT NULL,
  `numeroLinea` int(4) NOT NULL DEFAULT '0' COMMENT 'Maximo de lineas por factura = 1000.',
  `idTipoCodigo` int(11) DEFAULT NULL,
  `codigo` varchar(20) DEFAULT NULL,
  `cantidad` decimal(16,3) NOT NULL DEFAULT '0.000',
  `idUnidadMedida` int(11) NOT NULL,
  `unidadMedidaComercial` varchar(20) DEFAULT NULL,
  `detalle` varchar(160) NOT NULL,
  `precioUnitario` decimal(18,5) NOT NULL,
  `montoTotal` decimal(18,5) NOT NULL,
  `montoDescuento` decimal(18,5) DEFAULT NULL,
  `naturalezaDescuento` varchar(80) DEFAULT NULL,
  `subTotal` decimal(18,5) NOT NULL,
  `codigoImpuesto` varchar(2) DEFAULT NULL,
  `tarifaImpuesto` decimal(4,2) DEFAULT NULL,
  `montoImpuesto` decimal(18,5) DEFAULT NULL,
  `idExoneracionImpuesto` int(11) DEFAULT NULL,
  `montoTotalLinea` decimal(18,5) NOT NULL DEFAULT '0.00000',
  PRIMARY KEY (`id`),
  KEY `fk_productosXFactura_factura1_idx` (`idFactura`),
  CONSTRAINT `fk_productosXFactura_factura1` FOREIGN KEY (`idFactura`) REFERENCES `factura` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Productos vendidos en una factura (Detalle).';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `productosXFactura`
--

LOCK TABLES `productosXFactura` WRITE;
/*!40000 ALTER TABLE `productosXFactura` DISABLE KEYS */;
INSERT INTO `productosXFactura` VALUES ('002f7ba7-c266-11e8-a113-f2f00eda9788','1e7af315-9616-4551-b358-4d26dede4681','715da8ec-e431-43e3-9dfc-f77e5c64c692',1,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, TCOC',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('0075cf77-c266-11e8-a113-f2f00eda9788','1e7af315-9616-4551-b358-4d26dede4681','715da8ec-e431-43e3-9dfc-f77e5c64c692',2,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, TCOC',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('01dffbb6-c276-11e8-a113-f2f00eda9788','63580c52-0f3a-4773-9f3d-8727ea16656a','715da8ec-e431-43e3-9dfc-f77e5c64c692',1,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, TCOC',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('1310de1d-c25b-11e8-a113-f2f00eda9788','6ea364c8-fa19-4148-851f-afe8728fa74f','715da8ec-e431-43e3-9dfc-f77e5c64c692',1,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, TCOC',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('1727fe12-c232-11e8-a113-f2f00eda9788','7acb50df-f703-427b-915a-4a881262ead0','715da8ec-e431-43e3-9dfc-f77e5c64c692',1,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, TCOC',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('2132c451-c265-11e8-a113-f2f00eda9788','d631699b-f595-4ebe-8cff-16328ceb397f','715da8ec-e431-43e3-9dfc-f77e5c64c692',1,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, TCOC',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('216a1e2d-c265-11e8-a113-f2f00eda9788','d631699b-f595-4ebe-8cff-16328ceb397f','715da8ec-e431-43e3-9dfc-f77e5c64c692',2,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, NTOP',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('218f8168-c265-11e8-a113-f2f00eda9788','d631699b-f595-4ebe-8cff-16328ceb397f','715da8ec-e431-43e3-9dfc-f77e5c64c692',3,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, NTOP',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('21b6854f-c265-11e8-a113-f2f00eda9788','d631699b-f595-4ebe-8cff-16328ceb397f','715da8ec-e431-43e3-9dfc-f77e5c64c692',4,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, TCOC',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('21ecb223-c265-11e8-a113-f2f00eda9788','d631699b-f595-4ebe-8cff-16328ceb397f','715da8ec-e431-43e3-9dfc-f77e5c64c692',5,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, NTOP',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('22278f9e-c265-11e8-a113-f2f00eda9788','d631699b-f595-4ebe-8cff-16328ceb397f','715da8ec-e431-43e3-9dfc-f77e5c64c692',6,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, TCOC',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('224eb984-c265-11e8-a113-f2f00eda9788','d631699b-f595-4ebe-8cff-16328ceb397f','715da8ec-e431-43e3-9dfc-f77e5c64c692',7,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, NTOP',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('2283ece3-c265-11e8-a113-f2f00eda9788','d631699b-f595-4ebe-8cff-16328ceb397f','715da8ec-e431-43e3-9dfc-f77e5c64c692',8,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, TCOC',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('236f08ea-c233-11e8-a113-f2f00eda9788','6ca3bf5d-28a8-4f62-b02d-90a1f6c999a8','715da8ec-e431-43e3-9dfc-f77e5c64c692',1,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, TCOC',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('2613e8e9-c2a5-11e8-a113-f2f00eda9788','b55ec6a5-59e7-4682-806a-cd28fb70938a','715da8ec-e431-43e3-9dfc-f77e5c64c692',1,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, TCOC',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('266fe644-c234-11e8-a113-f2f00eda9788','5d683a75-d06e-442f-82b2-b572dd46d0c3','715da8ec-e431-43e3-9dfc-f77e5c64c692',1,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, TCOC',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('2835badc-c269-11e8-a113-f2f00eda9788','bc785b60-758d-4ab2-9e4d-eb5de4272bac','715da8ec-e431-43e3-9dfc-f77e5c64c692',1,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, TCOC',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('29f870f1-c26a-11e8-a113-f2f00eda9788','1e8fb9f2-5c34-4a97-9b1d-d0c443622d2b','715da8ec-e431-43e3-9dfc-f77e5c64c692',1,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, NTOP',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('2a2cdc60-c26a-11e8-a113-f2f00eda9788','1e8fb9f2-5c34-4a97-9b1d-d0c443622d2b','715da8ec-e431-43e3-9dfc-f77e5c64c692',2,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, TCOC',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('2d0e64c5-c232-11e8-a113-f2f00eda9788','0045a0bc-663f-4587-905d-a7d2004b4f13','715da8ec-e431-43e3-9dfc-f77e5c64c692',1,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, TCOC',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('3c20b84c-c26b-11e8-a113-f2f00eda9788','1ee45ae1-ebaf-4a9f-b998-56de66c09ac2','715da8ec-e431-43e3-9dfc-f77e5c64c692',1,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, TCOC',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('4558a064-c246-11e8-a113-f2f00eda9788','7b35a070-f09f-4261-8046-2feb8bf0f5ca','715da8ec-e431-43e3-9dfc-f77e5c64c692',1,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, NTOP',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('4558b868-c246-11e8-a113-f2f00eda9788','7b35a070-f09f-4261-8046-2feb8bf0f5ca','715da8ec-e431-43e3-9dfc-f77e5c64c692',2,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, TCOC',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('46a5ba9f-c267-11e8-a113-f2f00eda9788','a60d3a19-7089-4155-9e2e-bd46dc58db5a','715da8ec-e431-43e3-9dfc-f77e5c64c692',1,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, TCOC',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('46a5d3b8-c267-11e8-a113-f2f00eda9788','a60d3a19-7089-4155-9e2e-bd46dc58db5a','715da8ec-e431-43e3-9dfc-f77e5c64c692',2,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, NTOP',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('4b720cb3-c265-11e8-a113-f2f00eda9788','200c3b17-49e6-4226-8fa9-586aa13ee07f','715da8ec-e431-43e3-9dfc-f77e5c64c692',1,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, TCOC',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('4baaaba7-c265-11e8-a113-f2f00eda9788','200c3b17-49e6-4226-8fa9-586aa13ee07f','715da8ec-e431-43e3-9dfc-f77e5c64c692',2,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, NTOP',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('4bd0d46a-c265-11e8-a113-f2f00eda9788','200c3b17-49e6-4226-8fa9-586aa13ee07f','715da8ec-e431-43e3-9dfc-f77e5c64c692',3,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, NTOP',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('4bf5c05c-c265-11e8-a113-f2f00eda9788','200c3b17-49e6-4226-8fa9-586aa13ee07f','715da8ec-e431-43e3-9dfc-f77e5c64c692',4,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, TCOC',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('5007d4e5-c26f-11e8-a113-f2f00eda9788','cffaedb3-5634-415f-b950-24444a7295ff','715da8ec-e431-43e3-9dfc-f77e5c64c692',1,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, TCOC',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('514a724f-c269-11e8-a113-f2f00eda9788','ed037b01-8485-4b11-a029-366c5cd5e58a','715da8ec-e431-43e3-9dfc-f77e5c64c692',1,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, NTOP',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('514a8ffd-c269-11e8-a113-f2f00eda9788','ed037b01-8485-4b11-a029-366c5cd5e58a','715da8ec-e431-43e3-9dfc-f77e5c64c692',2,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, TCOC',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('5a8b0305-c2d1-11e8-a113-f2f00eda9788','f9d86e92-30ba-46ad-b835-568ed41dac7e','715da8ec-e431-43e3-9dfc-f77e5c64c692',1,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, TCOC',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('6004b6df-c231-11e8-a113-f2f00eda9788','5e353114-f004-4937-bcbc-ed121b296445','715da8ec-e431-43e3-9dfc-f77e5c64c692',1,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, TCOC',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('65c94e51-c2d1-11e8-a113-f2f00eda9788','89452a14-2c5b-4ff5-972b-5a7fbce6cdb6','715da8ec-e431-43e3-9dfc-f77e5c64c692',1,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, TCOC',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('6ee22b24-c265-11e8-a113-f2f00eda9788','6213db92-1aaa-447c-9c6c-c79ed913b0b0','715da8ec-e431-43e3-9dfc-f77e5c64c692',1,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, NTOP',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('6f06ecf5-c265-11e8-a113-f2f00eda9788','6213db92-1aaa-447c-9c6c-c79ed913b0b0','715da8ec-e431-43e3-9dfc-f77e5c64c692',2,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, TCOC',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('6f3c94c6-c2bf-11e8-a113-f2f00eda9788','adf05a09-e4fd-45a9-84d8-12bc69512977','715da8ec-e431-43e3-9dfc-f77e5c64c792',1,1,'08oz',1.000,78,NULL,'08oz, BANA, BANA, TCOC',1592.92035,1592.92035,0.00000,'No aplican descuentos',1592.92035,'1',13.00,207.07965,NULL,1800.00000),('6f6dc1e0-c2bf-11e8-a113-f2f00eda9788','adf05a09-e4fd-45a9-84d8-12bc69512977','715da8ec-e431-43e3-9dfc-f77e5c64c792',2,1,'08oz',1.000,78,NULL,'08oz, BANA, BANA, TCOC',1592.92035,1592.92035,0.00000,'No aplican descuentos',1592.92035,'1',13.00,207.07965,NULL,1800.00000),('6f8ee8ed-c232-11e8-a113-f2f00eda9788','3726175e-c368-40c3-9ccd-90075178a1d8','715da8ec-e431-43e3-9dfc-f77e5c64c692',1,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, TCOC',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('6fa1e001-c2bf-11e8-a113-f2f00eda9788','adf05a09-e4fd-45a9-84d8-12bc69512977','715da8ec-e431-43e3-9dfc-f77e5c64c692',3,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, NTOP',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('7405d21d-c267-11e8-a113-f2f00eda9788','1566d6b7-5d10-4dd7-83ed-67587baabe1b','715da8ec-e431-43e3-9dfc-f77e5c64c692',1,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, NTOP',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('7ffbac8b-c276-11e8-a113-f2f00eda9788','ce091386-1a80-4b2d-a9f8-399555a25093','715da8ec-e431-43e3-9dfc-f77e5c64c692',1,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, TCOC',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('80b55d28-c275-11e8-a113-f2f00eda9788','59170de4-7399-4a90-b1d9-fb27074bbcf4','715da8ec-e431-43e3-9dfc-f77e5c64c692',1,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, TCOC',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('80db3812-c275-11e8-a113-f2f00eda9788','59170de4-7399-4a90-b1d9-fb27074bbcf4','715da8ec-e431-43e3-9dfc-f77e5c64c692',2,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, NTOP',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('811545c9-c275-11e8-a113-f2f00eda9788','59170de4-7399-4a90-b1d9-fb27074bbcf4','715da8ec-e431-43e3-9dfc-f77e5c64c692',3,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, TCOC',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('84057afe-c26f-11e8-a113-f2f00eda9788','59b8037f-ffbf-465e-a2de-63adb5b19eaa','715da8ec-e431-43e3-9dfc-f77e5c64c692',1,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, TCOC',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('8426c45d-c26a-11e8-a113-f2f00eda9788','74e726b1-df7c-4d26-b0b8-7019fc08e901','715da8ec-e431-43e3-9dfc-f77e5c64c692',1,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, TCOC',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('8440e612-c26f-11e8-a113-f2f00eda9788','59b8037f-ffbf-465e-a2de-63adb5b19eaa','715da8ec-e431-43e3-9dfc-f77e5c64c692',2,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, NTOP',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('8482947d-c26f-11e8-a113-f2f00eda9788','59b8037f-ffbf-465e-a2de-63adb5b19eaa','715da8ec-e431-43e3-9dfc-f77e5c64c692',3,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, TCOC',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('878cbb5c-c242-11e8-a113-f2f00eda9788','0240534b-731b-400e-8ec9-36da80cf0a63','715da8ec-e431-43e3-9dfc-f77e5c64c692',1,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, TCOC',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('878cd780-c242-11e8-a113-f2f00eda9788','0240534b-731b-400e-8ec9-36da80cf0a63','715da8ec-e431-43e3-9dfc-f77e5c64c692',2,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, NTOP',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('8eaf9d0c-c26f-11e8-a113-f2f00eda9788','f53cb722-e29a-4393-9325-fc65e593e588','715da8ec-e431-43e3-9dfc-f77e5c64c692',1,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, TCOC',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('8ed4ef98-c26f-11e8-a113-f2f00eda9788','f53cb722-e29a-4393-9325-fc65e593e588','715da8ec-e431-43e3-9dfc-f77e5c64c692',2,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, NTOP',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('8f0febba-c26f-11e8-a113-f2f00eda9788','f53cb722-e29a-4393-9325-fc65e593e588','715da8ec-e431-43e3-9dfc-f77e5c64c692',3,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, TCOC',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('907cd36b-c23c-11e8-a113-f2f00eda9788','4bb4c7a1-a015-41c4-ad73-148caba6df07','715da8ec-e431-43e3-9dfc-f77e5c64c692',1,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, TCOC',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('90a33f04-c23c-11e8-a113-f2f00eda9788','4bb4c7a1-a015-41c4-ad73-148caba6df07','715da8ec-e431-43e3-9dfc-f77e5c64c692',2,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, NTOP',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('90c99a65-c23c-11e8-a113-f2f00eda9788','4bb4c7a1-a015-41c4-ad73-148caba6df07','715da8ec-e431-43e3-9dfc-f77e5c64c692',3,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, TCOC',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('90f0023a-c23c-11e8-a113-f2f00eda9788','4bb4c7a1-a015-41c4-ad73-148caba6df07','715da8ec-e431-43e3-9dfc-f77e5c64c692',4,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, NTOP',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('961f3c5f-c232-11e8-a113-f2f00eda9788','e6653ac2-71b2-45f1-8d92-bf74ca7a3ccd','715da8ec-e431-43e3-9dfc-f77e5c64c692',1,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, TCOC',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('974028fa-c266-11e8-a113-f2f00eda9788','5f0c3e97-e518-4de8-82c8-affcb67d4362','715da8ec-e431-43e3-9dfc-f77e5c64c692',1,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, TCOC',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('976f9230-c266-11e8-a113-f2f00eda9788','5f0c3e97-e518-4de8-82c8-affcb67d4362','715da8ec-e431-43e3-9dfc-f77e5c64c692',2,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, NTOP',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('9ecf639a-c274-11e8-a113-f2f00eda9788','5a930564-957f-45b7-9379-9050bae615a8','715da8ec-e431-43e3-9dfc-f77e5c64c692',1,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, TCOC',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('af71b431-c232-11e8-a113-f2f00eda9788','1a686d54-20df-4f27-8bb9-894e3b00ce8c','715da8ec-e431-43e3-9dfc-f77e5c64c692',1,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, TCOC',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('b1811052-c265-11e8-a113-f2f00eda9788','14a967d6-eef6-4bb5-94eb-efab2e8a3d28','715da8ec-e431-43e3-9dfc-f77e5c64c692',1,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, TCOC',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('bc2b2f24-c26f-11e8-a113-f2f00eda9788','6944f99c-048c-44fb-b0b3-d6cccd323cc6','715da8ec-e431-43e3-9dfc-f77e5c64c692',1,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, TCOC',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('bc557d12-c26f-11e8-a113-f2f00eda9788','6944f99c-048c-44fb-b0b3-d6cccd323cc6','715da8ec-e431-43e3-9dfc-f77e5c64c692',2,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, NTOP',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('c130088a-c269-11e8-a113-f2f00eda9788','c4dceaf9-0ef4-4422-9545-07367be75eb9','715da8ec-e431-43e3-9dfc-f77e5c64c692',1,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, NTOP',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('c1559f68-c269-11e8-a113-f2f00eda9788','c4dceaf9-0ef4-4422-9545-07367be75eb9','715da8ec-e431-43e3-9dfc-f77e5c64c692',2,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, TCOC',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('c4e6aa20-c231-11e8-a113-f2f00eda9788','93e4aee5-3306-44f5-9d37-21ee2b5d3940','715da8ec-e431-43e3-9dfc-f77e5c64c692',1,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, TCOC',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('ca78f9bd-c275-11e8-a113-f2f00eda9788','9965f537-dea2-4b41-8005-b6f85b707ec4','715da8ec-e431-43e3-9dfc-f77e5c64c692',1,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, TCOC',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('df6b2153-c232-11e8-a113-f2f00eda9788','5ef7b32c-d515-4e9a-8686-9c4db831ff07','715da8ec-e431-43e3-9dfc-f77e5c64c692',1,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, TCOC',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('e44d6083-c233-11e8-a113-f2f00eda9788','105d8ee3-6a5b-4058-a04b-0d6a4a6ce60a','715da8ec-e431-43e3-9dfc-f77e5c64c692',1,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, TCOC',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('ea81fa5c-c2bc-11e8-a113-f2f00eda9788','6be5775f-90ed-4608-82fe-8a5049dfa36d','715da8ec-e431-43e3-9dfc-f77e5c64c692',1,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, TCOC',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000),('fe9fd8b0-c294-11e8-a113-f2f00eda9788','c9d17752-e349-49e3-81f8-9c82466d77e6','715da8ec-e431-43e3-9dfc-f77e5c64c692',1,1,'12oz',1.000,78,NULL,'12oz, BANA, BANA, TCOC',2212.38938,2212.38938,0.00000,'No aplican descuentos',2212.38938,'1',13.00,287.61062,NULL,2500.00000);
/*!40000 ALTER TABLE `productosXFactura` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-09-29 17:55:13
