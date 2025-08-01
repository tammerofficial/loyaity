# Laravel Loyalty System with Apple Wallet Support

<!-- Use this file to provide workspace-specific custom instructions to Copilot. For more details, visit https://code.visualstudio.com/docs/copilot/copilot-customization#_use-a-githubcopilotinstructionsmd-file -->

## Project Overview
This is a Laravel 11 loyalty card management system with Apple Wallet support. The system uses SQLite as the database and provides RESTful APIs for mobile app integration.

## Key Features
- Customer loyalty card management
- Points earning and redemption system
- Apple Wallet PKPass generation
- Transaction logging
- Admin dashboard with TailwindCSS
- Laravel Sanctum API authentication

## Code Standards
- Follow Laravel conventions and best practices
- Use Eloquent ORM for database operations
- Implement proper API resource transformations
- Use form request validation
- Follow RESTful API design principles
- Implement proper error handling and logging

## Key Models
- Customer: Manages customer information
- LoyaltyCard: Manages loyalty card details
- Transaction: Logs all point-related activities
- PointRule: Configures point calculation rules
- AppleWalletPass: Manages Apple Wallet pass generation

## API Security
- Use Laravel Sanctum for API authentication
- Validate all inputs using Form Requests
- Implement rate limiting on API endpoints
- Use HTTPS in production

## Apple Wallet Integration
- Generate PKPass files with customer data
- Include QR codes for in-store scanning
- Support pass updates and push notifications
- Implement proper pass signing and certificates
