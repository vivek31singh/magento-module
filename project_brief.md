# Project Brief: magento-module

## Project Type
Magento 2 Extension / Module

## Project Goals (Golden Context)
To develop a robust Magento 2 module that automatically flushes the system cache at a fixed interval of every two hours via the native cron scheduler, thereby reducing manual administrative overhead and ensuring optimal site performance.

## Complexity
Simple
Justification: The project requires standard Magento 2 module scaffolding and a single cron job configuration. There are no complex database schema changes, heavy business logic, or frontend components required. The functionality relies entirely on existing Magento framework APIs (`Magento\Framework\App\Cache\Manager`).

## Tech Stack
PHP 7.4+ (Compatible with Magento 2.4.x)
Magento 2 Open Source / Commerce (CE/EE)
XML (Configuration)
Composer (Dependency Management)
