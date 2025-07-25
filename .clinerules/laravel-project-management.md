## Brief overview
This rule file contains guidelines for developing a Laravel-based project management application for telecommunication construction projects. It covers the specific requirements, architecture decisions, and development preferences discussed for this project.

## Communication style
- Use Indonesian language for all communications and code comments
- Be direct and technical in responses
- Provide clear explanations for architectural decisions
- Focus on implementation details rather than conversational elements

## Development workflow
- Create comprehensive TODO lists before implementation
- Use Laravel 12 with Blade templating (not SPA frameworks)
- Implement role-based access control (RBAC) with four user levels
- Follow database-first approach with proper migrations
- Implement approval workflows for financial operations
- Include import/export functionality for Excel files

## Coding best practices
- Use Tailwind CSS for modern, responsive UI design
- Implement proper validation for all forms
- Follow RESTful API principles for backend endpoints
- Use proper naming conventions (snake_case for database, camelCase for JavaScript)
- Implement proper error handling and user feedback
- Use middleware for role-based access control
- Implement audit trails for important operations

## Project context
- Telecommunication construction project management system
- Four user roles: Direktur, Project Manager, Finance Manager, Staf
- PostgreSQL database integration
- Financial tracking with budget vs actual comparison
- Approval workflow for expenses (Staf → Finance Manager → Project Manager/Direktur)
- Net profit calculation and reporting
- Excel import/export functionality
- Project status tracking with activity logging

## Other guidelines
- Provide template files after application completion
- Include comprehensive documentation and user guides
- Implement proper data validation and sanitization
- Use proper authentication and authorization mechanisms
- Ensure mobile-responsive design with modern UI
- Include dashboard analytics for business intelligence
