# تنفيذ نظام الأدوار والصلاحيات

## ما تم إنجازه

تم تنفيذ نظام كامل لإدارة الأدوار والصلاحيات باستخدام جداول قاعدة البيانات الموجودة.

## الملفات التي تم إنشاؤها

### 1. `server/sql/populate_roles_permissions.sql`
- ملف SQL لملء جداول `roles`, `permissions`, و `role_permissions`
- يحتوي على 3 أدوار: user, instructor, admin
- يحتوي على جميع الصلاحيات المطلوبة لكل دور

### 2. `server/utils/permissions.php`
- ملف PHP يحتوي على جميع الدوال للتحقق من الأدوار والصلاحيات
- الدوال المتاحة:
  - `getUserRoles()` - الحصول على أدوار المستخدم
  - `hasRole()` - التحقق من وجود دور معين
  - `isAdmin()` - التحقق من كون المستخدم مدير
  - `isInstructor()` - التحقق من كون المستخدم مدرب
  - `hasPermission()` - التحقق من وجود صلاحية معينة
  - `getUserPermissions()` - الحصول على جميع صلاحيات المستخدم
  - `assignRole()` - تعيين دور لمستخدم
  - `removeRole()` - إزالة دور من مستخدم
  - `user_is_admin()` - دالة للتوافق العكسي مع النظام القديم

### 3. `server/sql/ROLES_PERMISSIONS_SETUP.md`
- دليل شامل لاستخدام النظام
- شرح جميع الأدوار والصلاحيات
- أمثلة على الاستخدام

## الملفات التي تم تحديثها

### 1. `server/auth/login.php`
- تم تحديثه لجلب الأدوار والصلاحيات من جدول `user_roles`
- يعيد `roles` و `permissions` في استجابة تسجيل الدخول
- إذا لم يكن للمستخدم أي دور، يتم تعيين دور `user` تلقائياً
- يدعم التحويل التلقائي من النظام القديم (`profile_meta.rank`)

### 2. `server/api/request_role.php`
- تم تحديثه لتعيين الأدوار في جدول `user_roles` بدلاً من `profile_meta` فقط
- عند الموافقة على طلب دور، يتم تعيين الدور في `user_roles`
- لا يزال يحدث `profile_meta` للتوافق العكسي

### 3. `server/api/comments/comments_repository.php`
- تم تحديثه لاستخدام نظام الصلاحيات الجديد
- يستخدم الدالة `user_is_admin()` من `permissions.php`

## كيفية الاستخدام

### 1. تشغيل ملف SQL

افتح phpMyAdmin أو MySQL واشغل الملف:
```sql
SOURCE server/sql/populate_roles_permissions.sql;
```

أو انسخ محتوى الملف وقم بتنفيذه.

### 2. تعيين الأدوار للمستخدمين

يمكنك تعيين الأدوار للمستخدمين بطريقتين:

#### الطريقة الأولى: من خلال واجهة Admin
- المستخدم يطلب دور من صفحة Profile
- المدير يوافق على الطلب من Admin Dashboard
- يتم تعيين الدور تلقائياً

#### الطريقة الثانية: مباشرة من قاعدة البيانات
```sql
-- تعيين دور instructor لمستخدم معين
INSERT INTO user_roles (user_id, role_id, assigned_by)
SELECT 1, role_id, NULL
FROM roles WHERE name = 'instructor';
```

### 3. استخدام النظام في الكود

#### في PHP:
```php
require_once __DIR__ . '/utils/permissions.php';

// التحقق من الدور
if (isAdmin($conn, $userId)) {
    // كود خاص بالمدير
}

// التحقق من الصلاحية
if (hasPermission($conn, $userId, 'create_lab')) {
    // كود إنشاء معمل
}
```

#### في React (Frontend):
```javascript
// بعد تسجيل الدخول
const user = response.data.user;

// التحقق من الدور
const isAdmin = user.roles?.includes('admin');
const isInstructor = user.roles?.includes('instructor');

// التحقق من الصلاحية
const canCreateLab = user.permissions?.includes('create_lab');
```

## الأدوار والصلاحيات

### دور User (مستخدم)
- عرض المعاملات
- الوصول للمعاملات
- إرسال الحلول
- عرض لوحة المتصدرين
- إدارة الملف الشخصي
- التعليق والإعجاب
- طلب ترقية الدور

### دور Instructor (مدرب)
- جميع صلاحيات User
- إنشاء وتعديل وحذف المعاملات
- إنشاء وتعديل التحديات
- مراجعة وتقييم الحلول
- إدارة التلميحات
- عرض إحصائيات المعاملات

### دور Admin (مدير)
- جميع الصلاحيات
- إدارة المستخدمين
- تعيين الأدوار
- الموافقة على طلبات الأدوار
- عرض سجلات النظام
- حظر المستخدمين
- إدارة التعليقات

## التوافق العكسي

النظام الجديد متوافق تماماً مع النظام القديم:
- إذا كان المستخدم لديه `rank` في `profile_meta`، سيتم تحويله تلقائياً عند تسجيل الدخول
- الدالة `user_is_admin()` تعمل مع كلا النظامين
- لا حاجة لتحديث الكود القديم فوراً

## الخطوات التالية (اختيارية)

1. **تحديث Frontend**: يمكنك تحديث `App.jsx` لاستخدام `user.roles` و `user.permissions` بدلاً من `profile_meta.rank`

2. **إضافة فهارس**: يمكنك إضافة فهارس على `user_roles` و `role_permissions` لتحسين الأداء:
```sql
CREATE INDEX idx_user_roles_user ON user_roles(user_id);
CREATE INDEX idx_role_permissions_role ON role_permissions(role_id);
```

3. **إضافة صلاحيات جديدة**: يمكنك إضافة صلاحيات جديدة في `populate_roles_permissions.sql` وربطها بالأدوار

## ملاحظات مهمة

- تأكد من تشغيل ملف SQL قبل استخدام النظام
- جميع الاستعلامات تستخدم Prepared Statements للأمان
- النظام يدعم عدة أدوار للمستخدم الواحد
- الصلاحيات تُجمع من جميع أدوار المستخدم

