# إعداد نظام الأدوار والصلاحيات (Roles & Permissions Setup)

## نظرة عامة

تم إعداد نظام كامل لإدارة الأدوار والصلاحيات باستخدام جداول قاعدة البيانات:
- `roles` - الأدوار (user, instructor, admin)
- `permissions` - الصلاحيات
- `role_permissions` - ربط الأدوار بالصلاحيات
- `user_roles` - ربط المستخدمين بالأدوار

## خطوات الإعداد

### 1. تشغيل ملف SQL لملء الجداول

قم بتشغيل الملف التالي في phpMyAdmin أو MySQL:

```sql
SOURCE server/sql/populate_roles_permissions.sql;
```

أو افتح الملف `server/sql/populate_roles_permissions.sql` وانسخ محتواه وقم بتنفيذه.

### 2. التحقق من البيانات

بعد تشغيل الملف، تحقق من البيانات:

```sql
-- عرض الأدوار
SELECT * FROM roles;

-- عرض الصلاحيات
SELECT * FROM permissions;

-- عرض ربط الأدوار بالصلاحيات
SELECT 
    r.name as role,
    p.name as permission
FROM roles r
JOIN role_permissions rp ON r.role_id = rp.role_id
JOIN permissions p ON rp.permission_id = p.permission_id
ORDER BY r.name, p.name;
```

## الأدوار المتاحة

### 1. User (مستخدم عادي)
- عرض المعاملات (Labs)
- الوصول إلى المعاملات
- إرسال الحلول
- عرض لوحة المتصدرين
- إدارة الملف الشخصي
- التعليق والإعجاب
- طلب ترقية الدور

### 2. Instructor (مدرب)
- جميع صلاحيات User
- إنشاء وتعديل وحذف المعاملات
- إنشاء وتعديل التحديات
- مراجعة وتقييم الحلول
- إدارة التلميحات
- عرض إحصائيات المعاملات

### 3. Admin (مدير)
- جميع الصلاحيات
- إدارة المستخدمين
- تعيين الأدوار
- الموافقة على طلبات الأدوار
- عرض سجلات النظام
- حظر المستخدمين
- إدارة التعليقات

## استخدام النظام في الكود

### في PHP

```php
require_once __DIR__ . '/utils/permissions.php';

// التحقق من الدور
if (hasRole($conn, $userId, 'admin')) {
    // كود خاص بالمدير
}

// التحقق من الصلاحية
if (hasPermission($conn, $userId, 'create_lab')) {
    // كود إنشاء معمل
}

// الحصول على جميع أدوار المستخدم
$roles = getUserRoles($conn, $userId);

// الحصول على جميع صلاحيات المستخدم
$permissions = getUserPermissions($conn, $userId);

// تعيين دور لمستخدم
assignRole($conn, $userId, 'instructor', $assignedByUserId);

// إزالة دور من مستخدم
removeRole($conn, $userId, 'instructor');
```

### في Frontend (React)

بعد تسجيل الدخول، سيحتوي كائن المستخدم على:
```javascript
{
  user_id: 1,
  username: "user1",
  roles: ["user", "instructor"],  // الأدوار
  permissions: ["view_labs", "create_lab", ...]  // الصلاحيات
}
```

يمكنك التحقق من الأدوار والصلاحيات:
```javascript
// التحقق من الدور
const isAdmin = user.roles.includes('admin');
const isInstructor = user.roles.includes('instructor');

// التحقق من الصلاحية
const canCreateLab = user.permissions.includes('create_lab');
```

## الترقية من النظام القديم

النظام الجديد متوافق مع النظام القديم الذي كان يستخدم `profile_meta.rank`. 

- عند تسجيل الدخول، إذا كان المستخدم لديه `rank` في `profile_meta` ولا يوجد له أدوار في `user_roles`، سيتم تحويله تلقائياً
- الدالة `user_is_admin()` تعمل مع كلا النظامين للتوافق العكسي

## تعيين الأدوار للمستخدمين الحاليين

إذا كنت تريد تعيين دور `user` لجميع المستخدمين الحاليين الذين لا يملكون أدوار:

```sql
SET @role_user = (SELECT role_id FROM roles WHERE name = 'user' LIMIT 1);

INSERT INTO user_roles (user_id, role_id)
SELECT u.user_id, @role_user
FROM users u
WHERE NOT EXISTS (
    SELECT 1 FROM user_roles ur WHERE ur.user_id = u.user_id
);
```

## الصلاحيات المتاحة

### صلاحيات المستخدم
- `view_labs` - عرض المعاملات
- `access_lab` - الوصول للمعاملات
- `submit_solution` - إرسال الحلول
- `view_leaderboard` - عرض لوحة المتصدرين
- `view_profile` - عرض الملف الشخصي
- `edit_profile` - تعديل الملف الشخصي
- `comment` - التعليق
- `like_comment` - الإعجاب بالتعليقات
- `request_role` - طلب ترقية الدور

### صلاحيات المدرب
- `create_lab` - إنشاء معمل
- `edit_lab` - تعديل معمل
- `delete_lab` - حذف معمل
- `publish_lab` - نشر/إلغاء نشر معمل
- `create_challenge` - إنشاء تحدٍ
- `edit_challenge` - تعديل تحدٍ
- `delete_challenge` - حذف تحدٍ
- `review_submission` - مراجعة الحلول
- `view_all_submissions` - عرض جميع الحلول
- `manage_hints` - إدارة التلميحات
- `view_lab_analytics` - عرض إحصائيات المعاملات

### صلاحيات المدير
- `manage_users` - إدارة المستخدمين
- `assign_roles` - تعيين الأدوار
- `approve_role_requests` - الموافقة على طلبات الأدوار
- `delete_users` - حذف المستخدمين
- `manage_system` - إدارة النظام
- `view_audit_logs` - عرض سجلات النظام
- `manage_blocks` - حظر/إلغاء حظر المستخدمين
- `view_all_comments` - عرض جميع التعليقات
- `delete_comments` - حذف أي تعليق
- `export_data` - تصدير البيانات

## ملاحظات مهمة

1. **الأدوار الافتراضية**: عند تسجيل الدخول، إذا لم يكن للمستخدم أي دور، سيتم تعيين دور `user` تلقائياً (إذا كانت الجداول ممتلئة)

2. **التوافق العكسي**: النظام يعمل مع النظام القديم (`profile_meta.rank`) للتوافق

3. **الأمان**: جميع الاستعلامات تستخدم Prepared Statements لمنع SQL Injection

4. **الأداء**: يمكنك إضافة فهارس (indexes) على `user_roles` و `role_permissions` لتحسين الأداء إذا لزم الأمر

## استكشاف الأخطاء

### المشكلة: المستخدم لا يملك صلاحيات بعد تسجيل الدخول
**الحل**: تأكد من:
1. تشغيل ملف `populate_roles_permissions.sql`
2. تعيين دور للمستخدم في جدول `user_roles`

### المشكلة: الخطأ "Role not found"
**الحل**: تأكد من أن الدور موجود في جدول `roles` وأن الاسم صحيح (حساس لحالة الأحرف)

### المشكلة: الصلاحيات لا تظهر في Frontend
**الحل**: تأكد من أن `login.php` يعيد `roles` و `permissions` في الاستجابة

