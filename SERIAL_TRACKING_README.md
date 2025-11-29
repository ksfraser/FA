# Serial Number Tracking Module for FrontAccounting

This module adds comprehensive serial number tracking functionality to FrontAccounting, inspired by WebERP's implementation.

## Features

- **Serialised Items**: Track individual serial numbers for items
- **Controlled Items**: Manage items that require special control
- **Movement Tracking**: Track serial number movements through all inventory transactions
- **Research Functionality**: Search and trace serial numbers through their lifecycle
- **Quality Control**: Store quality notes and expiration dates for serial items

## Installation

1. Copy the module files to your FrontAccounting installation
2. Run the database schema: `sql/serial_tracking_tables.sql`
3. Activate the module through the module manager

## Usage

### Setting up Serial Tracking for Items

1. Go to Inventory > Items > Item Maintenance
2. Edit an item and check "Serialised" or "Controlled" as appropriate
3. Save the item

### Managing Serial Numbers

1. Go to Inventory > Serial Number Management
2. Select a stock item and location
3. Add, edit, or delete serial numbers for that item/location combination

### Researching Serial Numbers

1. Go to Inventory > Serial Number Research
2. Enter a serial number to see its current status and movement history

## Database Schema

### Modified Tables

- `0_stock_master`: Added `controlled` and `serialised` columns

### New Tables

- `0_stock_serial_items`: Stores serial number information
- `0_stock_serial_moves`: Tracks serial number movements

## Integration

The module integrates with existing FA inventory transactions:
- Sales deliveries
- Purchase receipts
- Inventory adjustments
- Stock transfers

Serial number validation and movement tracking are automatically handled during these transactions.

## Permissions

- `SA_SERIALITEMS`: Access to serial number management and research

## Compatibility

- Compatible with both new modular FA framework and legacy FA 2.4 hooks
- Works with existing inventory, sales, and purchasing modules