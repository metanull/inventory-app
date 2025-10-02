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
# Step 1: Create a new user (generates a random secure password)
php artisan user:create "Admin User" admin@company.com

# Step 2: Assign the Manager role to the user
php artisan user:assign-role admin@company.com "Manager of Users"

# Step 3: Verify the user was created correctly
php artisan user:show admin@company.com
```

The `user:create` command will output the generated password. **Save this password** as it's the only time you'll see it. The user can change it later through the web interface.

Example output:

```
$ php artisan user:create "System Administrator" admin@museumwnf.org

User created successfully.
Username: System Administrator
Email: admin@museumwnf.org
Password: Xy9kL2mN8pQ5wE7tR1

$ php artisan user:assign-role admin@museumwnf.org "Manager of Users"

Successfully assigned role 'Manager of Users' to user 'System Administrator' (admin@museumwnf.org).

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
# Create a user with a random password (password will be displayed once)
php artisan user:create "User Name" user@company.com
```

### Managing User Roles

```powershell
# Assign role to existing user
php artisan user:assign-role user@company.com "Manager of Users"

# Remove role from user
php artisan user:remove-role user@company.com "Regular User"
```

### User Information

```powershell
# Display detailed user information including roles and permissions
php artisan user:show user@company.com

# List all users with their roles
php artisan user:list

# List users filtered by role
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

# Show detailed permission matrix for all roles
php artisan permission:show

# Create a new role (if needed)
php artisan permission:create-role "New Role Name"

# Create a new permission (if needed)
php artisan permission:create-permission "new permission name"

# Clear permission cache
php artisan permission:cache-reset
```

## System Maintenance Commands

### Permission System Maintenance

```powershell
# Rebuild permissions and role assignments
php artisan permissions:rebuild

# Clear permission cache
php artisan cache:clear
php artisan config:clear
php artisan permission:cache-reset
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
php artisan user:create "System Administrator" admin@company.com

# 4. Assign admin role
php artisan user:assign-role admin@company.com "Manager of Users"

# 5. Verify setup
php artisan user:show admin@company.com
```

### Adding New Team Members

```powershell
# Create regular user account
php artisan user:create "New Team Member" newuser@company.com

# Assign regular user role
php artisan user:assign-role newuser@company.com "Regular User"

# Send login credentials to user (password will be displayed when created)
```

### Promoting Users to Admin

```powershell
# Assign Manager role to existing user
php artisan user:assign-role user@company.com "Manager of Users"

# Verify role assignment
php artisan user:show user@company.com
```

### Troubleshooting Access Issues

```powershell
# Check user's current roles and permissions
php artisan user:show user@company.com

# Rebuild permission system
php artisan permissions:rebuild

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan permission:cache-reset
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
php artisan user:show user@company.com

# View permission matrix
php artisan permission:show
```

### Getting Help

```powershell
# Get help for specific commands
php artisan help user:create
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
