---
layout: default
title: Command Line User Management
parent: Deployment Guide
nav_order: 6
---

# Command Line User Management

This guide covers managing users and roles from the command line using Laravel Artisan commands, essential for initial system setup and ongoing administration.

## Table of Contents

{: .no_toc .text-delta }

1. TOC
   {:toc}

---

## Overview

The Inventory Management System provides comprehensive command-line tools for user and role management through custom Artisan commands. These tools are essential for:

- **Initial Setup**: Creating the first admin user after deployment
- **User Management**: Creating, updating, and managing user accounts
- **Role Management**: Assigning and managing user roles and permissions
- **System Maintenance**: Rebuilding permissions and troubleshooting authorization issues

## Initial Admin Account Setup

### Creating the First Admin User

After deploying the application, you need to create an initial admin user to access the system:

```powershell
# Create a new user with Manager of Users role
php artisan make:user admin@company.com "Admin User" --role="Manager of Users"

# Or create a user interactively
php artisan make:user
```

### Interactive User Creation

When running `php artisan make:user` without parameters, you'll be prompted for:

- **Email Address**: Must be unique and valid
- **Full Name**: Display name for the user
- **Password**: Will be securely hashed (leave empty to generate random password)
- **Role Assignment**: Choose from available roles

Example:
```
$ php artisan make:user

 Enter email address:
 > admin@museumwnf.org

 Enter full name:
 > Museum Administrator

 Enter password (leave empty for random):
 > 

 Available roles:
  [0] Manager of Users
  [1] Regular User

 Select role:
 > 0

User created successfully!
Email: admin@museumwnf.org
Name: Museum Administrator
Password: RandomGeneratedPassword123
Role: Manager of Users
```

## User Management Commands

### Creating Users

```powershell
# Create user with specific role
php artisan make:user user@company.com "Regular User" --role="Regular User"

# Create user with generated password
php artisan make:user user@company.com "Test User" --generate-password

# Create user interactively
php artisan make:user
```

### Managing User Roles

```powershell
# Assign role to existing user
php artisan user:assign-role user@company.com "Manager of Users"

# Remove role from user
php artisan user:remove-role user@company.com "Regular User"

# List user's current roles
php artisan user:roles user@company.com
```

### User Information

```powershell
# Display user details
php artisan user:info user@company.com

# List all users
php artisan user:list

# List users by role
php artisan user:list --role="Manager of Users"
```

## Role and Permission Management

### Available Roles

The system includes two predefined roles:

1. **Manager of Users**
   - Full system access
   - Can manage other users and assign roles
   - All permissions: view, create, update, delete data, manage users

2. **Regular User**
   - Limited system access
   - Cannot manage users or assign roles
   - Basic permissions: view data, create data

### Permission Management

```powershell
# Rebuild all permissions (useful after updates)
php artisan permissions:rebuild

# List all permissions
php artisan permission:list

# List all roles
php artisan role:list

# Show role permissions
php artisan role:permissions "Manager of Users"
```

## System Maintenance Commands

### Permission System Maintenance

```powershell
# Rebuild permissions and role assignments
php artisan permissions:rebuild

# Clear permission cache
php artisan cache:clear
php artisan config:clear

# Verify permission structure
php artisan role:verify
```

### Database Seeding

```powershell
# Seed roles and permissions
php artisan db:seed --class=RolePermissionSeeder

# Full database reset and seed (CAUTION: Deletes all data)
php artisan migrate:fresh --seed
```

## Common Use Cases

### Initial System Setup

After a fresh deployment:

```powershell
# 1. Run migrations
php artisan migrate

# 2. Seed roles and permissions
php artisan db:seed --class=RolePermissionSeeder

# 3. Create initial admin user
php artisan make:user admin@company.com "System Administrator" --role="Manager of Users"

# 4. Verify setup
php artisan user:info admin@company.com
```

### Adding New Team Members

```powershell
# Create regular user account
php artisan make:user newuser@company.com "New Team Member" --role="Regular User"

# Send login credentials to user (password will be displayed)
```

### Promoting Users to Admin

```powershell
# Assign Manager role to existing user
php artisan user:assign-role user@company.com "Manager of Users"

# Verify role assignment
php artisan user:roles user@company.com
```

### Troubleshooting Access Issues

```powershell
# Check user's current roles and permissions
php artisan user:info user@company.com

# Rebuild permission system
php artisan permissions:rebuild

# Clear caches
php artisan cache:clear
php artisan config:clear
```

## Security Considerations

### Password Management

- Generated passwords are cryptographically secure
- Passwords are never stored in plain text
- Users should change generated passwords on first login
- Consider implementing password complexity requirements

### Role Assignment Best Practices

- Follow principle of least privilege
- Regularly audit user roles and permissions
- Remove unnecessary admin access
- Monitor user access patterns

### Command Line Security

- Restrict shell access to authorized administrators only
- Use environment-specific commands (production vs development)
- Log administrative actions for audit trails
- Secure the server where these commands are executed

## Troubleshooting

### Common Issues

**"Role does not exist" Error**
```powershell
# Ensure roles are seeded
php artisan db:seed --class=RolePermissionSeeder
```

**"Permission denied" Error**
```powershell
# Rebuild permissions
php artisan permissions:rebuild

# Clear caches
php artisan cache:clear
```

**User Cannot Access System**
```powershell
# Check user has a role assigned
php artisan user:roles user@company.com

# Verify role has correct permissions
php artisan role:permissions "Role Name"
```

### Getting Help

```powershell
# Get help for specific commands
php artisan help make:user
php artisan help user:assign-role
php artisan help permissions:rebuild

# List all available commands
php artisan list
```

## Integration with Web Interface

Once you have created admin users via command line, they can:

- Access the web-based User Management interface at `/admin/users`
- Manage users through the graphical interface
- Assign roles and permissions via the web UI
- View user activity and system logs

The command-line tools complement the web interface and are essential for initial setup, automation, and emergency access when the web interface is unavailable.