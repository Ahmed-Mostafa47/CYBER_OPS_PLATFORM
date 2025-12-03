# نظام SuperAdmin وإدارة المستخدمين

## ما تم إضافته

### 1. دور SuperAdmin
- تم إضافة دور `superadmin` الذي يملك جميع الصلاحيات
- SuperAdmin يمكنه إدارة صلاحيات جميع المستخدمين
- SuperAdmin يمكنه إعطاء أو إزالة أي دور لأي مستخدم

### 2. API لإدارة المستخدمين
- تم إنشاء `server/api/manage_users.php`
- يدعم GET, POST, PUT للتعامل مع المستخدمين
- GET: جلب جميع المستخدمين مع أدوارهم وصلاحياتهم
- POST: تعيين دور لمستخدم
- PUT: إزالة دور من مستخدم

### 3. واجهة إدارة المستخدمين في Admin Dashboard
- قسم "USERS_MANAGEMENT" في لوحة التحكم
- عند الضغط عليه تظهر/تختفي قائمة المستخدمين
- لكل مستخدم:
  - معلومات المستخدم (اسم، بريد، معرف)
  - الأدوار الحالية
  - أزرار لإضافة/إزالة الأدوار (فقط SuperAdmin)

## كيفية الاستخدام

### 1. تعيين دور SuperAdmin لمستخدم

#### من قاعدة البيانات:
```sql
-- الحصول على role_id لـ superadmin
SET @role_superadmin = (SELECT role_id FROM roles WHERE name = 'superadmin' LIMIT 1);

-- تعيين superadmin لمستخدم معين (استبدل 1 بـ user_id)
INSERT INTO user_roles (user_id, role_id, assigned_by)
VALUES (1, @role_superadmin, NULL);
```

#### من خلال الكود:
```php
require_once __DIR__ . '/utils/permissions.php';

// تعيين superadmin لمستخدم
assignRole($conn, $userId, 'superadmin', $currentUserId);
```

### 2. استخدام واجهة إدارة المستخدمين

1. سجل دخول كمستخدم لديه دور `superadmin`
2. اذهب إلى Admin Dashboard
3. اضغط على قسم "USERS_MANAGEMENT"
4. ستظهر قائمة بجميع المستخدمين
5. لكل مستخدم:
   - **ADD_ROLE**: لإضافة دور جديد
   - **REMOVE_ROLE**: لإزالة دور موجود

### 3. الصلاحيات المتاحة

#### SuperAdmin يمكنه:
- ✅ إدارة جميع المستخدمين
- ✅ تعيين أو إزالة أي دور لأي مستخدم
- ✅ الوصول لجميع الصلاحيات في النظام
- ✅ تجاوز أي فحص صلاحيات

#### Admin يمكنه:
- ✅ إدارة المستخدمين (عرض فقط)
- ✅ الموافقة على طلبات الأدوار
- ✅ جميع صلاحيات Instructor و User

## الحماية والأمان

### 1. حماية SuperAdmin
- لا يمكن إزالة دور `superadmin` من آخر superadmin في النظام
- يتم التحقق من الصلاحيات في كل عملية

### 2. التحقق من الصلاحيات
- جميع العمليات تتطلب التحقق من صلاحيات المستخدم الحالي
- SuperAdmin فقط يمكنه تعيين أو إزالة الأدوار

### 3. Prepared Statements
- جميع الاستعلامات تستخدم Prepared Statements لمنع SQL Injection

## API Endpoints

### GET /manage_users.php
جلب جميع المستخدمين مع أدوارهم

**Parameters:**
- `current_user_id` (required): معرف المستخدم الحالي

**Response:**
```json
{
  "success": true,
  "users": [
    {
      "user_id": 1,
      "username": "user1",
      "email": "user1@example.com",
      "full_name": "User One",
      "roles": ["user", "instructor"],
      "permissions": ["view_labs", "create_lab", ...]
    }
  ],
  "available_roles": [
    {
      "role_id": 1,
      "name": "user",
      "description": "..."
    }
  ]
}
```

### POST /manage_users.php
تعيين دور لمستخدم

**Body:**
```json
{
  "current_user_id": 1,
  "target_user_id": 2,
  "role_name": "admin"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Role assigned successfully",
  "user": { ... }
}
```

### PUT /manage_users.php
إزالة دور من مستخدم

**Body:**
```json
{
  "current_user_id": 1,
  "target_user_id": 2,
  "role_name": "admin"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Role removed successfully",
  "user": { ... }
}
```

## الملفات المعدلة

1. **server/sql/populate_roles_permissions.sql**
   - إضافة دور `superadmin`
   - إضافة صلاحيات خاصة بـ superadmin

2. **server/utils/permissions.php**
   - إضافة دالة `isSuperAdmin()`
   - تحديث `hasPermission()` لدعم superadmin
   - إضافة دوال `getAllRoles()` و `getAllPermissions()`

3. **server/api/manage_users.php** (جديد)
   - API كامل لإدارة المستخدمين

4. **src/features/dashboard/AdminDashboardPage.jsx**
   - إضافة قسم Users Management
   - واجهة لإدارة الأدوار

5. **src/App.jsx**
   - دعم SuperAdmin في التحقق من الصلاحيات

## الخطوات التالية

1. **تشغيل ملف SQL المحدث:**
   ```sql
   SOURCE server/sql/populate_roles_permissions.sql;
   ```

2. **تعيين SuperAdmin:**
   - قم بتعيين دور `superadmin` لمستخدمك من قاعدة البيانات

3. **اختبار النظام:**
   - سجل دخول كمستخدم superadmin
   - اذهب إلى Admin Dashboard
   - جرب قسم Users Management

## ملاحظات مهمة

- ⚠️ **لا تقم بإزالة دور superadmin من آخر superadmin في النظام**
- ⚠️ **تأكد من وجود superadmin واحد على الأقل دائماً**
- ✅ **SuperAdmin لديه جميع الصلاحيات تلقائياً**
- ✅ **النظام متوافق مع النظام القديم (profile_meta.rank)**

## استكشاف الأخطاء

### المشكلة: "Access denied. SuperAdmin privileges required"
**الحل**: تأكد من أن المستخدم الحالي لديه دور `superadmin`

### المشكلة: "Cannot remove superadmin role"
**الحل**: هذا حماية - لا يمكن إزالة superadmin من آخر superadmin. قم بإنشاء superadmin آخر أولاً.

### المشكلة: المستخدمون لا يظهرون
**الحل**: 
1. تأكد من أن API يعمل
2. تحقق من أن المستخدم الحالي لديه صلاحيات admin أو superadmin
3. تحقق من console للأخطاء

