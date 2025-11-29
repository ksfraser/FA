-- Serial Number Tracking Tables for FrontAccounting
-- Inspired by WebERP's serial tracking implementation

-- Add serial tracking fields to stock_master table
ALTER TABLE `0_stock_master`
ADD COLUMN `controlled` tinyint(4) NOT NULL DEFAULT '0' AFTER `inactive`,
ADD COLUMN `serialised` tinyint(4) NOT NULL DEFAULT '0' AFTER `controlled`;

-- Create stock_serial_items table for storing serial number information
CREATE TABLE `0_stock_serial_items` (
  `stock_id` varchar(20) NOT NULL,
  `loc_code` varchar(5) NOT NULL,
  `serial_no` varchar(30) NOT NULL,
  `expiration_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `quantity` double NOT NULL DEFAULT '0',
  `quality_text` text NOT NULL,
  `createdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`stock_id`,`serial_no`,`loc_code`),
  KEY `StockID` (`stock_id`),
  KEY `LocCode` (`loc_code`),
  KEY `serialno` (`serial_no`),
  KEY `createdate` (`createdate`),
  CONSTRAINT `stock_serial_items_ibfk_1` FOREIGN KEY (`stock_id`) REFERENCES `0_stock_master` (`stock_id`),
  CONSTRAINT `stock_serial_items_ibfk_2` FOREIGN KEY (`loc_code`) REFERENCES `0_locations` (`loc_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Create stock_serial_moves table for tracking serial number movements
CREATE TABLE `0_stock_serial_moves` (
  `stkitmmoveno` int(11) NOT NULL AUTO_INCREMENT,
  `stockmoveno` int(11) NOT NULL DEFAULT '0',
  `stock_id` varchar(20) NOT NULL,
  `serial_no` varchar(30) NOT NULL,
  `moveqty` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`stkitmmoveno`),
  KEY `StockMoveNo` (`stockmoveno`),
  KEY `StockID_SN` (`stock_id`,`serial_no`),
  KEY `serialno` (`serialno`),
  CONSTRAINT `stock_serial_moves_ibfk_1` FOREIGN KEY (`stockmoveno`) REFERENCES `0_stock_moves` (`trans_id`),
  CONSTRAINT `stock_serial_moves_ibfk_2` FOREIGN KEY (`stock_id`, `serial_no`) REFERENCES `0_stock_serial_items` (`stock_id`, `serial_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;